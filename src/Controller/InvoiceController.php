<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Form\InvoiceType;
use App\Form\InvoiceTypeEdit;
use App\Repository\InvoiceRepository;
use App\Services\PdfGeneratorService;
use App\Services\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/invoice')]
final class InvoiceController extends AbstractController
{   
    public array $headers;
    public $entity;
    public string $entityText;
    
    #[Route(name: 'app_invoice_index', methods: ['GET', 'POST'])]
    public function index(InvoiceRepository $invoiceRepository, Request $request, EntityManagerInterface $entityManager): Response
    {

        $user = $this->getUser();
        $company = $user->getCompany();

 $invoice = new Invoice();
        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            

    $lastInvoice = $invoiceRepository->findOneBy(
        ['company' => $company],
        ['id' => 'DESC']
    );
        
       
        $prefix = $company->getRefFacture();
         if (!$lastInvoice) {
                $number = 1;
            } else {
                $lastReference = $lastInvoice->getReference();
                $number = (int) str_replace($prefix, '', $lastReference);
                $number++;
            }
        $client = $form->get('client')->getData();
        
        $invoice = new Invoice;
        $invoice->setReference($prefix . $number);
        $invoice
                ->setUser($user)
                ->setCompany($company)
                ->setRaisonSocial($client->getRaisonSocial())
                ->setIsPay(false)
                ->setTotal(0)
                ->setTaxe(0)
                ->setTotalTtc(0);
        


            $entityManager->persist($invoice);
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice/index.html.twig', [
            'invoices' => $invoiceRepository->findBy(['company'=> $company]),
             'form' => $form,
             'entity' => Invoice::class,
             'entityText' => 'invoice',
            'headers'=>["reference", "client", "total", "createdAt"],
        ]);
    }


    #[Route('/new', name: 'app_invoice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');
        $invoice = new Invoice();
        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($invoice);
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice/new.html.twig', [
            'invoice' => $invoice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_invoice_show', methods: ['GET'])]
    public function show(Invoice $invoice): Response
    {
        return $this->render('invoice/show.html.twig', [
            'invoice' => $invoice,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_invoice_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');
        $this->denyAccessUnlessGranted('EDIT', $invoice);
        $form = $this->createForm(InvoiceTypeEdit::class, $invoice, [
            'company' => $invoice->getCompany(),
            'currentClient' => $invoice->getClient(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
               $address = $form->get('address')->getData();
           //dd($address->getNameStreet());
            if($address) {
                $invoice->setRaisonSocial($invoice->getClient()->getRaisonSocial())
                    ->setNameStreet2($address->getNameStreet2())
                    ->setCodePostal($address->getCodePostal())
                    ->setVille($address->getVille())
                    ->setEmail($address->getEmail()) ;
                    $invoice->setNameStreet($address->getNameStreet());
            }
            $entityManager->flush();

             return $this->redirectToRoute(
                'app_invoice_details_new',
                ['id' => $invoice->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('invoice/edit.html.twig', [
            'invoice' => $invoice,
            'form' => $form,
        ]);
    }
    #[Route("invoice/send/{id}", name: 'app_invoice_send')]
    public function sendInvoice(
        PdfGeneratorService $pdfGeneratorService,
        InvoiceRepository $invoiceRepository,
        SendMailService $mailer,
        string $id
    ): Response {
        $invoice = $invoiceRepository->find($id);

        if (!$invoice) {
            throw $this->createNotFoundException('Facture introuvable');
        }

        // 1. Génération du PDF
        $html = $this->renderView('pdf/invoice.html.twig', [
            'invoice' => $invoice,
            'company' => $invoice->getCompany(),
            'documentType' => 'FACTURE',
            'reference' => $invoice->getReference(),
            'date' => $invoice->getCreatedAt(),
        ]);

        $pdfContent = $pdfGeneratorService->output($html);
        $client = $invoice->getClient();
        $email = null;

        foreach ($client->getAddress() as $address) {
            if ($address->isIsDefault()) {
                $email = $address->getEmail();
                break;
            }
        }

        if(!$email) {
            $this->addFlash('error', 'Le client n\'a pas d\'adresse email. Veuillez en ajouter une pour pouvoir envoyer la facture.');
            return $this->redirectToRoute(
                'app_invoice_details_new',
                ['id' => $invoice->getId()],
                Response::HTTP_SEE_OTHER
            );
        }
        // 2. Création de l’email
        $mailer->sendAttachment(
            $this->getUser()->getEmail(),
            $email,
            'Votre facture '.$invoice->getReference(),
            'invoice',
            [
                'invoice' => $invoice,
            ],
            [
                [
                    'data' => $pdfContent,
                    'name' => 'facture-'.$invoice->getReference().'.pdf',
                    'type' => 'application/pdf'
                ]
            ]
        );
        $this->addFlash('success', 'La facture a été envoyée avec succès.');

        return $this->redirectToRoute(
                'app_invoice_details_new',
                ['id' => $invoice->getId()],
                Response::HTTP_SEE_OTHER
            );
    }
    
    #[Route('/{id}', name: 'app_invoice_delete', methods: ['POST'])]
    public function delete(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('DELETE', $invoice);
        if ($this->isCsrfTokenValid('delete'.$invoice->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($invoice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
    }
}
