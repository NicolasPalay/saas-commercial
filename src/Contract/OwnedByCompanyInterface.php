<?php

namespace App\Contract;

use App\Entity\Company;

interface OwnedByCompanyInterface
{
    public function getCompany(): ?Company;
}