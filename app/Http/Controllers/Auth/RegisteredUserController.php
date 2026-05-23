<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'adminRegistrationEnabled' => $this->adminRegistrationEnabled(),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        if ($this->adminRegistrationEnabled()) {
            $rules['admin_token'] = ['nullable', 'string'];
        }

        $request->validate($rules);

        $permissions = $this->shouldGrantAdmin($request)
            ? User::PERM_ADMIN
            : User::PERM_STUDENT;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'permissions' => $permissions,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    private function adminRegistrationEnabled(): bool
    {
        return filled(config('portal.admin.registration_token'))
            && ! User::adminExists();
    }

    private function shouldGrantAdmin(Request $request): bool
    {
        if (! $this->adminRegistrationEnabled()) {
            return false;
        }

        $token = (string) config('portal.admin.registration_token');

        return hash_equals($token, (string) $request->input('admin_token', ''));
    }
}
