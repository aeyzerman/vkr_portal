<?php

namespace App\Enums;

enum StudyGroupJoinRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Ожидает решения',
            self::Approved => 'Одобрена',
            self::Rejected => 'Отклонена',
        };
    }
}
