<?php
namespace App\Services;

use App\Entity\Client;
use App\Entity\Devis;
use App\Repository\AddressRepository;

class DevisAddress
{
    public function __construct(
        private AddressRepository $addressRepository
    ) {}

    public function setFromClient(Client $client, Devis $devis): void
    {
        $address = $this->addressRepository->findOneBy([
            'client' => $client,
            'isDefault' => true
        ]);

        if (!$address) {
            throw new \Exception('Aucune adresse par défaut trouvée pour ce client.');
        }

        $devis->setDeliveryCity($address->getVille());
        $devis->setDeliveryStreet($address->getNameStreet());
        $devis->setDeliveryPostalCode($address->getCodePostal());
        $devis->setDeliveryLabel($client->getRaisonSocial());
        if($address->getMobilePhone()) $devis->setDeliveryPhone($address->getMobilePhone());
    }
}