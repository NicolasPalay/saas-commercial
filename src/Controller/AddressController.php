<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Client;
use App\Form\AddressType;
use App\Repository\AddressRepository;
use App\Services\AddresssDefault;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/address')]
final class AddressController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,)
    {
    }

    #[Route('/{id}', name: 'app_address_index', methods: ['GET'])]
    public function index(AddressRepository $addressRepository, int $id): Response
    {
       $client = $this->entityManager->getRepository(Client::class)->find($id);

        if (!$client) {
            throw $this->createNotFoundException('Client not found');
        }
        return $this->render('address/index.html.twig', [
            'addresses' => $addressRepository->findBy(['client' => $id]),
            'entity' => Address::class,
            'client' => $client,
            'headers'=>["nameStreet", "codePostal", "ville", "isDefault", "isDelivery", "businessPhone", "mobilePhone"],
        ]);
    }

    #[Route('/new/{id}', name: 'app_address_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,int $id, AddresssDefault $addresssDefault): Response
    {
        $address = new Address();
        $client = $entityManager->getReference('App\Entity\Client', $id);
         if (!$client) {
            throw $this->createNotFoundException('Client not found');
        }
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isDefault = $form->get('isDefault')->getData();
            if ($isDefault) {           
                $addresssDefault->setDefaultAddress($address);
                $address->setIsDefault($isDefault);
            }
            $address->setClient($client);
            $address->setCompany($client->getCompany());
            

            $entityManager->persist($address);
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_client_edit',
                ['id' => $client->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('address/new.html.twig', [
            'address' => $address,
            'form' => $form,
            'client' => $client
        ]);
    }

    #[Route('/{id}/edit', name: 'app_address_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Address $address, 
        EntityManagerInterface $entityManager, 
        AddresssDefault $addresssDefault
        ): Response
    {
        $user = $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');
        $this->denyAccessUnlessGranted('EDIT', $address);
        $form = $this->createForm(AddressType::class, $address);
        $client = $address->getClient();
         if (!$client) {
            throw $this->createNotFoundException('Client not found');
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
             $isDefault = $form->get('isDefault')->getData();
            if ($isDefault) {           
                $addresssDefault->setDefaultAddress($address);
                $address->setIsDefault($isDefault);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_address_index',  ['id' => $client->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('address/edit.html.twig', [
            'address' => $address,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_address_delete', methods: ['POST'])]
    public function delete(Request $request, Address $address, EntityManagerInterface $entityManager): Response
    {
        $client = $address->getClient();
         if (!$client) {
            throw $this->createNotFoundException('Client not found');
        }
        $this->denyAccessUnlessGranted('DELETE', $address);
        if ($this->isCsrfTokenValid('delete'.$address->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($address);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_address_index', [], Response::HTTP_SEE_OTHER);
    }
}
