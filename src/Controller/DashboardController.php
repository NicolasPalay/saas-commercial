<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Repository\DevisDetailsRepository;
use App\Repository\DevisRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(DevisRepository $devisRepository,UserRepository $userRepository): Response
    { 
        $user = $this->getUser();
        if(!$user) {
            return $this->redirectToRoute('app_login');
        }
        $company = $user->getCompany();
        if(!$company) {
            return $this->redirectToRoute('app_login');
        }
        $employedCount = $userRepository->countByCompany($company);
        $conversations = $user->getConversations();
        $devis = $devisRepository->findBy(['company' => $company], ['id' => 'DESC'],3);
       
        return $this->render('dashboard/index.html.twig', [
            "user"=> $user,
            "users"=> $company->getUser()->toArray(),
            "conversations"=> $conversations->toArray(),
            "devis" => $devis,
            "nEmployes" => $employedCount
        ]);
    }
}
