<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ThesisAssignmentStatus;
use App\Enums\ThesisAssignmentType;
use App\Enums\ThesisStatus;
use DateTime;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $student_id
 * @property int $supervisor_id
 * @property int $study_group_id
 * @property int $topic_id
 * @property ThesisAssignmentType $assignment_type
 * @property ThesisAssignmentStatus $assignment_status
 * @property ThesisStatus $status
 * @property string $document_path
 * @property string $document_name
 * @property DateTime $assigned_at
 * @property DateTime $assignment_responded_at
 * @property DateTime $started_at
 * @property DateTime $submitted_at
 * @property DateTime $done_at
 * @property int $grade
 */
class Thesis extends Model
{
    const STATUS_INITIAL = ThesisStatus::Draft;
    const STATUS_FINAL = ThesisStatus::Completed;

    protected $fillable = [
        'student_id',
        'supervisor_id',
        'study_group_id',
        'topic_id',
        'assignment_type',
        'assignment_status',
        'assigned_at',
        'assignment_responded_at',
        'started_at',
        'submitted_at',
        'status',
        'document_path',
        'document_name',
        'done_at',
        'grade',
    ];

    protected $casts = [
        'assignment_type' => ThesisAssignmentType::class,
        'assignment_status' => ThesisAssignmentStatus::class,
        'status' => ThesisStatus::class,
        'assigned_at' => 'datetime',
        'assignment_responded_at' => 'datetime',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'done_at' => 'datetime',
    ];

    // --- Связи ---

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function studyGroup()
    {
        return $this->belongsTo(StudyGroup::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    // --- Хелперы ---

    public function isActive(): bool
    {
        return $this->done_at === null
            && in_array($this->assignment_status?->value, ThesisAssignmentStatus::activeValues(), true);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_FINAL;
    }

    public function markDone(): void
    {
        $this->update([
            'status'  => self::STATUS_FINAL,
            'done_at' => now(),
        ]);
    }

    public function isAwaitingStudentDecision(): bool
    {
        return $this->assignment_type === ThesisAssignmentType::TeacherOffer
            && $this->assignment_status === ThesisAssignmentStatus::Pending
            && $this->done_at === null;
    }

    // Скоупы
    public function scopeActive($query)
    {
        return $query->whereNull('done_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('done_at');
    }
}
