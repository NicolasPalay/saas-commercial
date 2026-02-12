<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Repository\DevisDetailsRepository;
use App\Repository\DevisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(DevisRepository $devisRepository): Response
    { 
        $user = $this->getUser();
        if(!$user) {
            return $this->redirectToRoute('app_login');
        }
        $company = $user->getCompany();
        if(!$company) {
            return $this->redirectToRoute('app_login');
        }
        $conversations = $user->getConversations();
        $devis = $devisRepository->findBy(['company' => $company]);
       
        return $this->render('dashboard/index.html.twig', [
            "user"=> $user,
            "users"=> $company->getUser()->toArray(),
            "conversations"=> $conversations->toArray(),
            "devis" => $devis
        ]);
    }
}
