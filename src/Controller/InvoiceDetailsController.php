<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\InvoiceDetails;
use App\Form\InvoiceDetailsType;
use App\Form\InvoiceDetailsTypeEdit;
use App\Repository\ProductRepository;
use App\Services\DocumentCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/invoice/details')]
final class InvoiceDetailsController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly DocumentCalculator $calculator
    ) {}

    #[Route('/{uuid}/{reference}/show', name: 'app_invoice_details_new', methods: ['GET', 'POST'])]
    public function new(Request $request,
                        string $uuid,
                        string $reference,
                        InvoiceRepository $invoiceRepository
                        ): Response
    {
        $invoice = $invoiceRepository->findOneByReferenceAndCompanyUuid($reference, $uuid);
        if (!$invoice) {
            throw $this->createNotFoundException('Facture introuvable');
        }
        $user = $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');

        $products = $this->productRepository->findBy(['company' => $invoice->getCompany()]);
        if (!$products) return $this->redirectToRoute('app_product_new');

        $invoiceDetail = new InvoiceDetails();

        $form = $this->createForm(InvoiceDetailsType::class, $invoiceDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $product = $invoiceDetail->getProduct();

            if (!$product) {
                $this->addFlash('error', 'Produit requis.');
                return $this->render('invoice_details/new.html.twig', [
                    'invoice' => $invoice,
                    'invoice_detail' => $invoiceDetail,
                    'form' => $form,
                ]);
            }

            // Hydratation
            $invoiceDetail->setLabel($product->getName());
            $invoiceDetail->setPrice($product->getPrice());
            $invoiceDetail->setTaxe($product->getTaxe());
            $invoiceDetail->setInvoice($invoice);

            // Calcul ligne
            $total = $this->calculator->calculLineHT(
                (string) $invoiceDetail->getPrice(),
                (string) $invoiceDetail->getQuantity(),
                (string) $invoiceDetail->getReduce()
            );

            $invoiceDetail->setTotal($total);

            // Persist
            $this->entityManager->persist($invoiceDetail);
            $this->entityManager->flush();

            // Recalcul global
            $this->calculator->recalculate($invoice, 'getInvoiceDetails');
            $this->entityManager->flush();

            return $this->redirectToRoute(
                'app_invoice_details_new',
                [
                    'uuid' => $invoice->getCompany()->getUuid(),
                    'reference' => $invoice->getReference()
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('invoice_details/new.html.twig', [
            'invoice' => $invoice,
            'invoice_detail' => $invoiceDetail,
            'form' => $form,
        ]);
    }

    #[Route('/{uuid}/{reference}/line/{id}/edit', name: 'app_invoice_details_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request,
                         string $uuid,
                         string $reference,
                         InvoiceDetails $invoiceDetail): Response
    {
        $invoice = $invoiceDetail->getInvoice();
        if ($uuid !== (string) $invoice->getCompany()->getUuid() || $reference !== $invoice->getReference()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(InvoiceDetailsTypeEdit::class, $invoiceDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Recalcul ligne
            $total = $this->calculator->calculLineHT(
                (string) $invoiceDetail->getPrice(),
                (string) $invoiceDetail->getQuantity(),
                (string) $invoiceDetail->getReduce()
            );

            $invoiceDetail->setTotal($total);

            // Recalcul global
            $this->calculator->recalculate($invoice, 'getInvoiceDetails');

            $this->entityManager->flush();

            return $this->redirectToRoute(
                'app_invoice_details_new',
                 [
                     'uuid' => $invoice->getCompany()->getUuid(),
                     'reference' => $invoice->getReference()
                 ],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('invoice_details/edit.html.twig', [
            'invoice' => $invoice,
            'invoice_detail' => $invoiceDetail,
            'form' => $form,
        ]);
    }

    #[Route('/{uuid}/{reference}/line/{id}/delete', name: 'app_invoice_details_delete', methods: ['POST'])]
    public function delete(Request $request,
                           string $uuid,
                           string $reference,
                           InvoiceDetails $invoiceDetail): Response
    {
        $invoice = $invoiceDetail->getInvoice();
        if ($uuid !== (string) $invoice->getCompany()->getUuid() || $reference !== $invoice->getReference()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $invoiceDetail->getId(), $request->getPayload()->getString('_token'))) {
            $this->entityManager->remove($invoiceDetail);
            $this->entityManager->flush();

            // Recalcul global
            $this->calculator->recalculate($invoice, 'getInvoiceDetails');
            $this->entityManager->flush();
        }

        return $this->redirectToRoute(
            'app_invoice_details_new',
            [
                'uuid' => $invoice->getCompany()->getUuid(),
                'reference' => $invoice->getReference()
            ],
            Response::HTTP_SEE_OTHER
        );
    }
}