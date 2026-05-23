<?php

namespace App\Models;

use App\Enums\StudyGroupJoinRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyGroupJoinRequest extends Model
{
    protected $fillable = [
        'user_id',
        'study_group_id',
        'status',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'status' => StudyGroupJoinRequestStatus::class,
        'processed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studyGroup(): BelongsTo
    {
        return $this->belongsTo(StudyGroup::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function isPending(): bool
    {
        return $this->status === StudyGroupJoinRequestStatus::Pending;
    }
}
