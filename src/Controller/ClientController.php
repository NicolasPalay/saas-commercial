<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\AddressRepository;
use App\Repository\ClientRepository;
use App\Services\ClientAddressService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/client')]
final class ClientController extends AbstractController
{
    public function __construct(private ClientAddressService $clientAddressService)
    {
      $this->clientAddressService = $clientAddressService;
    }

    #[Route(name: 'app_client_index', methods: ['GET'])]
    public function index(ClientRepository $clientRepository): Response
    {
        $user= $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');

        // dd($clientRepository->getAllClientsByCompany($user->getCompany()));
        return $this->render('client/index.html.twig', [
            'clients' => $clientRepository->getAllClientsByCompany($user->getCompany()),
            'entity' => Client::class,
            'headers'=>["raisonSocial", "codePostal", "ville", "mobilePhone",  "createdAt"],
        ]);
    }

    #[Route('/new', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user= $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $user= $this->getUser();
        $campony = $user->getCompany();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client->setCompany($campony);
            $client->setRaisonSocial($form->get('raisonSocial')->getData());
            $address = $this->clientAddressService->setAddress($form);
           
            $client->addAddress($address);
            $entityManager->persist($address);


            $entityManager->persist($client);
            $entityManager->flush();

            return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('client/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_show', methods: ['GET'])]
    public function show(Client $client): Response
    {
        return $this->render('client/show.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        $user= $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');
        $this->denyAccessUnlessGranted('EDIT', $client);

            // Récupérer la première adresse (ou celle que tu veux)
    $address = $client->getAddress()->first();
    $form = $this->createForm(ClientType::class, $client);

    if ($address) {
        $form->get('nameStreet')->setData($address->getNameStreet());
        $form->get('nameStreet2')->setData($address->getNameStreet2());
        $form->get('codePostal')->setData($address->getCodePostal());
        $form->get('ville')->setData($address->getVille());
        $form->get('businessPhone')->setData($address->getBusinessPhone());
        $form->get('mobilePhone')->setData($address->getMobilePhone());
        $form->get('email')->setData($address->getEmail());
    }
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

             $address = $this->clientAddressService->setAddress($form, $address);

        if (!$client->getAddress()->contains($address)) {
            $client->addAddress($address);
            $entityManager->persist($address);
        }

        $entityManager->flush();

            return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('client/edit.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_delete', methods: ['POST'])]
    public function delete(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('DELETE', $client);
        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($client);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
    }
}
