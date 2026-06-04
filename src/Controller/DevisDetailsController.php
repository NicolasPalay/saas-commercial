<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Devis;
use App\Entity\DevisDetails;
use App\Form\DevisDetailsType;
use App\Form\DevisDetailsTypeEdit;
use App\Repository\ProductRepository;
use App\Services\DocumentCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[Route('/devis/details')]
final class DevisDetailsController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly DocumentCalculator $calculator
    ) {}

    #[Route('/{uuid}/{reference}/show', name: 'app_devis_details_new', methods: ['GET', 'POST'])]
    public function new(#[MapEntity(mapping: ['reference' => 'reference'])] Devis $devis,
                        string $uuid,
                        Request $request
                        ): Response
    {
        $this->denyAccessUnlessGranted('DEVIS_EDIT', $devis);
        if ($uuid !== (string) $devis->getCompany()->getUuid()) {
            throw $this->createAccessDeniedException();
        }
        $user = $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');

        $products = $this->productRepository->findBy(['company' => $devis->getCompany()]);
        if (!$products) return $this->redirectToRoute('app_product_new');

        $devisDetail = new DevisDetails();

        $form = $this->createForm(DevisDetailsType::class, $devisDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $product = $devisDetail->getProduct();
            if ($product->getCompany() !== $devis->getCompany()) {
                throw $this->createAccessDeniedException();
            }

            if (!$product) {
                $this->addFlash('error', 'Produit requis.');
                return $this->render('devis_details/new.html.twig', [
                    'devis' => $devis,
                    'devis_detail' => $devisDetail,
                    'form' => $form,
                ]);
            }

            // Hydratation
            $devisDetail->setLabel($product->getName());
            $devisDetail->setPrice($product->getPrice());
            $devisDetail->setTaxe($product->getTaxe());
            $devisDetail->setDevis($devis);

            // Calcul ligne
            $total = $this->calculator->calculLineHT(
                (string) $devisDetail->getPrice(),
                (string) $devisDetail->getQuantity(),
                (string) $devisDetail->getReduce()
            );

            $devisDetail->setTotal($total);

            // Persist
            $this->entityManager->persist($devisDetail);
            $this->entityManager->flush();

            // Recalcul global
            $this->calculator->recalculate($devis, 'getDevisDetails');
            $this->entityManager->flush();

            return $this->redirectToRoute(
                'app_devis_details_new',
                [
                    'uuid' => $devis->getCompany()->getUuid(),
                    'reference' => $devis->getReference()
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('devis_details/new.html.twig', [
            'devis' => $devis,
            'devis_detail' => $devisDetail,
            'form' => $form,
        ]);
    }

    #[Route('/{uuid}/{reference}/line/{id}/edit', name: 'app_devis_details_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request,
                         string $uuid,
                         string $reference,
                         DevisDetails $devisDetail): Response
    {
        $devis = $devisDetail->getDevis();
        if ($uuid !== (string) $devis->getCompany()->getUuid() || $reference !== $devis->getReference()) {
            throw $this->createAccessDeniedException();
        }

        $this->denyAccessUnlessGranted('DEVIS_EDIT', $devisDetail->getDevis());
        $form = $this->createForm(DevisDetailsTypeEdit::class, $devisDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Recalcul ligne
            $total = $this->calculator->calculLineHT(
                (string) $devisDetail->getPrice(),
                (string) $devisDetail->getQuantity(),
                (string) $devisDetail->getReduce()
            );

            $devisDetail->setTotal($total);

            // Recalcul global
            $this->calculator->recalculate($devis, 'getDevisDetails');

            $this->entityManager->flush();

            return $this->redirectToRoute(
                'app_devis_details_new',
                [
                    'uuid' => $devis->getCompany()->getUuid(),
                    'reference' => $devis->getReference()
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('devis_details/edit.html.twig', [
            'devis' => $devis,
            'devis_detail' => $devisDetail,
            'form' => $form,
        ]);
    }

    #[Route('/{uuid}/{reference}/line/{id}/delete', name: 'app_devis_details_delete', methods: ['POST'])]
    public function delete(Request $request,
                           string $uuid,
                           string $reference,
                           DevisDetails $devisDetail): Response
    {
        $devis = $devisDetail->getDevis();
        if ($uuid !== (string) $devis->getCompany()->getUuid() || $reference !== $devis->getReference()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $devisDetail->getId(), $request->getPayload()->getString('_token'))) {
            $this->entityManager->remove($devisDetail);
            $this->entityManager->flush();

            // Recalcul global propre
            $this->calculator->recalculate($devis, 'getDevisDetails');
            $this->entityManager->flush();
        }

        return $this->redirectToRoute(
            'app_devis_details_new',
            [
                'uuid' => $devis->getCompany()->getUuid(),
                'reference' => $devis->getReference()
            ],
            Response::HTTP_SEE_OTHER
        );
    }
}