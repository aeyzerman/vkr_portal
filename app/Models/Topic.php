<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ThesisAssignmentStatus;
use App\Enums\TopicKind;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property int $proposed_by
 * @property int $reserved_for
 * @property bool $is_approved
 * @property TopicKind $kind
 */
class Topic extends Model
{
    protected $fillable = [
        'title',
        'description',
        'proposed_by',
        'kind',
        'reserved_for',
        'is_approved',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'kind' => TopicKind::class,
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function proposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function reservedFor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reserved_for');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function theses(): HasMany
    {
        return $this->hasMany(Thesis::class);
    }

    public function thesis(): HasOne
    {
        return $this->hasOne(Thesis::class)
            ->whereIn('assignment_status', ThesisAssignmentStatus::activeValues())
            ->latestOfMany();
    }

    // Свободные (не занятые) одобренные темы
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_approved', true)
            ->whereDoesntHave('theses', fn($q) => $q
                ->whereNull('done_at')
                ->whereIn('assignment_status', ThesisAssignmentStatus::activeValues()));
    }
}
