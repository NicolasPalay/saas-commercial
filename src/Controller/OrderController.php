<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Form\OrderTypeEdit;
use App\Repository\OrderRepository;
use App\Services\TransfertService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commande')]
final class OrderController extends AbstractController
{
    #[Route(name: 'app_order_index', methods: ['GET','POST'])]
    public function index(OrderRepository $orderRepository, Request $request, EntityManagerInterface $entityManager): Response
    {


       $user = $this->getUser();
       if(!$user) return $this->redirectToRoute('app_login');
        $company = $user->getCompany();
        $prefix = $company->getRefOrder();
        
        $count = $orderRepository->CountOrderByCompany($company->getId());
           
        $lastOrder = $orderRepository->findOneBy(
            ['company' => $company],
            ['id' => 'DESC']
        );

        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($count >= 10) {
                $this->addFlash('info', 'Vous avez atteint la limite de '.$count.' order pour votre entreprise. Veuillez souscrire à un abonnement pour continuer à créer des order.');
                return $this->redirectToRoute('app_subscription_index');
            }
             $client= $form->get('client')->getData();
             $addresses = $client->getAddress()->toArray();

            $address = current(array_filter(
                $addresses,
                fn($address) => $address->isDelivery()
            ));

            if (!$address) {
                $address = $addresses[0] ?? null;
            }

            if (!$address) {
                throw new \Exception('Aucune adresse trouvée');
            }
             

            $order->setDeliveryLabel($client->getRaisonSocial())
                    ->setDeliveryStreet($address->getNameStreet())
                    ->setDeliveryStreet2($address->getNameStreet2())
                    ->setDeliveryPostalCode($address->getCodePostal())
                    ->setDeliveryCity($address->getVille())
                    ->setDeliveryPhone($address->getMobilePhone())
                    ->setDeliveryStreet($address->getEmail()) ;
           
            if (!$lastOrder) {
                $number = 1;
            } else {
                $lastReference = $lastOrder->getReference();
                $number = (int) str_replace($prefix, '', $lastReference);
                $number++;
            }
            $order->setReference($prefix . $number);
            $order->setCompany($company);

            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_order_details_new',
                ['id' => $order->getId()]
            );
        }

        return $this->render('order/index.html.twig', [
            'orders' => $orderRepository->findBy(['company' => $company]),
            'user' => $user,
            'form' => $form,
            'entity' => Order::class,
             'entityText' => 'order',
            'headers'=>["reference", "client", "total", "createdAt"],
        ]);
    }

    #[Route('/new', name: 'app_order_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, OrderRepository $orderRepository): Response
    {       
        $user = $this->getUser();
        if(!$user) return $this->redirectToRoute('app_login');
        $company = $user->getCompany();
            $lastOrder = $orderRepository->findOneBy(
            ['company' => $company],
            ['id' => 'DESC']
        );
        $prefix = $company->getRefOrder();
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $client= $form->get('client')->getData();
            $addresses = $client->getAddress();

            $address = array_filter($addresses->toArray(), function ($address) {
                return $address->isDelivery();
            });
            if(!$address){
                $address = $client->getAddress()[0];
            }
                // $order->setDeliveryLabel($address->getDeliveryLabel())
                //     ->setDeliveryStreet($address->getDeliveryStreet())
                //     ->setDeliveryStreet2($address->getDeliveryStreet2())
                //     ->setDeliveryPostalCode($address->getDeliveryPostalCode())
                //     ->setDeliveryCity($address->getDeliveryCity())
                //     ->setDeliveryPhone($address->getDeliveryPhone())
                //     ->setDeliveryStreet($address->getDeliveryStreet()) ;
            
            $order->setDeliveryLabel($address->getRaisonSocial())
                    ->setDeliveryStreet($address->getNameStreet())
                    ->setDeliveryStreet2($address->getNameStreet2())
                    ->setDeliveryPostalCode($address->getCodePostal())
                    ->setDeliveryCity($address->getVille())
                    ->setDeliveryPhone($address->getMobilePhone())
                    ->setDeliveryStreet($address->getEmail()) ;
            

             if (!$lastOrder) {
                $number = 1;
            } else {
                $lastReference = $lastOrder->getReference();
                $number = (int) str_replace($prefix, '', $lastReference);
                $number++;
            }

            $order->setReference($prefix . $number);
            $order->setCompany($company);

            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('order/new.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

#[Route('/toinvoice/{id}', name: 'app_orderInInvoice', methods: ['GET'])]
    public function inInvoice(OrderRepository $orderRepository, int $id, TransfertService $orderVsInvoiceService): Response
    {
    $user = $this->getUser();
    if(!$user) return $this->redirectToRoute('app_login');
    
    $order = $orderRepository->findOneBy(['id' => $id]);
    $this->denyAccessUnlessGranted('EDIT', $order);
    $orderVsInvoiceService->transferToInvoice($order);

    return $this->redirectToRoute('app_invoice_index');
    }

    #[Route('/{id}', name: 'app_order_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_order_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrderTypeEdit::class, $order, [
            'company' => $order->getCompany(),
            'currentClient' => $order->getClient(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address = $form->get('address')->getData();
           //dd($address->getNameStreet());
            if($address) {
                $order->setDeliveryLabel($order->getClient()->getRaisonSocial())
                    ->setDeliveryStreet2($address->getNameStreet2())
                    ->setDeliveryPostalCode($address->getCodePostal())
                    ->setDeliveryCity($address->getVille())
                    ->setDeliveryPhone($address->getMobilePhone())
                    ->setDeliveryStreet($address->getEmail()) ;
                    $order->setDeliveryStreet($address->getNameStreet());
            }
            
            $entityManager->flush();

             return $this->redirectToRoute(
                'app_order_details_new',
                ['id' => $order->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_order_delete', methods: ['POST'])]
    public function delete(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
    }
}
