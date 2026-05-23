<?php

namespace App\Services;

use App\Enums\StudyGroupJoinRequestStatus;
use App\Models\StudyGroup;
use App\Models\StudyGroupJoinRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudyGroupMembershipService
{
    public function requestJoin(User $student, StudyGroup $group): StudyGroupJoinRequest
    {
        $this->assertStudent($student);

        if ($student->study_group_id !== null) {
            throw ValidationException::withMessages([
                'study_group_id' => 'Вы уже состоите в учебной группе.',
            ]);
        }

        $pending = $student->studyGroupJoinRequests()
            ->where('status', StudyGroupJoinRequestStatus::Pending)
            ->exists();

        if ($pending) {
            throw ValidationException::withMessages([
                'study_group_id' => 'У вас уже есть активная заявка на вступление в группу.',
            ]);
        }

        return StudyGroupJoinRequest::query()->create([
            'user_id' => $student->id,
            'study_group_id' => $group->id,
            'status' => StudyGroupJoinRequestStatus::Pending,
        ]);
    }

    public function approve(StudyGroupJoinRequest $joinRequest, User $processor): void
    {
        $this->assertCanManageGroup($processor, $joinRequest->studyGroup);
        $this->assertPending($joinRequest);

        $student = $joinRequest->user;

        if ($student->study_group_id !== null && $student->study_group_id !== $joinRequest->study_group_id) {
            throw ValidationException::withMessages([
                'user_id' => 'Студент уже закреплён за другой группой.',
            ]);
        }

        DB::transaction(function () use ($joinRequest, $processor, $student): void {
            $student->update(['study_group_id' => $joinRequest->study_group_id]);

            $joinRequest->update([
                'status' => StudyGroupJoinRequestStatus::Approved,
                'processed_by' => $processor->id,
                'processed_at' => now(),
            ]);

            $this->closeOtherPendingRequests($student, $joinRequest->id);
        });
    }

    public function reject(StudyGroupJoinRequest $joinRequest, User $processor): void
    {
        $this->assertCanManageGroup($processor, $joinRequest->studyGroup);
        $this->assertPending($joinRequest);

        $joinRequest->update([
            'status' => StudyGroupJoinRequestStatus::Rejected,
            'processed_by' => $processor->id,
            'processed_at' => now(),
        ]);
    }

    public function assignStudent(User $student, StudyGroup $group, User $processor): void
    {
        $this->assertCanManageGroup($processor, $group);
        $this->assertStudent($student);

        if ($student->study_group_id !== null && $student->study_group_id !== $group->id) {
            throw ValidationException::withMessages([
                'user_id' => 'Студент уже состоит в другой группе.',
            ]);
        }

        DB::transaction(function () use ($student, $group, $processor): void {
            $student->update(['study_group_id' => $group->id]);

            StudyGroupJoinRequest::query()
                ->where('user_id', $student->id)
                ->where('study_group_id', $group->id)
                ->where('status', StudyGroupJoinRequestStatus::Pending)
                ->update([
                    'status' => StudyGroupJoinRequestStatus::Approved,
                    'processed_by' => $processor->id,
                    'processed_at' => now(),
                ]);

            $this->closeOtherPendingRequests($student);
        });
    }

    public function removeStudent(User $student, User $processor): void
    {
        if ($student->study_group_id === null) {
            return;
        }

        $group = $student->studyGroup;

        if ($group) {
            $this->assertCanManageGroup($processor, $group);
        } elseif (! $processor->isAdmin()) {
            abort(403);
        }

        $student->update(['study_group_id' => null]);
    }

    private function closeOtherPendingRequests(User $student, ?int $exceptId = null): void
    {
        StudyGroupJoinRequest::query()
            ->where('user_id', $student->id)
            ->where('status', StudyGroupJoinRequestStatus::Pending)
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->update([
                'status' => StudyGroupJoinRequestStatus::Rejected,
                'processed_at' => now(),
            ]);
    }

    private function assertStudent(User $user): void
    {
        if (! $user->isStudent()) {
            throw ValidationException::withMessages([
                'user_id' => 'В группу можно добавить только студента.',
            ]);
        }
    }

    private function assertCanManageGroup(User $user, StudyGroup $group): void
    {
        if ($user->isAdmin() || ($user->isSupervisor() && $group->supervisor_id === $user->id)) {
            return;
        }

        abort(403);
    }

    private function assertPending(StudyGroupJoinRequest $joinRequest): void
    {
        if (! $joinRequest->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Заявка уже обработана.',
            ]);
        }
    }
}
