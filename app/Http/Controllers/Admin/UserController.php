<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudyGroup;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('studyGroup.supervisor');

        if ($request->filled('search')) {
            $query->where(function ($builder) use ($request) {
                $search = '%' . $request->string('search') . '%';
                $builder->where('full_name', 'like', $search)
                    ->orWhere('name', 'like', $search)
                    ->orWhere('email', 'like', $search);
            });
        }

        if ($request->filled('role')) {
            $query->whereRaw('(permissions & ?) != 0', [(int) $request->role]);
        }

        $users = $query->orderByRaw('coalesce(full_name, name)')->paginate(30)->withQueryString();

        $roles = [
            User::PERM_STUDENT => 'Студент',
            User::PERM_SUPERVISOR => 'Преподаватель',
            User::PERM_REVIEWER => 'Рецензент',
            User::PERM_COMMISSION => 'Комиссия',
            User::PERM_ADMIN => 'Администратор',
        ];

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function show(User $user)
    {
        $user->load([
            'studyGroup.supervisor',
            'theses.topic',
            'supervisedTheses.student',
            'supervisedGroups',
            'proposedTopics',
        ]);

        return view('admin.users.show', compact('user'));
    }

    public function assignGroup(Request $request, User $user)
    {
        $validated = $request->validate([
            'study_group_id' => 'required|exists:study_groups,id',
        ]);

        $user->update([
            'study_group_id' => $validated['study_group_id'],
        ]);

        return back()->with('success', 'Пользователь прикреплён к группе.');
    }

    public function updatePermissions(Request $request, User $user)
    {
        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|in:1,2,4,8,16',
        ]);

        $user->update([
            'permissions' => array_sum($validated['permissions'] ?? []),
        ]);

        return back()->with('success', 'Роли пользователя обновлены.');
    }

    public function edit(User $user)
    {
        $groups = StudyGroup::with('supervisor')->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'groups'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'study_group_id' => 'nullable|exists:study_groups,id',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Данные пользователя обновлены.');
    }
}
