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

        $html = $this->renderView('pdf/order_template.html.twig', [
            'order' => $order,
            'public_path' => $this->getParameter('kernel.project_dir') . '/public/'
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

        $html = $this->renderView('pdf/invoice_template.html.twig', [
            'invoice' => $invoice,
            'public_path' => $this->getParameter('kernel.project_dir') . '/public/'
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