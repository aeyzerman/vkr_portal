<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyGroup extends Model
{
    protected $fillable = [
        'name',
        'course',
        'specialty_code',
        'specialty_name',
        'supervisor_id',
        'enrollment_year',
    ];

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function theses()
    {
        return $this->hasMany(Thesis::class);
    }

    // Активные (незавершённые) работы группы
    public function activeTheses()
    {
        return $this->hasMany(Thesis::class)->whereNull('done_at');
    }
}
