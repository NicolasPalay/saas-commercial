<?php

namespace App\Controller;

use App\Entity\Devis;

use App\Form\DevisType;
use App\Form\DevisTypeEdit;
use App\Repository\AddressRepository;
use App\Repository\DevisRepository;
use App\Services\DevisAddress;
use App\Services\TransfertService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/devis')]
final class DevisController extends AbstractController
{
    #[Route(name: 'app_devis_index', methods: ['GET', 'POST'])]
    public function index(DevisRepository $devisRepository, Request $request, EntityManagerInterface $entityManager, AddressRepository $addressRepository, DevisAddress $devisAddress): Response
    { 
        $user = $this->getUser();
        if(!$user) return $this->redirectToRoute('app_login');

        $company = $user->getCompany();
        $devi = $devisRepository->findBy(['company' => $company]);
       
        $prefix = $company->getRefDevis();
        
        $count = $devisRepository->CountDevisByCompany($company->getId());
           
        $lastDevis = $devisRepository->findOneBy(
            ['company' => $company],
            ['id' => 'DESC']
        );

        $devis = new Devis();
        $form = $this->createForm(DevisType::class, $devis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($count >= 15) {
                $this->addFlash('info', 'Vous avez atteint la limite de '.$count.' devis pour votre entreprise. Veuillez souscrire à un abonnement pour continuer à créer des devis.');
                return $this->redirectToRoute('app_subscription_index');
            }

            $client = $devis->getClient();
            $devisAddress->setFromClient($client, $devis);


            if (!$lastDevis) {
                $number = 1;
            } else {
                $lastReference = $lastDevis->getReference();
                $number = (int) str_replace($prefix, '', $lastReference);
                $number++;
            }
            $devis->setUser($user);
            $devis->setReference($prefix . $number);
            $devis->setCompany($company);

            $entityManager->persist($devis);
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_devis_details_new',
                ['id' => $devis->getId()]
            );
        }

        return $this->render('devis/index.html.twig', [
            'devis' => $devi,
            'user' => $user,
            'form' => $form,
            'entity' => Devis::class,
            'entityText' => 'devis',
            'headers'=>["reference", "client", "total", "createdAt"],
        ]);
    }

    #[Route('/new', name: 'app_devis_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        
        $user = $this->getUser();
        if(!$user) return $this->redirectToRoute('app_login');
        $devis = new Devis();
        $form = $this->createForm(DevisType::class, $devis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $devis->setUser($user);
            $devis->setCompany($user->getCompany());
            $entityManager->persist($devis);
            $entityManager->flush();

            return $this->redirectToRoute('app_devis_details_new', ['id' => $devis->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('devis/new.html.twig', [
            'devi' => $devis,
            'form' => $form,
        ]);
    }

    #[Route('/toorder/{id}', name: 'app_toOrder', methods: ['GET'])]
    public function toOrder(DevisRepository $devisRepository, int $id, TransfertService $transfertService): Response
    {
        $user = $this->getUser();
        $devis = $devisRepository->findOneBy([
            'id' => $id,
            'company' => $user->getCompany()
        ]);
        $this->denyAccessUnlessGranted('EDIT', $devis);
        if($devis) {  
            $transfertService->devisToOrder($devis);
        }
    return $this->redirectToRoute('app_order_index');
    }

    #[Route('/toinvoice/{id}', name: 'app_devis_toInvoice', methods: ['GET'])]
    public function devisToInvoice(DevisRepository $devisRepository, int $id, TransfertService $devisVsInvoiceService): Response
    {
    $user = $this->getUser();

    $devis = $devisRepository->findOneBy([
        'id' => $id,
        'company' => $user->getCompany()
    ]);
    $this->denyAccessUnlessGranted('EDIT', $devis);
    if($devis) {
        $devisVsInvoiceService->devisToInvoice($devis);
    }
    return $this->redirectToRoute('app_invoice_index');
    }

    #[Route('/{id}/edit', name: 'app_devis_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Devis $devi, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $devi);
        $form = $this->createForm(DevisTypeEdit::class, $devi, [
            'company' => $devi->getCompany(),
            'currentClient' => $devi->getClient(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
               $address = $form->get('address')->getData();
           //dd($address->getNameStreet());
            if($address) {
                $devi->setDeliveryLabel($devi->getClient()->getRaisonSocial())
                    ->setDeliveryStreet2($address->getNameStreet2())
                    ->setDeliveryPostalCode($address->getCodePostal())
                    ->setDeliveryCity($address->getVille())
                    ->setDeliveryPhone($address->getMobilePhone())
                    ->setDeliveryStreet($address->getEmail()) ;
                    $devi->setDeliveryStreet($address->getNameStreet());
            }


            $entityManager->flush();

            return $this->redirectToRoute(
                'app_devis_details_new',
                ['id' => $devi->getId()]
            );
        
        }

        return $this->render('devis/edit.html.twig', [
            'devi' => $devi,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_devis_delete', methods: ['POST'])]
    public function delete(Request $request, Devis $devi, EntityManagerInterface $entityManager): Response
    {
            $this->denyAccessUnlessGranted('DELETE', $devi);
        if ($this->isCsrfTokenValid('delete'.$devi->getId(), $request->getPayload()->getString('_token'))) {

            $entityManager->remove($devi);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_devis_index', [], Response::HTTP_SEE_OTHER);
    }
}
