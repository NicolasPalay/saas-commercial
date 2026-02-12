<?php

namespace App\Services;

use App\Entity\Devis;
use App\Repository\DevisDetailsRepository;

class TotalDevisService
{
    public function __construct(
        private DevisDetailsRepository $devisDetailsRepository
    ) {}

    /**
     * Calcule le total HT d'un devis
     */
    public function calculTotalHT(Devis $devis): string
    {
        $total = '0.00';

        $details = $this->devisDetailsRepository->findBy([
            'devis' => $devis
        ]);

        foreach ($details as $detail) {
            $total = bcadd($total, (string) $detail->getTotal(), 2);
        }

        return $total;
    }
}
