<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property int $proposed_by
 * @property int $reserved_for
 * @property bool $is_approved
 * @property string $kind
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
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function proposedBy()
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function reservedFor()
    {
        return $this->belongsTo(User::class, 'reserved_for');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function theses()
    {
        return $this->hasMany(Thesis::class);
    }

    public function thesis()
    {
        return $this->hasOne(Thesis::class)
            ->whereIn('assignment_status', ['accepted', 'assigned'])
            ->latestOfMany();
    }

    // Свободные (не занятые) одобренные темы
    public function scopeAvailable($query)
    {
        return $query->where('is_approved', true)
            ->whereDoesntHave('theses', fn($q) => $q
                ->whereNull('done_at')
                ->whereIn('assignment_status', ['accepted', 'assigned']));
    }
}
