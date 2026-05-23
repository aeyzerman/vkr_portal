<?php

namespace App\Enums;

enum ThesisStatus: int
{
    case None = 0;
    case Draft = 1 << 0;
    case Submitted = 1 << 1;
    case Review = 1 << 2;
    case Revision = 1 << 3;
    case Approved = 1 << 4;
    case Completed = 1 << 5;

    public function label(): string
    {
        return match ($this) {
            self::None => 'Не задано',
            self::Draft => 'Черновик',
            self::Submitted => 'Сдана',
            self::Review => 'На рецензировании',
            self::Revision => 'На доработке',
            self::Approved => 'Одобрена',
            self::Completed => 'Завершена',
        };
    }

    /**
     * Колонки канбан-доски «Работы».
     *
     * @return list<self>
     */
    public static function boardColumns(): array
    {
        return [
            self::Draft,
            self::Submitted,
            self::Review,
            self::Revision,
            self::Approved,
            self::Completed,
        ];
    }

    public static function forBoard(mixed $value): self
    {
        $status = self::tryFrom((int) $value);

        if ($status === null || $status === self::None) {
            return self::Draft;
        }

        return $status;
    }
}
