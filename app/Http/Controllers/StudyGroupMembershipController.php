<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\StudyGroup;
use App\Models\StudyGroupJoinRequest;
use App\Models\User;
use App\Services\StudyGroupMembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class StudyGroupMembershipController extends Controller
{
    public function __construct(
        private readonly StudyGroupMembershipService $membership,
    ) {}

    public function requestJoin(Request $request, StudyGroup $studyGroup): RedirectResponse
    {
        abort_unless($request->user()->isStudent(), HttpResponse::HTTP_FORBIDDEN);

        $this->membership->requestJoin($request->user(), $studyGroup);

        return back()->with('success', 'Заявка на вступление в группу отправлена.');
    }

    public function approve(Request $request, StudyGroupJoinRequest $joinRequest): RedirectResponse
    {
        $joinRequest->load('studyGroup');
        $this->membership->approve($joinRequest, $request->user());

        return back()->with('success', 'Студент добавлен в группу.');
    }

    public function reject(Request $request, StudyGroupJoinRequest $joinRequest): RedirectResponse
    {
        $joinRequest->load('studyGroup');
        $this->membership->reject($joinRequest, $request->user());

        return back()->with('success', 'Заявка отклонена.');
    }

    public function storeMember(Request $request, StudyGroup $studyGroup): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $student = User::query()->findOrFail($validated['user_id']);
        $this->membership->assignStudent($student, $studyGroup, $request->user());

        return back()->with('success', 'Студент добавлен в группу.');
    }

    public function destroyMember(Request $request, StudyGroup $studyGroup, User $user): RedirectResponse
    {
        abort_unless($user->study_group_id === $studyGroup->id, HttpResponse::HTTP_NOT_FOUND);

        $this->membership->removeStudent($user, $request->user());

        return back()->with('success', 'Студент исключён из группы.');
    }

    public function searchStudents(Request $request, StudyGroup $studyGroup): JsonResponse
    {
        $user = $request->user();
        abort_unless(
            $user->isAdmin() || ($user->isSupervisor() && $studyGroup->supervisor_id === $user->id),
            HttpResponse::HTTP_FORBIDDEN
        );

        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $students = User::query()
            ->students()
            ->whereNull('study_group_id')
            ->searchByName($validated['q'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(15)
            ->get(['id', 'last_name', 'first_name', 'patronymic', 'name', 'email']);

        return response()->json([
            'students' => $students->map(fn (User $student) => [
                'id' => $student->id,
                'display_name' => $student->display_name,
                'email' => $student->email,
            ]),
        ]);
    }
}
