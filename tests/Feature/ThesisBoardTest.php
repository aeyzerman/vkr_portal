<?php

namespace Tests\Feature;

use App\Enums\ThesisAssignmentStatus;
use App\Enums\ThesisAssignmentType;
use App\Enums\ThesisStatus;
use App\Models\StudyGroup;
use App\Models\Thesis;
use App\Enums\TopicKind;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThesisBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_can_update_status_via_json(): void
    {
        $supervisor = User::factory()->create(['permissions' => User::PERM_SUPERVISOR]);
        $student = User::factory()->create(['permissions' => User::PERM_STUDENT]);
        $group = StudyGroup::create([
            'name' => 'ИВТ-401',
            'course' => 4,
            'specialty_code' => '09.03.01',
            'specialty_name' => 'Информатика',
            'supervisor_id' => $supervisor->id,
            'enrollment_year' => 2021,
        ]);

        $topic = Topic::create([
            'title' => 'Тема',
            'description' => 'Описание',
            'kind' => TopicKind::Catalog,
            'proposed_by' => $supervisor->id,
            'is_approved' => true,
            'approved_by' => $supervisor->id,
            'approved_at' => now(),
        ]);

        $thesis = Thesis::create([
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
            'study_group_id' => $group->id,
            'topic_id' => $topic->id,
            'assignment_type' => ThesisAssignmentType::TeacherOffer,
            'assignment_status' => ThesisAssignmentStatus::Accepted,
            'assigned_at' => now(),
            'started_at' => now(),
            'status' => ThesisStatus::Draft,
        ]);

        $response = $this->actingAs($supervisor)->patchJson(route('thesis.status.update', $thesis), [
            'status' => ThesisStatus::Review->value,
        ]);

        $response->assertOk()
            ->assertJsonPath('thesis.status', ThesisStatus::Review->value);

        $this->assertSame(ThesisStatus::Review, $thesis->fresh()->status);
    }
}
