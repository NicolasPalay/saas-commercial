<?php 

namespace App\Services;

use App\Entity\Devis;
use App\Entity\DevisDetails;
use App\Entity\Invoice;
use App\Entity\InvoiceDetails;
use App\Entity\Order;
use App\Entity\OrderDetail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class TransfertService 
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

    public function transferToInvoice(Order $order)
    {
        $user = $this->security->getUser();
        $company = $user->getCompany();

        // 🔥 Repo facture
        $invoiceRepository = $this->entityManager->getRepository(Invoice::class);

        $lastInvoice = $invoiceRepository->findOneBy(
            ['company' => $company],
            ['id' => 'DESC']
        );

        $prefix = $company->getRefFacture();

        $number = $lastInvoice
            ? (int) str_replace($prefix, '', $lastInvoice->getReference()) + 1
            : 1;

        // 🔥 Création facture
        $invoice = new Invoice();
        $invoice->setReference($prefix . $number)
            ->setClient($order->getClient())
            ->setUser($user)
            ->setCompany($company)
            ->setRaisonSocial($order->getDeliveryLabel())
            ->setIsPay(false)
            ->setTotal($order->getTotal())
            ->setTaxe($order->getTaxe())
            ->setTotalTtc($order->getTotalTtc());


        // 🔥 marquer comme facturé
        if (method_exists($order, 'setIsInvoiced')) {
            $order->setIsInvoiced(true);
        }

        $this->entityManager->persist($invoice);

            $repo = $this->entityManager->getRepository(OrderDetail::class);
            $details = $repo->findBy(['commande' => $order]);

            foreach ($details as $detail) {
                $invoiceDetail = new InvoiceDetails();

                $invoiceDetail
                    ->setCompany($company)
                    ->setInvoice($invoice)
                    ->setProduct($detail->getProduct())
                    ->setTaxe($detail->getTaxe())
                    ->setLabel($detail->getLabel())
                    ->setPrice($detail->getPrice())
                    ->setQuantity($detail->getQuantity())
                    ->setTotal($detail->getTotal());

                $this->entityManager->persist($invoiceDetail); 
            }
        

        $this->entityManager->flush();

        return $invoice;
    }

    public function devisToOrder(Devis $devis)
    {   
        $user= $this->security->getUser();
        $orderRepository = $this->entityManager->getRepository(Order::class);

        $lastInvoice = $orderRepository->findOneBy(
            ['company' => $user->getCompany()],
            ['id' => 'DESC']
        );
       
        $prefix = $user->getCompany()->getRefOrder();
         if (!$lastInvoice) {
                $number = 1;
            } else {
                $lastReference = $lastInvoice->getReference();
                $number = (int) str_replace($prefix, '', $lastReference);
                $number++;
            }

        $addresses = $devis->getClient()->getAddress()->toArray();
        $adress = array_filter($addresses, fn($a) => $a->isDelivery());
        /***
         * create Order
         */
        $order = new Order;
        $order->setReference($prefix . $number);
        $order->setDevis($devis)
            ->setClient($devis->getClient())
            ->setUser($user)
            ->setCompany($user->getCompany())
            ->setDeliveryLabel($devis->getClient()->getRaisonSocial())
            ->setDeliveryStreet($adress[0]->getNameStreet())
            ->setDeliveryStreet2($adress[0]->getNameStreet2())
            ->setDeliveryPostalCode($adress[0]->getCodePostal())
            ->setDeliveryPhone($adress[0]->getMobilePhone())
            ->setTotal($devis->getTotal())
            ->setTaxe($devis->getTaxe())
            ->setTotalTtc($devis->getTotalTTC());
        $this->entityManager->persist($order);
        $this->entityManager->flush();
   
        /**
         * create Orderdetail
         */
        $orderRepository = $this->entityManager->getRepository(DevisDetails::class);
        $devisDetails = $orderRepository->findBy(['devis' => $devis]);
        foreach ($devisDetails as  $devisDetail) {
            $orderDetail = new OrderDetail();
            $orderDetail->setCommande($order)
                        ->setCompany($user->getCompany())
                        ->setProduct($devisDetail->getProduct())
                        ->setTaxe($devisDetail->getTaxe())
                        ->setLabel($devisDetail->getLabel())
                        ->setPrice($devisDetail->getPrice())
                        ->setQuantity($devisDetail->getQuantity())
                        ->setTotal($devisDetail->getTotal());
    
        $this->entityManager->persist($orderDetail);
            
        }
        $this->entityManager->flush();
    }

    public function devisToInvoice(Devis $devis)
    {
        $user = $this->security->getUser();
        $company = $user->getCompany();

        // 🔥 Repo facture
        $invoiceRepository = $this->entityManager->getRepository(Invoice::class);

        $lastInvoice = $invoiceRepository->findOneBy(
            ['company' => $company],
            ['id' => 'DESC']
        );

        $prefix = $company->getRefFacture();

        $number = $lastInvoice
            ? (int) str_replace($prefix, '', $lastInvoice->getReference()) + 1
            : 1;

        // 🔥 Création facture
        $invoice = new Invoice();
        $invoice->setReference($prefix . $number)
            ->setClient($devis->getClient())
            ->setUser($user)
            ->setCompany($company)
            ->setRaisonSocial($devis->getClient()->getRaisonSocial())
            ->setIsPay(false)
            ->setTotal($devis->getTotal())
            ->setTaxe($devis->getTaxe())
            ->setTotalTtc($devis->getTotalTTC());


        // 🔥 marquer comme facturé
        if (method_exists($devis, 'setIsInvoiced')) {
            $devis->setIsInvoiced(true);
        }

        $this->entityManager->persist($invoice);

            $repo = $this->entityManager->getRepository(DevisDetails::class);
            $details = $repo->findBy(['devis' => $devis]);

            foreach ($details as $detail) {
                $invoiceDetail = new InvoiceDetails();

                $invoiceDetail
                    ->setCompany($company)
                    ->setInvoice($invoice)
                    ->setProduct($detail->getProduct())
                    ->setTaxe($detail->getTaxe())
                    ->setLabel($detail->getLabel())
                    ->setPrice($detail->getPrice())
                    ->setQuantity($detail->getQuantity())
                    ->setTotal($detail->getTotal());

                $this->entityManager->persist($invoiceDetail); 
            }
        

        $this->entityManager->flush();

        return $invoice;
    }

}
