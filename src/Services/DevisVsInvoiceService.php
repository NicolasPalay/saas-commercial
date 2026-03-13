<?php 

namespace App\Services;

use App\Entity\Devis;
use App\Entity\Invoice;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class DevisVsInvoiceService 
{
    private Security $security;
    private EntityManagerInterface $entityManager;

    public function __construct(
        Security $security,
        EntityManagerInterface $entityManager
    ) {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    public function devisToInvoice(Devis $devis)
    {   $user= $this->security->getUser();
        
    $invoiceRepository = $this->entityManager->getRepository(Invoice::class);

    $lastInvoice = $invoiceRepository->findOneBy(
        ['company' => $user->getCompany()],
        ['id' => 'DESC']
    );
        
       
        $prefix = $user->getCompany()->getRefFacture();
         if (!$lastInvoice) {
                $number = 1;
            } else {
                $lastReference = $lastInvoice->getReference();
                $number = (int) str_replace($prefix, '', $lastReference);
                $number++;
            }

        
        $invoice = new Invoice;
        $invoice->setReference($prefix . $number);
        $invoice->setDevis($devis)
                ->setClient($devis->getClient())
                ->setUser($user)
                ->setCompany($user->getCompany())
                ->setRaisonSocial($devis->getDeliveryLabel())
                ->setIsPay(false)
                ->setPriceTotalHt($devis->getTotal())
                ->setTaxeTotal($devis->getTaxe())
                ->setPriceTotalTTC($devis->getTotalTTC());
        $devis->setIsInvoiced(true);
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();


    }

}
