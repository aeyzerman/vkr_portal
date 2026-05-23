<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_registration_grants_student_permission(): void
    {
        $response = $this->post('/register', [
            'last_name' => 'Студентов',
            'first_name' => 'Студент',
            'email' => 'student@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertTrue(User::where('email', 'student@example.com')->first()->isStudent());
    }

    public function test_admin_token_grants_admin_when_no_admin_exists(): void
    {
        config(['portal.admin.registration_token' => 'bootstrap-secret']);

        $response = $this->post('/register', [
            'last_name' => 'Админов',
            'first_name' => 'Админ',
            'email' => 'admin@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'admin_token' => 'bootstrap-secret',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'admin@example.com')->first();
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isStudent());
    }

    public function test_admin_token_ignored_when_admin_already_exists(): void
    {
        config(['portal.admin.registration_token' => 'bootstrap-secret']);

        User::factory()->create([
            'email' => 'existing@example.com',
            'permissions' => User::PERM_ADMIN,
        ]);

        $this->post('/register', [
            'last_name' => 'Другой',
            'first_name' => 'Админ',
            'email' => 'another@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'admin_token' => 'bootstrap-secret',
        ]);

        $user = User::where('email', 'another@example.com')->first();
        $this->assertTrue($user->isStudent());
        $this->assertFalse($user->isAdmin());
    }
}
