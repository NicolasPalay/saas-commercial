<?php

namespace App\Services;

class DevisCalculator
{
    public function calculLineHT($price, $quantity, $reduce): string
    {
        $total = bcmul((string) $price, (string) $quantity, 2);

        if ($reduce > 0) {
            $discount = bcmul($total, bcdiv((string) $reduce, '100', 4), 2);
            $total = bcsub($total, $discount, 2);
        }

        return $total;
    }
}