<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use App\Repository\ConversationRepository;
use App\Repository\DevisRepository;
use App\Repository\InvoiceRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard')]
final class DashboardController extends AbstractController
{
    #[Route('', name: 'app_dashboard', methods: ['GET'])]
    public function index(
        DevisRepository $devisRepository,
        UserRepository $userRepository,
        InvoiceRepository $invoiceRepository,
        OrderRepository $orderRepository,
        ClientRepository $clientRepository,
        ConversationRepository $conversationRepository
    ): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $company = $user->getCompany();
        if (!$company) {
            return $this->redirectToRoute('app_login');
        }

        // ===== KPIs PRINCIPAUX =====
        
        // CA Mensuel
        $monthlyRevenue = $invoiceRepository->findMonthlyRevenue($company);

        // CA Annuel
        $annualRevenue = $invoiceRepository->findAnnualRevenue($company);

        // Nombre de clients
        $totalClients = $clientRepository->countByCompany($company);

        // Nombre de devis ce mois
        $monthlyQuotesCount = $devisRepository->findCountThisMonth($company);

        // Nombre de commandes ce mois
        $monthlyOrdersCount = $orderRepository->findCountThisMonth($company);

        // Factures impayées (nombre et montant)
        $unpaidInvoicesCount = $invoiceRepository->findUnpaidCount($company);
        $unpaidInvoicesAmount = $invoiceRepository->findUnpaidAmount($company);
        $countInvoices = $invoiceRepository->findCountByCompany($company);

        // ===== STATISTIQUES ADDITIONNELLES =====
        
        // Nombre d'employés
        $employedCount = $userRepository->countByCompany($company);

        // Derniers devis (3)
        $recentDevis = $devisRepository->findRecentByCompany($company, 3);

        // Dernières factures (3)
        $recentInvoices = $invoiceRepository->findRecentByCompany($company, 3);

        // Dernières commandes (3)
        $recentOrders = $orderRepository->findRecentByCompany($company, 3);

        // Factures payées cette année
        $paidInvoicesThisYear = $invoiceRepository->findCountPaidThisYear($company);

        // ===== MESSAGERIE =====
        
        // Conversations récentes
        $recentConversations = $conversationRepository->findRecentByUser($user, 5);

        // Stats messagerie
        $totalConversations = $conversationRepository->countByCompany($company);
        $todayMessages = 0; // À calculer si Message entity existe
        $activeConversations = $conversationRepository->findCountActiveLastWeek($company);

        // ===== RENDRE LE TEMPLATE =====
        
        return $this->render('dashboard/index.html.twig', [
            // Utilisateur et entreprise
            'user' => $user,
            'company' => $company,
            'users' => $company->getUser()->toArray(),
            'nEmployes' => $employedCount,
            
            // KPIs principaux
            'monthlyRevenue' => $monthlyRevenue,
            'annualRevenue' => $annualRevenue,
            'totalClients' => $totalClients,
            'monthlyQuotesCount' => $monthlyQuotesCount,
            'monthlyOrdersCount' => $monthlyOrdersCount,
            'unpaidInvoicesCount' => $unpaidInvoicesCount,
            'unpaidInvoicesAmount' => $unpaidInvoicesAmount,
            'paidInvoicesThisYear' => $paidInvoicesThisYear,
            'countInvoices' => $countInvoices,
            
            // Statistiques additionnelles
            'employedCount' => $employedCount,
            'devis' => $recentDevis,
            'invoices' => $recentInvoices,
            'recentOrders' => $recentOrders,
            
            // Messagerie
            'recentConversations' => $recentConversations,
            'totalConversations' => $totalConversations,
            'todayMessages' => $todayMessages,
            'activeConversations' => $activeConversations,
        ]);
    }
}
