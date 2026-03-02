<?php

namespace App\Services;

use Nucleos\DompdfBundle\Factory\DompdfFactoryInterface;
use Nucleos\DompdfBundle\Wrapper\DompdfWrapperInterface;
use Symfony\Component\HttpFoundation\Response;

class PdfGeneratorService 
{
    public function __construct(
        private readonly DompdfFactoryInterface $factory,
        private readonly DompdfWrapperInterface $wrapper
    )
    {

    }
    public function getStreamResponse(string $html, string $fileName): Response
    {

     $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

    // Ajoute charset si absent
    if (!str_contains($html, 'charset=UTF-8')) {
        $html = '<meta charset="UTF-8">' . $html;
    }

    return $this->wrapper->getStreamResponse($html, $fileName);
    }

    public function output(string $html): string
    {
        $dompdf = $this->factory->create();

        $html = $this->sanitizeUtf8($html);
        $html = preg_replace('/^\xEF\xBB\xBF/', '', $html);
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('isRemoteEnabled', true);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4');

        $dompdf->render();

        return $dompdf->output();
    }


    private function sanitizeUtf8(string $input): string
    {
        // supprime bytes invalides
        $input = mb_convert_encoding($input, 'UTF-8', 'UTF-8');

        // supprime caractères de contrôle invalides
        $input = preg_replace('/[^\x09\x0A\x0D\x20-\x7E\xA0-\x{10FFFF}]/u', '', $input);

        // convertit en HTML entities (clé pour Dompdf)
        return mb_convert_encoding($input, 'HTML-ENTITIES', 'UTF-8');
    }
}