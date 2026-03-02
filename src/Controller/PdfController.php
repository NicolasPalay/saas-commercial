<?php

namespace App\Controller;

use App\Repository\DevisRepository;
use App\Services\PdfGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PdfController extends AbstractController
{
   #[Route("/output-pdf/{id}", name: 'app_output_pdf')]
    public function output(
        PdfGeneratorService $pdfGeneratorService,
        DevisRepository $devisRepository,
        string $id
    ): Response
    {
        $devis = $devisRepository->find($id);

        if (!$devis) {
            throw $this->createNotFoundException('Devis introuvable');
        }

        $html = $this->renderView('devis/devis_template.html.twig', [
            'devis' => $devis
        ]);
$result = iconv('UTF-8', "ISO-8859-1//IGNORE", $html);
        $content = $pdfGeneratorService->output($result );

        return new Response(
            $content,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="devis-'.$devis->getReference().'.pdf"',
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