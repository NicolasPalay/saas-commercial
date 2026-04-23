<?php

namespace App\Controller;

use App\Repository\DevisRepository;
use App\Repository\InvoiceRepository;
use App\Repository\OrderRepository;
use App\Services\PdfGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PdfController extends AbstractController
{
   #[Route("devis/output-pdf/{id}", name: 'app_devis_output_pdf')]
    public function outputDevis(
        PdfGeneratorService $pdfGeneratorService,
        DevisRepository $devisRepository,
        string $id
    ): Response
    {
        $devis = $devisRepository->find($id);

        if (!$devis) {
            throw $this->createNotFoundException('Devis introuvable');
        }

       $html = $this->renderView('pdf/devis_template.html.twig', [
    'devis' => $devis,
  
    ]);

    $content = $pdfGeneratorService->output($html);

        return new Response(
            $content,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="devis-'.$devis->getReference().'.pdf"',
            ]
        );
    }

    #[Route("order/output-pdf/{id}", name: 'app_order_output_pdf')]
    public function outputOrder(
        PdfGeneratorService $pdfGeneratorService,
        OrderRepository $orderRepository,
        string $id
    ): Response
    {
        $order = $orderRepository->find($id);

        if (!$order) {
            throw $this->createNotFoundException('Commande introuvable');
        }

        $html = $this->renderView('pdf/order.html.twig', [
            'order' => $order,
            'company' => $order->getCompany(),
            'documentType' => 'COMMANDE',
            'reference' => $order->getReference(),
            'date' => $order->getCreatedAt(),
        ]);
       $content = $pdfGeneratorService->output($html);

        return new Response(
            $content,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="devis-'.$order->getReference().'.pdf"',
            ]
        );
    }

    #[Route("invoice/output-pdf/{id}", name: 'app_invoice_output_pdf')]
    public function outputInvoice(
        PdfGeneratorService $pdfGeneratorService,
        InvoiceRepository $invoiceRepository,
        string $id
    ): Response
    {
        $invoice = $invoiceRepository->find($id);

        if (!$invoice) {
            throw $this->createNotFoundException('Devis introuvable');
        }

        $html = $this->renderView('pdf/invoice.html.twig', [
            'invoice' => $invoice,
            'company' => $invoice->getCompany(),
            'documentType' => 'FACTURE',
            'reference' => $invoice->getReference(),
            'date' => $invoice->getCreatedAt(),
        ]);
        $result = iconv('UTF-8', "ISO-8859-1//IGNORE", $html);
        $content = $pdfGeneratorService->output($result );$content = $pdfGeneratorService->output($html);

        return new Response(
            $content,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="devis-'.$invoice->getReference().'.pdf"',
            ]
        );
    }

    #[Route("/stream-pdf/{id}", name: 'app_stream_pdf')]
    public function streamPdf(PdfGeneratorService $pdfGeneratorService, DevisRepository $devisRepository,string $id): Response
    {
        $devis = $devisRepository->findOneBy(['id'=> $id]);
        $html = $this->renderView('devis/devis_template.html.twig', ['devis' => $devis]);
        return $pdfGeneratorService->getStreamResponse($html, 'hello.pdf');
    }
}