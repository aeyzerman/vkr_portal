<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ThesisStatus;
use App\Models\Thesis;
use App\Models\User;

class ThesisPolicy
{
    public function view(User $user, Thesis $thesis): bool
    {
        return $user->isAdmin()
            || $thesis->student_id     === $user->id
            || $thesis->supervisor_id  === $user->id
            || $user->isCommission()
            || $user->isReviewer();
    }

    public function uploadDocument(User $user, Thesis $thesis): bool
    {
        // Только сам студент, только активная работа
        return $thesis->student_id === $user->id && $thesis->isActive();
    }

    public function downloadDocument(User $user, Thesis $thesis): bool
    {
        return $this->view($user, $thesis);
    }

    public function updateStatus(User $user, Thesis $thesis): bool
    {
        if (! $thesis->isActive()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSupervisor() && $thesis->supervisor_id === $user->id) {
            return true;
        }

        if (($user->isCommission() || $user->isReviewer())
            && in_array($thesis->status?->value, [ThesisStatus::Review->value, ThesisStatus::Approved->value], true)) {
            return true;
        }

        return false;
    }
}
