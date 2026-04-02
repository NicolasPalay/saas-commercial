<?php

namespace App\Services;

class DocumentCalculator
{
    /**
     * Calcule une ligne HT
     */
    public function calculLineHT(string $price, string $quantity, string $reduce = '0'): string
    {
        $total = bcmul($price, $quantity, 2);

        if (bccomp($reduce, '0', 2) === 1) {
            $discount = bcmul($total, bcdiv($reduce, '100', 4), 2);
            $total = bcsub($total, $discount, 2);
        }

        return $total;
    }

    /**
     * Total HT
     */
    public function calculTotalHT(array $details): string
    {
        $total = '0.00';

        foreach ($details as $detail) {
            $total = bcadd($total, (string) $detail->getTotal(), 2);
        }

        return $total;
    }

    /**
     * Total TVA
     */
    public function calculTotalTVA(array $details): string
    {
        $totalTVA = '0.00';

        foreach ($details as $detail) {
            $rate = (string) $detail->getTaxe()->getRate();

            $tva = bcmul(
                (string) $detail->getTotal(),
                bcdiv($rate, '100', 4),
                2
            );

            $totalTVA = bcadd($totalTVA, $tva, 2);
        }

        return $totalTVA;
    }

    /**
     * Total TTC
     */
    public function calculTotalTTC(string $ht, string $tva): string
    {
        return bcadd($ht, $tva, 2);
    }

    /**
     * Recalcule entièrement un document
     */
    public function recalculate($document, string $detailsMethod): void
    {
        $details = $document->$detailsMethod()->toArray();

        $ht = $this->calculTotalHT($details);
        $tva = $this->calculTotalTVA($details);
        $ttc = $this->calculTotalTTC($ht, $tva);

        $document->setTotal($ht);
        $document->setTaxe($tva);
        $document->setTotalTTC($ttc);
    }
}