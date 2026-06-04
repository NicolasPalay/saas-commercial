<?php

namespace App\Controller;

use App\Entity\Devis;
use App\Entity\Invoice;
use App\Entity\Order;
use App\Repository\DevisRepository;
use App\Repository\InvoiceRepository;
use App\Repository\OrderRepository;
use App\Services\PdfGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

class PdfController extends AbstractController
{
   #[Route("devis/output-pdf/{uuid}/{reference}", name: 'app_devis_output_pdf')]
    public function outputDevis(
        PdfGeneratorService $pdfGeneratorService,
        #[MapEntity(mapping: ['reference' => 'reference'])] Devis $devis,
        string $uuid
    ): Response
    {
        if ($uuid !== (string) $devis->getCompany()->getUuid()) {
            throw $this->createAccessDeniedException();
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

    #[Route("order/output-pdf/{uuid}/{reference}", name: 'app_order_output_pdf')]
    public function outputOrder(
        PdfGeneratorService $pdfGeneratorService,
        #[MapEntity(mapping: ['reference' => 'reference'])] Order $order,
        string $uuid
    ): Response
    {
        if ($uuid !== (string) $order->getCompany()->getUuid()) {
            throw $this->createAccessDeniedException();
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
                'Content-Disposition' => 'inline; filename="commande-'.$order->getReference().'.pdf"',
            ]
        );
    }

    #[Route("invoice/output-pdf/{uuid}/{reference}", name: 'app_invoice_output_pdf')]
    public function outputInvoice(
        PdfGeneratorService $pdfGeneratorService,
        #[MapEntity(mapping: ['reference' => 'reference'])] Invoice $invoice,
        string $uuid
    ): Response
    {
        if ($uuid !== (string) $invoice->getCompany()->getUuid()) {
            throw $this->createAccessDeniedException();
        }

        $html = $this->renderView('pdf/invoice.html.twig', [
            'invoice' => $invoice,
            'company' => $invoice->getCompany(),
            'documentType' => 'FACTURE',
            'reference' => $invoice->getReference(),
            'date' => $invoice->getCreatedAt(),
        ]);
        $content = $pdfGeneratorService->output($html);

        return new Response(
            $content,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="facture-'.$invoice->getReference().'.pdf"',
            ]
        );
    }

    #[Route("/stream-pdf/{id}", name: 'app_stream_pdf')]
    public function streamPdf(PdfGeneratorService $pdfGeneratorService, DevisRepository $devisRepository,string $id): Response
    {
        $user = $this->getUser();
        $devis = $devisRepository->findOneBy(['id'=> $id, 'company' => $user?->getCompany()]);
        $html = $this->renderView('devis/devis_template.html.twig', ['devis' => $devis]);
        return $pdfGeneratorService->getStreamResponse($html, 'hello.pdf');
    }
}
