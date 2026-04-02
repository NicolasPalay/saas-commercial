<?php

namespace App\Services;

use App\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;
use Dom\Entity;

class AddresssDefault
{   
        public function __construct( private EntityManagerInterface $entityManager)
        {
            $this->entityManager = $entityManager;
        }

    public function setDefaultAddress(Address $address): void
    {
        $client = $address->getClient();

        $addresses = $this->entityManager
            ->getRepository(Address::class)
            ->findBy(['client' => $client]);

        foreach ($addresses as $addr) {
            $addr->setIsDefault(false);
            $this->entityManager->persist($addr);

        }
    }
}


