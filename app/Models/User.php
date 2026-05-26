<?php

namespace App\Models;

use App\Enums\StudyGroupJoinRequestStatus;
use App\Enums\ThesisAssignmentStatus;
use App\Enums\ThesisAssignmentType;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
/**
 * @property int $id
 * @property string $name
 * @property string $last_name
 * @property string $first_name
 * @property string|null $patronymic
 * @property string $email
 * @property string $password
 * @property int $permissions
 * @property-read string $display_name
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    const PERM_STUDENT    = 1;
    const PERM_SUPERVISOR = 2;
    const PERM_REVIEWER   = 4;
    const PERM_COMMISSION = 8;
    const PERM_ADMIN      = 16;

    protected $fillable = [
        'name',
        'last_name',
        'first_name',
        'patronymic',
        'email',
        'password',
        'permissions',
        'study_group_id',
    ];

    protected $attributes = [
        'permissions' => self::PERM_STUDENT,
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if ((int) ($user->permissions ?? 0) === 0) {
                $user->permissions = self::PERM_STUDENT;
            }

            $user->syncNameFromParts();
        });

        static::saving(function (User $user): void {
            if ($user->isDirty(['last_name', 'first_name', 'patronymic'])) {
                $user->syncNameFromParts();
            }
        });
    }

    public function getDisplayNameAttribute(): string
    {
        $formatted = trim(implode(' ', array_filter([
            $this->last_name,
            $this->first_name,
            $this->patronymic,
        ])));

        return $formatted !== '' ? $formatted : (string) $this->name;
    }

    public function syncNameFromParts(): void
    {
        $formatted = trim(implode(' ', array_filter([
            $this->last_name,
            $this->first_name,
            $this->patronymic,
        ])));

        if ($formatted !== '') {
            $this->name = $formatted;
        }
    }

    public function scopeStudents(Builder $query): Builder
    {
        return $query->whereRaw('(permissions & ?) != 0', [self::PERM_STUDENT]);
    }

    public function scopeWithoutActiveTopicAssignment(Builder $query): Builder
    {
        return $query->whereDoesntHave('theses', function (Builder $thesisQuery): void {
            $thesisQuery->whereNull('done_at')
                ->where(function (Builder $statusQuery): void {
                    $statusQuery->whereIn('assignment_status', ThesisAssignmentStatus::activeValues())
                        ->orWhere('assignment_status', ThesisAssignmentStatus::Pending->value);
                });
        });
    }

    public function scopeSearchByName(Builder $query, string $search): Builder
    {
        $like = '%'.$search.'%';

        return $query->where(function (Builder $builder) use ($like): void {
            $builder->where('last_name', 'like', $like)
                ->orWhere('first_name', 'like', $like)
                ->orWhere('patronymic', 'like', $like)
                ->orWhere('name', 'like', $like)
                ->orWhere('email', 'like', $like);
        });
    }

    public function hasPermission(int $permission): bool
    {
        return ($this->permissions & $permission) !== 0;
    }

    public function grantPermission(int $permission): void
    {
        $this->permissions |= $permission;
        $this->save();
    }

    public function revokePermission(int $permission): void
    {
        $this->permissions &= ~$permission;
        $this->save();
    }

    public function isStudent(): bool    { return $this->hasPermission(self::PERM_STUDENT); }
    public function isSupervisor(): bool { return $this->hasPermission(self::PERM_SUPERVISOR); }
    public function isReviewer(): bool   { return $this->hasPermission(self::PERM_REVIEWER); }
    public function isCommission(): bool { return $this->hasPermission(self::PERM_COMMISSION); }
    public function isAdmin(): bool      { return $this->hasPermission(self::PERM_ADMIN); }

    public static function adminExists(): bool
    {
        return static::query()
            ->whereRaw('(permissions & ?) != 0', [self::PERM_ADMIN])
            ->exists();
    }

    public function activeThesis(): HasOne
    {
        return $this->hasOne(Thesis::class, 'student_id')
            ->whereNull('done_at')
            ->whereIn('assignment_status', ThesisAssignmentStatus::activeValues());
    }

    public function topicOffers(): HasMany
    {
        return $this->hasMany(Thesis::class, 'student_id')
            ->whereNull('done_at')
            ->where('assignment_status', ThesisAssignmentStatus::Pending->value)
            ->where('assignment_type', ThesisAssignmentType::TeacherOffer->value);
    }

    public function theses(): HasMany
    {
        return $this->hasMany(Thesis::class, 'student_id');
    }

    public function supervisedTheses(): HasMany
    {
        return $this->hasMany(Thesis::class, 'supervisor_id');
    }

    public function supervisedGroups(): HasMany
    {
        return $this->hasMany(StudyGroup::class, 'supervisor_id');
    }

    public function studyGroup(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StudyGroup::class);
    }

    public function studyGroupJoinRequests(): HasMany
    {
        return $this->hasMany(StudyGroupJoinRequest::class);
    }

    public function pendingStudyGroupJoinRequest(): HasOne
    {
        return $this->hasOne(StudyGroupJoinRequest::class)
            ->where('status', StudyGroupJoinRequestStatus::Pending)
            ->latestOfMany();
    }

    public function proposedTopics(): HasMany
    {
        return $this->hasMany(Topic::class, 'proposed_by');
    }
}
