<?php

namespace App\Controller;

use App\Entity\Devis;

use App\Form\DevisType;
use App\Form\DevisTypeEdit;
use App\Repository\AddressRepository;
use App\Repository\DevisRepository;
use App\Services\DevisAddress;
use App\Services\PdfGeneratorService;
use App\Services\SendMailService;
use App\Services\TransfertService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/devis')]
final class DevisController extends AbstractController
{
    #[Route(name: 'app_devis_index', methods: ['GET', 'POST'])]
    public function index(DevisRepository $devisRepository, Request $request, EntityManagerInterface $entityManager, AddressRepository $addressRepository, DevisAddress $devisAddress): Response
    { 
        $user = $this->getUser();
        if(!$user) return $this->redirectToRoute('app_login');

        $company = $user->getCompany();
        $devi = $devisRepository->findBy(['company' => $company]);
       
        $prefix = $company->getRefDevis();
        
        $count = $devisRepository->CountDevisByCompany($company->getId());
           
        $lastDevis = $devisRepository->findOneBy(
            ['company' => $company],
            ['id' => 'DESC']
        );

        $devis = new Devis();
        $form = $this->createForm(DevisType::class, $devis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($count >= 15) {
                $this->addFlash('info', 'Vous avez atteint la limite de '.$count.' devis pour votre entreprise. Veuillez souscrire à un abonnement pour continuer à créer des devis.');
                return $this->redirectToRoute('app_subscription_index');
            }

            $client = $devis->getClient();
            $devisAddress->setFromClient($client, $devis);


            if (!$lastDevis) {
                $number = 1;
            } else {
                $lastReference = $lastDevis->getReference();
                $number = (int) str_replace($prefix, '', $lastReference);
                $number++;
            }
            $devis->setUser($user);
            $devis->setReference($prefix . $number);
            $devis->setCompany($company);

            $entityManager->persist($devis);
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_devis_details_new',
                [
                    'uuid' => $devis->getCompany()->getUuid(),
                    'reference' => $devis->getReference()
                ]
            );
        }

        return $this->render('devis/index.html.twig', [
            'devis' => $devi,
            'user' => $user,
            'form' => $form,
            'entity' => Devis::class,
            'entityText' => 'devis',
            'headers'=>["reference", "client", "total", "createdAt","status"],
        ]);
    }


    #[Route('/toorder/{uuid}/{reference}', name: 'app_toOrder', methods: ['GET'])]
    public function toOrder(DevisRepository $devisRepository, string $uuid, string $reference, TransfertService $transfertService): Response
    {
        $user = $this->getUser();
        $devis = $devisRepository->findOneByReferenceAndCompanyUuid($reference, $uuid);
        $this->denyAccessUnlessGranted('EDIT', $devis);
        if($devis) {  
            $transfertService->devisToOrder($devis);
        }
    return $this->redirectToRoute('app_order_index');
    }

    #[Route('/toinvoice/{uuid}/{reference}', name: 'app_devis_toInvoice', methods: ['GET'])]
    public function devisToInvoice(DevisRepository $devisRepository, string $uuid, string $reference, TransfertService $devisVsInvoiceService): Response
    {
    $user = $this->getUser();

    $devis = $devisRepository->findOneByReferenceAndCompanyUuid($reference, $uuid);
    $this->denyAccessUnlessGranted('EDIT', $devis);
    if($devis) {
        $devisVsInvoiceService->devisToInvoice($devis);
    }
    return $this->redirectToRoute('app_invoice_index');
    }

    #[Route('/{uuid}/{reference}/edit', name: 'app_devis_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, string $uuid, string $reference, DevisRepository $devisRepository, EntityManagerInterface $entityManager): Response
    {
        $devi = $devisRepository->findOneByReferenceAndCompanyUuid($reference, $uuid);
        if (!$devi) {
            throw $this->createNotFoundException('Devis introuvable');
        }
        $this->denyAccessUnlessGranted('EDIT', $devi);
        $form = $this->createForm(DevisTypeEdit::class, $devi, [
            'company' => $devi->getCompany(),
            'currentClient' => $devi->getClient(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
               $address = $form->get('address')->getData();
           //dd($address->getNameStreet());
            if($address) {
                $devi->setDeliveryLabel($devi->getClient()->getRaisonSocial())
                    ->setDeliveryStreet2($address->getNameStreet2())
                    ->setDeliveryPostalCode($address->getCodePostal())
                    ->setDeliveryCity($address->getVille())
                    ->setDeliveryPhone($address->getMobilePhone())
                    ->setDeliveryStreet($address->getEmail()) ;
                    $devi->setDeliveryStreet($address->getNameStreet());
            }


            $entityManager->flush();

            return $this->redirectToRoute(
                'app_devis_details_new',
                [
                    'uuid' => $devi->getCompany()->getUuid(),
                    'reference' => $devi->getReference()
                ]
            );
        
        }

        return $this->render('devis/edit.html.twig', [
            'devi' => $devi,
            'form' => $form,
        ]);
    }
    #[Route("/send/{uuid}/{reference}", name: 'app_devis_send')]
    public function sendDevis(
        PdfGeneratorService $pdfGeneratorService,
        DevisRepository $devisRepository,
        SendMailService $mailer,
        string $uuid,
        string $reference
    ): Response {
        $user = $this->getUser();
        $devis = $devisRepository->findOneByReferenceAndCompanyUuid($reference, $uuid);

        if (!$devis) {
            throw $this->createNotFoundException('Devis introuvable');
        }

        // 1. Génération du PDF
        $html = $this->renderView('pdf/devis_template.html.twig', [
            'devis' => $devis,
        ]);

        $pdfContent = $pdfGeneratorService->output($html);
        $client = $devis->getClient();
        $email = null;

        foreach ($client->getAddress() as $address) {
            if ($address->isIsDefault()) {
                $email = $address->getEmail();
                break;
            }
        }

        if(!$email) {
            $this->addFlash('error', 'Le client n\'a pas d\'adresse email. Veuillez en ajouter une pour pouvoir envoyer le devis.');
            return $this->redirectToRoute(
                'app_devis_details_new',
                [
                    'uuid' => $devis->getCompany()->getUuid(),
                    'reference' => $devis->getReference()
                ],
                Response::HTTP_SEE_OTHER
            );
        }
        // 2. Création de l’email
        $mailer->sendAttachment(
            $this->getUser()->getEmail(),
            $email,
            'Votre devis '.$devis->getReference(),
            'devis',
            [
                'devis' => $devis
            ],
            [
                [
                    'data' => $pdfContent,
                    'name' => 'devis-'.$devis->getReference().'.pdf',
                    'type' => 'application/pdf'
                ]
            ]
        );
        $this->addFlash('success', 'Le devis a été envoyé avec succès.');

        return $this->redirectToRoute(
                'app_devis_details_new',
                [
                    'uuid' => $devis->getCompany()->getUuid(),
                    'reference' => $devis->getReference()
                ],
                Response::HTTP_SEE_OTHER
            );
    }

    #[Route('/{uuid}/{reference}/delete', name: 'app_devis_delete', methods: ['POST'])]
    public function delete(Request $request, string $uuid, string $reference, DevisRepository $devisRepository, EntityManagerInterface $entityManager): Response
    {
        $devi = $devisRepository->findOneByReferenceAndCompanyUuid($reference, $uuid);
        if (!$devi) {
            throw $this->createNotFoundException('Devis introuvable');
        }
            $this->denyAccessUnlessGranted('DELETE', $devi);
        if ($this->isCsrfTokenValid('delete'.$devi->getId(), $request->getPayload()->getString('_token'))) {

            $entityManager->remove($devi);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_devis_index', [], Response::HTTP_SEE_OTHER);
    }
}
