<?php

namespace App\Controller;

use App\Repository\PlanRepository;
use App\Services\SendMailService;
use App\Services\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/subscription')]
#[IsGranted('ROLE_USER')]
class SubscriptionController extends AbstractController
{
    public function __construct(
        private StripeService $stripeService,
        private PlanRepository $planRepository,
        private SendMailService $sendMailService,
    ) {}

    /**
     * Page listant tous les plans disponibles.
     */
    #[Route('/plans', name: 'subscription_plans')]
    public function plans(): Response
    {
        $plans = $this->planRepository->findBy(['isActive' => true]);

        $user    = $this->getUser();
        $company = $user->getCompany();

        $activeSub = $this->stripeService->getActiveSubscription($company);

        return $this->render('subscription/plans.html.twig', [
            'plans'     => $plans,
            'activeSub' => $activeSub,
        ]);
    }

    /**
     * Lance le checkout Stripe pour un plan donné.
     */
    #[Route('/checkout/{id}', name: 'subscription_checkout')]
    public function checkout(int $id): Response
    {
        $plan = $this->planRepository->find($id);

        if (!$plan || !$plan->isActive()) {
            $this->addFlash('danger', 'Ce plan n\'est pas disponible.');
            return $this->redirectToRoute('subscription_plans');
        }

        $user    = $this->getUser();
        $company = $user->getCompany();

        // Empêche de souscrire si déjà abonné
        $existing = $this->stripeService->getActiveSubscription($company);
        if ($existing) {
            $this->addFlash('warning', 'Vous avez déjà un abonnement actif.');
            return $this->redirectToRoute('subscription_manage');
        }

        $session = $this->stripeService->createCheckoutSession($plan, $company, $user);

        return $this->redirect($session->url, 303);
    }

    /**
     * Page de succès après paiement Stripe.
     */
    #[Route('/success', name: 'subscription_success')]
    public function success(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            return $this->redirectToRoute('subscription_plans');
        }

        try {
            $subscription = $this->stripeService->handleCheckoutSuccess($sessionId);

            // Envoi d'un email de confirmation
            $user = $this->getUser();
            $this->sendMailService->send(
                'noreply@' . $_SERVER['HTTP_HOST'],
                $user->getEmail(),
                'Confirmation de votre abonnement',
                'subscription_confirmation',
                [
                    'user'         => $user,
                    'subscription' => $subscription,
                    'plan'         => $subscription->getPlan(),
                ]
            );

            $this->addFlash('success', 'Votre abonnement est maintenant actif !');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Une erreur est survenue lors de la validation.');
        }

        return $this->render('subscription/success.html.twig');
    }

    /**
     * Page de gestion de l'abonnement (portail Stripe ou détails).
     */
    #[Route('/manage', name: 'subscription_manage')]
    public function manage(): Response
    {
        $user    = $this->getUser();
        $company = $user->getCompany();

        $activeSub = $this->stripeService->getActiveSubscription($company);

        return $this->render('subscription/manage.html.twig', [
            'subscription' => $activeSub,
            'company'      => $company,
        ]);
    }

    /**
     * Redirige vers le portail de facturation Stripe.
     */
    #[Route('/billing-portal', name: 'subscription_billing_portal')]
    public function billingPortal(): Response
    {
        $user    = $this->getUser();
        $company = $user->getCompany();

        if (!$company->getStripeCustomerId()) {
            $this->addFlash('warning', 'Aucun abonnement trouvé.');
            return $this->redirectToRoute('subscription_plans');
        }

        $portalSession = $this->stripeService->createBillingPortalSession($company);

        return $this->redirect($portalSession->url, 303);
    }

    /**
     * Annulation de l'abonnement (côté app, sans passer par le portail).
     */
    #[Route('/cancel', name: 'subscription_cancel', methods: ['POST'])]
    public function cancel(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('cancel_subscription', $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('subscription_manage');
        }

        $user    = $this->getUser();
        $company = $user->getCompany();

        $activeSub = $this->stripeService->getActiveSubscription($company);

        if (!$activeSub) {
            $this->addFlash('warning', 'Aucun abonnement actif à annuler.');
            return $this->redirectToRoute('subscription_manage');
        }

        $this->stripeService->cancelSubscription($activeSub);

        $this->addFlash('success', 'Votre abonnement a été annulé.');

        return $this->redirectToRoute('subscription_plans');
    }
}
