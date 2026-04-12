<?php

namespace App\Enums;

enum QuestionType: string
{
    case OPEN_LONG = 'open_long';
    case OPEN_SHORT = 'open_short';
    case CLOSED = 'closed';
    case TRUE_FALSE = 'true_false';

    public function label(): string
    {
        return match ($this) {
            self::OPEN_LONG => 'Open (Long)',
            self::OPEN_SHORT => 'Open (Short)',
            self::CLOSED => 'Closed',
            self::TRUE_FALSE => 'True / False',
        };
    }
}
