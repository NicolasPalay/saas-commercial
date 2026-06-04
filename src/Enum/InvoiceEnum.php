<?php

namespace App\Enum;

enum InvoiceEnum: string
{
   case DRAFT     = 'draft';      // Facture en cours de création
    case SENT      = 'sent';       // Envoyée au client
    case PAID      = 'paid';       // Payée
    case PARTIAL   = 'partial';    // Partiellement payée
    case OVERDUE   = 'overdue';    // En retard de paiement
    case CANCELLED = 'cancelled';  // Annulée

    public function label(): string
    {
        return match($this) {
            self::DRAFT     => 'Brouillon',
            self::SENT      => 'Envoyée',
            self::PAID      => 'Payée',
            self::PARTIAL   => 'Partiellement payée',
            self::OVERDUE   => 'En retard',
            self::CANCELLED => 'Annulée',
        };
    }
}