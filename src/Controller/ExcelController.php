<?php

namespace App\Controller;

use App\Form\ExcelType;
use App\Services\ImportExcelService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExcelController extends AbstractController
{
    #[Route('/excel/import', name: 'app_excel', methods: ['GET', 'POST'])]
    public function import(
        Request $request,
        ImportExcelService $importService,
        EntityManagerInterface $em
    ) : Response {


        $user = $this->getUser();
        $company = $user->getCompany();
        $taxe = $company->getTaxes()[0] ?? null;
    
       $form = $this->createForm(ExcelType::class, null, [
            'action' => $this->generateUrl('app_excel'),
            'method' => 'POST',
            'attr' => ['enctype' => 'multipart/form-data'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
        $file = $form->get('file')->getData();

        if (!$file) {
            $this->addFlash('error', 'Fichier manquant');
            return $this->redirectToRoute('app_product_index');
        }

        $count = $importService->importProducts(
            $file->getPathname(),
            $em,
            $company,
            $taxe

        );

        $this->addFlash('success', "$count produits importés");

        return $this->redirectToRoute('app_product_index');
        }

    return $this->render('excel/import.html.twig', [
            'form' => $form->createView(),

        ]);
}

  

    #[Route('/excel/export', name: 'app_excel_export', methods: ['GET', 'POST'])]
    public function export(): Response
    {
        return $this->render('excel/export.html.twig', [

        ]);
    }
}
