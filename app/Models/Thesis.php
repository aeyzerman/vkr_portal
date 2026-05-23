<?php

declare(strict_types=1);

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $student_id
 * @property int $supervisor_id
 * @property int $study_group_id
 * @property int $topic_id
 * @property string $assignment_type
 * @property string $assignment_status
 * @property string $status
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
    // Порядок статусов для прогресс-бара
    const STATUSES = [
        'draft',
        'submitted',
        'review',
        'revision',
        'approved',
        'completed',
    ];

    // Первый и последний - особые
    const STATUS_INITIAL  = 'draft';
    const STATUS_FINAL    = 'completed';

    const ASSIGNMENT_TYPES = [
        'teacher_offer',
        'student_choice',
        'student_proposal',
        'random_assignment',
    ];

    const ASSIGNMENT_STATUSES = [
        'pending',
        'accepted',
        'declined',
        'assigned',
    ];

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
        return $this->done_at === null;
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
