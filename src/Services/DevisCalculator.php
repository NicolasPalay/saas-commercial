<?php

namespace App\Services;

class DevisCalculator
{
    public function calculLineHT($price, $quantity): float
    {
        return bcmul((string) $price, (string) $quantity, 2);
    }
}