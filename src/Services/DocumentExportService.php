<?php

namespace App\Services;

use App\Entity\Devis;
use App\Entity\Invoice;
use App\Entity\Order;
use Dompdf\Dompdf;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Twig\Environment;

class DocumentExportService
{
    public function __construct(
        private Environment $twig,
        private SendMailService $mailService
    ) {}

    /**
     * Générer PDF Devis (avec ou sans prix)
     */
    public function generateDevisPDF(Devis $devis, bool $withPrices = true): string
    {
        $html = $this->twig->render('exports/devis_pdf.html.twig', [
            'devis' => $devis,
            'showPrices' => $withPrices,
        ]);

        return $this->generatePDF($html, 'devis-' . $devis->getReference());
    }

    /**
     * Générer PDF Facture (avec ou sans prix)
     */
    public function generateInvoicePDF(Invoice $invoice, bool $withPrices = true): string
    {
        $html = $this->twig->render('exports/invoice_pdf.html.twig', [
            'invoice' => $invoice,
            'showPrices' => $withPrices,
        ]);

        return $this->generatePDF($html, 'facture-' . $invoice->getReference());
    }

    /**
     * Générer PDF Commande (avec ou sans prix)
     */
    public function generateOrderPDF(Order $order, bool $withPrices = true): string
    {
        $html = $this->twig->render('exports/order_pdf.html.twig', [
            'order' => $order,
            'showPrices' => $withPrices,
        ]);

        return $this->generatePDF($html, 'commande-' . $order->getReference());
    }

    /**
     * Générer PDF (moteur interne)
     */
    private function generatePDF(string $html, string $filename): string
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        $filepath = sys_get_temp_dir() . '/' . $filename . '.pdf';
        file_put_contents($filepath, $dompdf->output());

        return $filepath;
    }

    /**
     * Envoyer Devis par email
     */
    public function sendDevisByEmail(Devis $devis, string $recipientEmail, bool $withPrices = true): void
    {
        $pdfPath = $this->generateDevisPDF($devis, $withPrices);

        try {
            $this->mailService->send(
                $devis->getCompany()->getEmail() ?? 'noreply@company.local',
                $recipientEmail,
                "Devis {$devis->getReference()} - {$devis->getCompany()->getRaisonSocial()}",
                'devis_email',
                [
                    'devis' => $devis,
                    'attachment' => $pdfPath,
                ]
            );
        } finally {
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }
        }
    }

    /**
     * Envoyer Facture par email
     */
    public function sendInvoiceByEmail(Invoice $invoice, string $recipientEmail, bool $withPrices = true): void
    {
        $pdfPath = $this->generateInvoicePDF($invoice, $withPrices);

        try {
            $this->mailService->send(
                $invoice->getCompany()->getEmail() ?? 'noreply@company.local',
                $recipientEmail,
                "Facture {$invoice->getReference()} - {$invoice->getCompany()->getRaisonSocial()}",
                'invoice_email',
                [
                    'invoice' => $invoice,
                    'attachment' => $pdfPath,
                ]
            );
        } finally {
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }
        }
    }

    /**
     * Envoyer Commande par email
     */
    public function sendOrderByEmail(Order $order, string $recipientEmail, bool $withPrices = true): void
    {
        $pdfPath = $this->generateOrderPDF($order, $withPrices);

        try {
            $this->mailService->send(
                $order->getCompany()->getEmail() ?? 'noreply@company.local',
                $recipientEmail,
                "Commande {$order->getReference()} - {$order->getCompany()->getRaisonSocial()}",
                'order_email',
                [
                    'order' => $order,
                    'attachment' => $pdfPath,
                ]
            );
        } finally {
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }
        }
    }
}
