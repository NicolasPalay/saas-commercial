<?php

namespace App\Enum;

enum InvoiceTypeEnum: string
{
    case INVOICE = 'invoice'; // facture classique
    case CREDIT  = 'credit';  // avoir

    public function label(): string
    {
        return match($this) {
            self::INVOICE => 'Facture',
            self::CREDIT  => 'Avoir',
        };
    }
}