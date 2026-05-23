<?php

namespace App\Enums;

enum TopicKind: int
{
    case None = 0;
    case Catalog = 1 << 0;
    case StudentProposal = 1 << 1;

    public function label(): string
    {
        return match ($this) {
            self::None => 'Не задано',
            self::Catalog => 'Каталог',
            self::StudentProposal => 'Предложение студента',
        };
    }
}
