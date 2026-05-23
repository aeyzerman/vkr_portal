<?php

namespace App\Support;

class StudyGroupOptions
{
    /**
     * @return array<int, string>
     */
    public static function courses(): array
    {
        return [
            1 => '1 курс',
            2 => '2 курс',
            3 => '3 курс',
            4 => '4 курс',
        ];
    }

    /**
     * @return list<int>
     */
    public static function enrollmentYears(): array
    {
        $current = (int) now()->year;

        return range($current + 1, $current - 15);
    }
}
