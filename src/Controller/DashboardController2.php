<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Repository\DevisDetailsRepository;
use App\Repository\DevisRepository;
use App\Repository\InvoiceRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController2 extends AbstractController
{
    #[Route('/dashboard2', name: 'app_dashboard2')]
    public function index(DevisRepository $devisRepository, UserRepository $userRepository, InvoiceRepository $invoiceRepository): Response
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
        $invoices = $invoiceRepository->findBy(['company' => $company], ['id' => 'DESC'],3);
        $countInvoices = $invoiceRepository->countInvoicesByCompanyAnnual($company);
        return $this->render('dashboard/index.html.twig', [
            "user"=> $user,
            "users"=> $company->getUser()->toArray(),
            "conversations"=> $conversations->toArray(),
            "devis" => $devis,
            "nEmployes" => $employedCount,
            "invoices" => $invoices,
            "countInvoices" => $countInvoices
        ]);
    }
}
