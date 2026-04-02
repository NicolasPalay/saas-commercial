<?php

namespace App\Services;

use App\Entity\Address;

class ClientAddressService{
   public function setAddress($form, Address $address = null): Address
{
    if (!$address) {
        $address = new Address();
    }

    $address->setNameStreet($form->get('nameStreet')->getData());
    $address->setNameStreet2($form->get('nameStreet2')->getData());
    $address->setCodePostal($form->get('codePostal')->getData());
    $address->setVille($form->get('ville')->getData());
    $address->setBusinessPhone($form->get('businessPhone')->getData());
    $address->setMobilePhone($form->get('mobilePhone')->getData());
    $address->setEmail($form->get('email')->getData());

    return $address;
}
}