<?php

namespace App\Models;

use App\Enums\ThesisAssignmentStatus;
use App\Enums\ThesisAssignmentType;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property int $permissions
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // Битфлаги прав доступа
    const PERM_STUDENT    = 1;
    const PERM_SUPERVISOR = 2;
    const PERM_REVIEWER   = 4;
    const PERM_COMMISSION = 8;
    const PERM_ADMIN      = 16;

    protected $fillable = [
        'name',
        'full_name',
        'email',
        'password',
        'permissions',
        'study_group_id',
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

    // --- Хелперы прав ---

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

    // --- Связи ---

    // Активные ВКР студента (без done_at)
    public function activeThesis()
    {
        return $this->hasOne(Thesis::class, 'student_id')
            ->whereNull('done_at')
            ->whereIn('assignment_status', ThesisAssignmentStatus::activeValues());
    }

    public function topicOffers()
    {
        return $this->hasMany(Thesis::class, 'student_id')
            ->whereNull('done_at')
            ->where('assignment_status', ThesisAssignmentStatus::Pending->value)
            ->where('assignment_type', ThesisAssignmentType::TeacherOffer->value);
    }

    // Все ВКР студента (история)
    public function theses()
    {
        return $this->hasMany(Thesis::class, 'student_id');
    }

    // ВКР под руководством
    public function supervisedTheses()
    {
        return $this->hasMany(Thesis::class, 'supervisor_id');
    }

    // Группы где является куратором
    public function supervisedGroups()
    {
        return $this->hasMany(StudyGroup::class, 'supervisor_id');
    }

    public function studyGroup()
    {
        return $this->belongsTo(StudyGroup::class);
    }

    // Предложенные темы
    public function proposedTopics()
    {
        return $this->hasMany(Topic::class, 'proposed_by');
    }
}
