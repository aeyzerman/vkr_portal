<?php

namespace App\Enums;

enum ThesisAssignmentType: int
{
    case None = 0;
    case TeacherOffer = 1 << 0;
    case StudentChoice = 1 << 1;
    case StudentProposal = 1 << 2;
    case RandomAssignment = 1 << 3;

    public function label(): string
    {
        return match ($this) {
            self::None => 'Не задано',
            self::TeacherOffer => 'Предложение преподавателя',
            self::StudentChoice => 'Выбор из каталога',
            self::StudentProposal => 'Авторская тема студента',
            self::RandomAssignment => 'Случайное распределение',
        };
    }
}
