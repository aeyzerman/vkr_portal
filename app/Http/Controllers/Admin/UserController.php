<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudyGroup;
use App\Models\User;
use App\Services\StudyGroupMembershipService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly StudyGroupMembershipService $membership,
    ) {}

    public function index(Request $request)
    {
        $query = User::query()->with('studyGroup.supervisor');

        if ($request->filled('search')) {
            $query->searchByName($request->string('search'));
        }

        if ($request->filled('role')) {
            $query->whereRaw('(permissions & ?) != 0', [(int) $request->role]);
        }

        $users = $query->orderBy('last_name')->orderBy('first_name')->paginate(30)->withQueryString();

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

        $group = StudyGroup::query()->findOrFail($validated['study_group_id']);
        $this->membership->assignStudent($user, $group, $request->user());

        return back()->with('success', 'Пользователь прикреплён к группе.');
    }

    public function removeGroup(Request $request, User $user)
    {
        $this->membership->removeStudent($user, $request->user());

        return back()->with('success', 'Пользователь откреплён от группы.');
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
            'last_name' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'patronymic' => 'nullable|string|max:100',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'study_group_id' => 'nullable|exists:study_groups,id',
        ]);

        $newGroupId = $validated['study_group_id'] ?? null;
        unset($validated['study_group_id']);

        $user->update($validated);

        if ($newGroupId && $user->study_group_id !== (int) $newGroupId) {
            $group = StudyGroup::query()->findOrFail($newGroupId);
            $this->membership->assignStudent($user->fresh(), $group, $request->user());
        } elseif (! $newGroupId && $user->study_group_id) {
            $this->membership->removeStudent($user->fresh(), $request->user());
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Данные пользователя обновлены.');
    }
}
