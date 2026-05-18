<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Topic;
use App\Models\User;

class TopicPolicy
{
    public function create(User $user): bool
    {
        return $user->isStudent() || $user->isSupervisor() || $user->isAdmin();
    }

    public function update(User $user, Topic $topic): bool
    {
        // Редактировать может автор или админ, но только если тема ещё не занята
        return ($topic->proposed_by === $user->id || $user->isAdmin())
            && ! $topic->thesis()->whereNull('done_at')->exists();
    }

    public function approve(User $user): bool
    {
        return $user->isAdmin();
    }
}
