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
 */
class Topic extends Model
{
    protected $fillable = [
        'title',
        'description',
        'proposed_by',
        'reserved_for',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function proposedBy()
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function reservedFor()
    {
        return $this->belongsTo(User::class, 'reserved_for');
    }

    public function thesis()
    {
        return $this->hasOne(Thesis::class);
    }

    // Свободные (не занятые) одобренные темы
    public function scopeAvailable($query)
    {
        return $query->where('is_approved', true)
            ->whereDoesntHave('thesis', fn($q) => $q->whereNull('done_at'));
    }
}
