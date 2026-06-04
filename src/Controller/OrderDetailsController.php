<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Form\OrderDetailType;
use App\Form\OrderDetailTypeEdit;
use App\Repository\OrderDetailRepository;
use App\Repository\ProductRepository;
use App\Services\DocumentCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/order/details')]
final class OrderDetailsController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly DocumentCalculator $calculator
    ) {}

    #[Route('/{uuid}/{reference}/show', name: 'app_order_details_new', methods: ['GET', 'POST'])]
    public function new(Request $request,
                        string $uuid,
                        string $reference,
                        OrderRepository $orderRepository
                        ): Response
    {
        $order = $orderRepository->findOneByReferenceAndCompanyUuid($reference, $uuid);
        if (!$order) {
            throw $this->createNotFoundException('Commande introuvable');
        }
        $user = $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');

        $products = $this->productRepository->findBy(['company' => $order->getCompany()]);
        if (!$products) return $this->redirectToRoute('app_product_new');

        $orderDetail = new OrderDetail();

        $form = $this->createForm(OrderDetailType::class, $orderDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $product = $orderDetail->getProduct();

            if (!$product) {
                $this->addFlash('error', 'Produit requis.');
                return $this->render('order_detail/new.html.twig', [
                    'order' => $order,
                    'order_detail' => $orderDetail,
                    'form' => $form,
                ]);
            }

            // Hydratation
            $orderDetail->setCompany($user->getCompany());
            $orderDetail->setLabel($product->getName());
            $orderDetail->setPrice($product->getPrice());
            $orderDetail->setTaxe($product->getTaxe());
            $orderDetail->setCommande($order);

            // Calcul ligne
            $total = $this->calculator->calculLineHT(
                (string) $orderDetail->getPrice(),
                (string) $orderDetail->getQuantity(),
                (string) $orderDetail->getReduce()
            );

            $orderDetail->setTotal($total);

            // Persist
            $this->entityManager->persist($orderDetail);
            $this->entityManager->flush();

            // Recalcul global
            $this->calculator->recalculate($order, 'getOrderDetails');
            $this->entityManager->flush();

            return $this->redirectToRoute(
                'app_order_details_new',
                [
                    'uuid' => $order->getCompany()->getUuid(),
                    'reference' => $order->getReference()
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('order_detail/new.html.twig', [
            'order' => $order,
            'order_detail' => $orderDetail,
            'form' => $form,
        ]);
    }

    #[Route('/{uuid}/{reference}/line/{id}/edit', name: 'app_order_details_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request,
                         string $uuid,
                         string $reference,
                         OrderDetail $orderDetail): Response
    {
        $order = $orderDetail->getCommande();
        if ($uuid !== (string) $order->getCompany()->getUuid() || $reference !== $order->getReference()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(OrderDetailTypeEdit::class, $orderDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Recalcul ligne
            $total = $this->calculator->calculLineHT(
                (string) $orderDetail->getPrice(),
                (string) $orderDetail->getQuantity(),
                (string) $orderDetail->getReduce()
            );

            $orderDetail->setTotal($total);

            // Recalcul global
            $this->calculator->recalculate($order, 'getOrderDetails');

            $this->entityManager->flush();

            return $this->redirectToRoute(
                'app_order_details_new',
                [
                    'uuid' => $order->getCompany()->getUuid(),
                    'reference' => $order->getReference()
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('order_detail/edit.html.twig', [
            'order' => $order,
            'orderDetail' => $orderDetail,
            'form' => $form,
        ]);
    }

    #[Route('/{uuid}/{reference}/line/{id}/delete', name: 'app_order_detail_delete', methods: ['POST'])]
    public function delete(Request $request,
                           string $uuid,
                           string $reference,
                           OrderDetail $orderDetail): Response
    {
        $order = $orderDetail->getCommande();
        if ($uuid !== (string) $order->getCompany()->getUuid() || $reference !== $order->getReference()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $orderDetail->getId(), $request->getPayload()->getString('_token'))) {
            $this->entityManager->remove($orderDetail);
            $this->entityManager->flush();

            // Recalcul global après suppression
            $this->calculator->recalculate($order, 'getOrderDetails');
            $this->entityManager->flush();
        }

        return $this->redirectToRoute(
            'app_order_details_new',
            [
                'uuid' => $order->getCompany()->getUuid(),
                'reference' => $order->getReference()
            ],
            Response::HTTP_SEE_OTHER
        );
    }
}