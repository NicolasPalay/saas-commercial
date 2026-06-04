<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Form\OrderTypeEdit;
use App\Repository\OrderRepository;
use App\Services\PdfGeneratorService;
use App\Services\SendMailService;
use App\Services\TransfertService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

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
            $order->setUser($user);

            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_order_details_new',
                [
                    'uuid' => $company->getUuid(),
                    'reference' => $order->getReference()
                ]
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
            $order->setUser($user);

            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_order_details_new',
                [
                    'uuid' => $company->getUuid(),
                    'reference' => $order->getReference()
                ]
            );
        }

        return $this->render('order/new.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

#[Route('/toinvoice/{uuid}/{reference}', name: 'app_orderInInvoice', methods: ['GET'])]
    public function inInvoice(
        #[MapEntity(mapping: ['reference' => 'reference'])] Order $order,
        string $uuid,
        TransfertService $orderVsInvoiceService
    ): Response {
        if ($uuid !== (string) $order->getCompany()->getUuid()) {
            throw $this->createAccessDeniedException();
        }
        $this->denyAccessUnlessGranted('EDIT', $order);
        $orderVsInvoiceService->transferToInvoice($order);

        return $this->redirectToRoute('app_invoice_index');
    }

    #[Route("/send/{uuid}/{reference}", name: 'app_order_send')]
    public function sendOrder(
        PdfGeneratorService $pdfGeneratorService,
        #[MapEntity(mapping: ['reference' => 'reference'])] Order $order,
        string $uuid,
        SendMailService $mailer
    ): Response {
        if ($uuid !== (string) $order->getCompany()->getUuid()) {
            throw $this->createAccessDeniedException();
        }

        // 1. Génération du PDF
        $html = $this->renderView('pdf/order_template.html.twig', [
            'order' => $order,
        ]);

        $pdfContent = $pdfGeneratorService->output($html);
        $client = $order->getClient();
        $email = null;

        foreach ($client->getAddress() as $address) {
            if ($address->isIsDefault()) {
                $email = $address->getEmail();
                break;
            }
        }

        if(!$email) {
            $this->addFlash('error', 'Le client n\'a pas d\'adresse email. Veuillez en ajouter une pour pouvoir envoyer la commande.');
            return $this->redirectToRoute(
                'app_order_details_new',
                [
                    'uuid' => $order->getCompany()->getUuid(),
                    'reference' => $order->getReference()
                ],
                Response::HTTP_SEE_OTHER
            );
        }
        // 2. Création de l’email
        $mailer->sendAttachment(
            $this->getUser()->getEmail(),
            $email,
            'Votre commande '.$order->getReference(),
            'commande',
            [
                'order' => $order
            ],
            [
                [
                    'data' => $pdfContent,
                    'name' => 'commande-'.$order->getReference().'.pdf',
                    'type' => 'application/pdf'
                ]
            ]
        );
        $this->addFlash('success', 'La commande a été envoyée avec succès.');

        return $this->redirectToRoute(
                'app_order_details_new',
                [
                    'uuid' => $order->getCompany()->getUuid(),
                    'reference' => $order->getReference()
                ],
                Response::HTTP_SEE_OTHER
            );
    }
    
    #[Route('/{uuid}/{reference}/edit', name: 'app_order_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(mapping: ['reference' => 'reference'])] Order $order,
        string $uuid,
        EntityManagerInterface $entityManager
    ): Response {
        if ($uuid !== (string) $order->getCompany()->getUuid()) {
            throw $this->createAccessDeniedException();
        }

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
                [
                    'uuid' => $order->getCompany()->getUuid(),
                    'reference' => $order->getReference()
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/{uuid}/{reference}/delete', name: 'app_order_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        #[MapEntity(mapping: ['reference' => 'reference'])] Order $order,
        string $uuid,
        EntityManagerInterface $entityManager
    ): Response {
        if ($uuid !== (string) $order->getCompany()->getUuid()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
    }
}
