<?php

namespace App\Enums;

enum ThesisAssignmentStatus: int
{
    case None = 0;
    case Pending = 1 << 0;
    case Accepted = 1 << 1;
    case Declined = 1 << 2;
    case Assigned = 1 << 3;

    public function label(): string
    {
        return match ($this) {
            self::None => 'Не задано',
            self::Pending => 'Ожидает ответа',
            self::Accepted => 'Принята',
            self::Declined => 'Отклонена',
            self::Assigned => 'Назначена',
        };
    }

    public static function activeValues(): array
    {
        return [self::Accepted->value, self::Assigned->value];
    }
}
