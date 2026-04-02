<?php

namespace App\Services;



class TotalDevisService
{


    /**
     * Calcule le total HT d'un devis
     */
    public function calculTotalHT(array $entityDetails): string
    {
        $total = '0.00';

        foreach ($entityDetails as $detail) {
            $total = bcadd($total, (string) $detail->getTotal(), 2);
        }

        return $total;
    }
}
