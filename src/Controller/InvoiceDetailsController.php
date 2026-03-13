<?php

namespace App\Controller;

use App\Entity\InvoiceDetails;
use App\Form\InvoiceDetailsType;
use App\Repository\InvoiceDetailsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/invoice/details')]
final class InvoiceDetailsController extends AbstractController
{
    #[Route(name: 'app_invoice_details_index', methods: ['GET'])]
    public function index(InvoiceDetailsRepository $invoiceDetailsRepository): Response
    {
        return $this->render('invoice_details/index.html.twig', [
            'invoice_details' => $invoiceDetailsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_invoice_details_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $invoiceDetail = new InvoiceDetails();
        $form = $this->createForm(InvoiceDetailsType::class, $invoiceDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($invoiceDetail);
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_details_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice_details/new.html.twig', [
            'invoice_detail' => $invoiceDetail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_invoice_details_show', methods: ['GET'])]
    public function show(InvoiceDetails $invoiceDetail): Response
    {
        return $this->render('invoice_details/show.html.twig', [
            'invoice_detail' => $invoiceDetail,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_invoice_details_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, InvoiceDetails $invoiceDetail, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InvoiceDetailsType::class, $invoiceDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_details_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice_details/edit.html.twig', [
            'invoice_detail' => $invoiceDetail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_invoice_details_delete', methods: ['POST'])]
    public function delete(Request $request, InvoiceDetails $invoiceDetail, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$invoiceDetail->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($invoiceDetail);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_invoice_details_index', [], Response::HTTP_SEE_OTHER);
    }
}
