<?php

namespace App\Services;

class DevisCalculator
{
    public function calculLineHT($price, $quantity,$reduce): float
    {
        $total = bcmul((string) $price, (string) $quantity, 2);
        if ($reduce > 0) {
            $total = bcsub($total, bcmul($total, (string) ($reduce / 100), 2), 2);
        }
        return $total;
    }
}