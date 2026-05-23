<?php

namespace Tests\Feature;

use App\Enums\StudyGroupJoinRequestStatus;
use App\Models\StudyGroup;
use App\Models\StudyGroupJoinRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyGroupMembershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_request_join(): void
    {
        $student = User::factory()->create(['permissions' => User::PERM_STUDENT]);
        $supervisor = User::factory()->create(['permissions' => User::PERM_SUPERVISOR]);
        $group = $this->createGroup($supervisor);

        $this->actingAs($student)
            ->post(route('groups.join-request', $group))
            ->assertRedirect();

        $this->assertDatabaseHas('study_group_join_requests', [
            'user_id' => $student->id,
            'study_group_id' => $group->id,
            'status' => StudyGroupJoinRequestStatus::Pending->value,
        ]);
    }

    public function test_supervisor_can_approve_request(): void
    {
        $student = User::factory()->create(['permissions' => User::PERM_STUDENT]);
        $supervisor = User::factory()->create(['permissions' => User::PERM_SUPERVISOR]);
        $group = $this->createGroup($supervisor);

        $request = StudyGroupJoinRequest::query()->create([
            'user_id' => $student->id,
            'study_group_id' => $group->id,
            'status' => StudyGroupJoinRequestStatus::Pending,
        ]);

        $this->actingAs($supervisor)
            ->post(route('join-requests.approve', $request))
            ->assertRedirect();

        $this->assertSame($group->id, $student->fresh()->study_group_id);
        $this->assertSame(
            StudyGroupJoinRequestStatus::Approved,
            $request->fresh()->status
        );
    }

    public function test_supervisor_can_add_student_by_search_flow(): void
    {
        $student = User::factory()->create([
            'permissions' => User::PERM_STUDENT,
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
        ]);
        $supervisor = User::factory()->create(['permissions' => User::PERM_SUPERVISOR]);
        $group = $this->createGroup($supervisor);

        $this->actingAs($supervisor)
            ->post(route('groups.members.store', $group), ['user_id' => $student->id])
            ->assertRedirect();

        $this->assertSame($group->id, $student->fresh()->study_group_id);
    }

    private function createGroup(User $supervisor): StudyGroup
    {
        return StudyGroup::query()->create([
            'name' => 'ИВТ-401',
            'course' => 4,
            'specialty_code' => '09.03.01',
            'specialty_name' => 'Информатика',
            'supervisor_id' => $supervisor->id,
            'enrollment_year' => 2022,
        ]);
    }
}
