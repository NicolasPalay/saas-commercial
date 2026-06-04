<?php

namespace App\Enum;

enum StatusEnum: string
{
    case DRAFT    = 'draft';
    case ACCEPTED = 'accepted';
    case REFUSED  = 'refused';
    case EXPIRED  = 'expired';

    public function label(): string
    {
        return match($this) {
            self::DRAFT    => 'Brouillon',
            self::ACCEPTED => 'Accepté',
            self::REFUSED  => 'Refusé',
            self::EXPIRED  => 'Expiré',
        };
    }
}