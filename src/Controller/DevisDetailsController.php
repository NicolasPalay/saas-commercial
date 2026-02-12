<?php

namespace App\Controller;

use App\Entity\Devis;
use App\Entity\DevisDetails;
use App\Form\DevisDetailsType;
use App\Form\DevisDetailsTypeEdit;
use App\Repository\DevisDetailsRepository;
use App\Repository\DevisRepository;
use App\Services\DevisCalculator;
use App\Services\TotalDevisService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/devis/details')]
final class DevisDetailsController extends AbstractController
{
    public function __construct(
        private readonly TotalDevisService $totalDevisService,
        private readonly EntityManagerInterface $entityManager,
        private readonly DevisCalculator $devisCalculator
    ) {}

    #[Route(name: 'app_devis_details_index', methods: ['GET'])]
    public function index(DevisDetailsRepository $devisDetailsRepository): Response
    {
        return $this->render('devis_details/index.html.twig', [
            'devis_details' => $devisDetailsRepository->findAll(),
        ]);
    }

    #[Route('/{id}/new', name: 'app_devis_details_new', methods: ['GET', 'POST'])]
    public function new(Devis $devis, Request $request): Response
    {
        $devisDetail = new DevisDetails();

        $form = $this->createForm(DevisDetailsType::class, $devisDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $product = $devisDetail->getProduct();

            if (!$product) {
                $this->addFlash('error', 'Le produit associé au détail de devis est requis.');
                return $this->render('devis_details/new.html.twig', [
                    'devis' => $devis,
                    'devis_detail' => $devisDetail,
                    'form' => $form,
                ]);
            }

            $devisDetail->setLabel($product->getName());
            $devisDetail->setPrice($product->getPrice());

            // calcul total propre
            $total = $this->devisCalculator->calculLineHT($devisDetail->getPrice(),$devisDetail->getQuantity());
            $devisDetail->setTotal($total);

            $devisDetail->setDevis($devis);
            
            $totalDevis = $this->totalDevisService->calculTotalHT($devis);
            $devis->setTotal($totalDevis);

            $this->entityManager->persist($devisDetail);
            $this->entityManager->flush();

            return $this->redirectToRoute(
                'app_devis_details_edit',
                ['id' => $devisDetail->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('devis_details/new.html.twig', [
            'devis' => $devis,
            'devis_detail' => $devisDetail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_devis_details_show', methods: ['GET'])]
    public function show(DevisDetails $devisDetail): Response
    {
        return $this->render('devis_details/show.html.twig', [
            'devis_detail' => $devisDetail,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_devis_details_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DevisDetails $devisDetail): Response
    {

        $form = $this->createForm(DevisDetailsTypeEdit::class, $devisDetail);
        $form->handleRequest($request);
        $devis = $devisDetail->getDevis();
        if ($form->isSubmitted() && $form->isValid()) {

            $total = $this->devisCalculator->calculLineHT($devisDetail->getPrice(),$devisDetail->getQuantity());
            $devisDetail->setTotal($total);

            $totalDevis = $this->totalDevisService->calculTotalHT($devis);
            $devis->setTotal($totalDevis);
            
            $this->entityManager->flush();

            return $this->redirectToRoute(
                'app_devis_details_new',
                ['id' => $devis->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('devis_details/edit.html.twig', [
            'devis' => $devis,
            'devis_detail' => $devisDetail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_devis_details_delete', methods: ['POST'])]
    public function delete(Request $request, DevisDetails $devisDetail, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$devisDetail->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($devisDetail);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_devis_details_index', [], Response::HTTP_SEE_OTHER);
    }
}
