<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudyGroup;
use App\Models\Thesis;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->where('permissions', '&', (int) $request->role);
        }

        $users = $query->orderBy('full_name')->paginate(30)->withQueryString();

        $roles = [
            User::PERM_STUDENT    => 'Студент',
            User::PERM_SUPERVISOR => 'Руководитель',
            User::PERM_REVIEWER   => 'Рецензент',
            User::PERM_COMMISSION => 'Комиссия',
            User::PERM_ADMIN      => 'Администратор',
        ];

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function show(User $user)
    {
        $user->load(['theses.topic', 'supervisedTheses.student', 'supervisedGroups']);
        return view('admin.users.show', compact('user'));
    }

    // Назначить группу студенту и создать thesis-запись
    public function assignGroup(Request $request, User $user)
    {
        $validated = $request->validate([
            'study_group_id' => 'required|exists:study_groups,id',
            'supervisor_id'  => 'required|exists:users,id',
        ]);

        // Проверяем что у студента нет уже активной работы в другой группе
        if ($user->activeThesis) {
            return back()->with('error', 'У студента уже есть активная работа.');
        }

        Thesis::create([
            'student_id'     => $user->id,
            'supervisor_id'  => $validated['supervisor_id'],
            'study_group_id' => $validated['study_group_id'],
            'status'         => 'draft',
        ]);

        return back()->with('success', 'Студент добавлен в группу.');
    }

    // Управление правами - выдать / забрать битфлаг
    public function updatePermissions(Request $request, User $user)
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'integer|in:1,2,4,8,16',
        ]);

        $bits = array_sum($validated['permissions']);
        $user->update(['permissions' => $bits]);

        return back()->with('success', 'Права пользователя обновлены.');
    }

    public function edit(User $user)
    {
        $groups = StudyGroup::orderBy('name')->get();
        $supervisors = User::where('permissions', '&', User::PERM_SUPERVISOR)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'name']);

        return view('admin.users.edit', compact('user', 'groups', 'supervisors'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return back()->with('success', 'Данные пользователя обновлены.');
    }
}
