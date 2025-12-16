<?php

namespace App\Enums;

enum QuestionType: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';
    case TRUE_FALSE = 'true_false';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Open Answer',
            self::CLOSED => 'Closed Options',
            self::TRUE_FALSE => 'True / False',
        };
    }
}
