<?php

namespace App\Controller;

use App\Entity\QuoteDetails;
use App\Form\QuoteDetailsType;
use App\Repository\QuoteDetailsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/quote/details')]
final class QuoteDetailsController extends AbstractController
{
    #[Route(name: 'app_quote_details_index', methods: ['GET'])]
    public function index(QuoteDetailsRepository $quoteDetailsRepository): Response
    {
        return $this->render('quote_details/index.html.twig', [
            'quote_details' => $quoteDetailsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_quote_details_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $quoteDetail = new QuoteDetails();
        $form = $this->createForm(QuoteDetailsType::class, $quoteDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($quoteDetail);
            $entityManager->flush();

            return $this->redirectToRoute('app_quote_details_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('quote_details/new.html.twig', [
            'quote_detail' => $quoteDetail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_quote_details_show', methods: ['GET'])]
    public function show(QuoteDetails $quoteDetail): Response
    {
        return $this->render('quote_details/show.html.twig', [
            'quote_detail' => $quoteDetail,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_quote_details_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, QuoteDetails $quoteDetail, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(QuoteDetailsType::class, $quoteDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_quote_details_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('quote_details/edit.html.twig', [
            'quote_detail' => $quoteDetail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_quote_details_delete', methods: ['POST'])]
    public function delete(Request $request, QuoteDetails $quoteDetail, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$quoteDetail->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($quoteDetail);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_quote_details_index', [], Response::HTTP_SEE_OTHER);
    }
}
