<?php

namespace App\Services;

use App\Entity\Company;
use App\Entity\Plan;
use App\Entity\Subscription;
use App\Entity\User;
use App\Repository\PlanRepository;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\StripeClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService
{
    private StripeClient $stripe;

    public function __construct(
        #[Autowire('%env(STRIPE_SECRET_KEY)%')]
        private string $stripeSecretKey,
        private EntityManagerInterface $em,
        private PlanRepository $planRepository,
        private SubscriptionRepository $subscriptionRepository,
        private UrlGeneratorInterface $urlGenerator,
    ) {
        Stripe::setApiKey($this->stripeSecretKey);
        $this->stripe = new StripeClient($this->stripeSecretKey);
    }

    /**
     * Récupère ou crée un Customer Stripe pour une Company.
     */
    public function getOrCreateCustomer(Company $company, User $user): Customer
    {
        if ($company->getStripeCustomerId()) {
            return $this->stripe->customers->retrieve($company->getStripeCustomerId());
        }

        $customer = $this->stripe->customers->create([
            'email' => $user->getEmail(),
            'name'  => $company->getName(),
            'metadata' => [
                'company_id' => $company->getId(),
                'user_id'    => $user->getId(),
            ],
        ]);

        $company->setStripeCustomerId($customer->id);
        $this->em->flush();

        return $customer;
    }

    /**
     * Crée une Checkout Session Stripe pour un abonnement.
     */
    public function createCheckoutSession(Plan $plan, Company $company, User $user): \Stripe\Checkout\Session
    {
        $customer = $this->getOrCreateCustomer($company, $user);

        $successUrl = $this->urlGenerator->generate(
            'subscription_success',
            ['planId' => $plan->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $cancelUrl = $this->urlGenerator->generate(
            'subscription_plans',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->stripe->checkout->sessions->create([
            'customer'            => $customer->id,
            'payment_method_types' => ['card'],
            'mode'                => 'subscription',
            'line_items'          => [[
                'price'    => $plan->getStripeId(),
                'quantity' => 1,
            ]],
            'success_url' => $successUrl . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => $cancelUrl,
            'metadata'    => [
                'plan_id'    => $plan->getId(),
                'company_id' => $company->getId(),
                'user_id'    => $user->getId(),
            ],
        ]);
    }

    /**
     * Traite la session Checkout après paiement réussi.
     */
    public function handleCheckoutSuccess(string $sessionId): Subscription
    {
        $session = $this->stripe->checkout->sessions->retrieve($sessionId, [
            'expand' => ['subscription'],
        ]);

        $planId    = $session->metadata->plan_id;
        $companyId = $session->metadata->company_id;
        $userId    = $session->metadata->user_id;

        $plan    = $this->planRepository->find($planId);
        $company = $this->em->getRepository(Company::class)->find($companyId);
        $user    = $this->em->getRepository(User::class)->find($userId);

        // Vérifie si un abonnement actif existe déjà
        $existing = $this->subscriptionRepository->findOneBy([
            'company' => $company,
            'isPay'   => true,
        ]);

        if ($existing) {
            return $existing;
        }

        $subscription = new Subscription();
        $subscription->setUser($user);
        $subscription->setCompany($company);
        $subscription->setPlan($plan);
        $subscription->setMontant($plan->getPrice());
        $subscription->setType('stripe');
        $subscription->setIsPay(true);
        $subscription->setStripeSubscriptionId($session->subscription->id);
        $subscription->setStripeCustomerId($session->customer);

        $this->em->persist($subscription);
        $this->em->flush();

        return $subscription;
    }

    /**
     * Crée un portail de facturation Stripe pour gérer l'abonnement.
     */
    public function createBillingPortalSession(Company $company): \Stripe\BillingPortal\Session
    {
        $returnUrl = $this->urlGenerator->generate(
            'subscription_manage',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->stripe->billingPortal->sessions->create([
            'customer'   => $company->getStripeCustomerId(),
            'return_url' => $returnUrl,
        ]);
    }

    /**
     * Annule un abonnement Stripe.
     */
    public function cancelSubscription(Subscription $subscription): void
    {
        if ($subscription->getStripeSubscriptionId()) {
            $this->stripe->subscriptions->cancel($subscription->getStripeSubscriptionId());
        }

        $subscription->setIsPay(false);
        $this->em->flush();
    }

    /**
     * Traite les événements webhook Stripe.
     */
    public function handleWebhook(string $payload, string $sigHeader, string $webhookSecret): void
    {
        $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);

        switch ($event->type) {
            case 'invoice.payment_succeeded':
                $this->onInvoicePaymentSucceeded($event->data->object);
                break;

            case 'invoice.payment_failed':
                $this->onInvoicePaymentFailed($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->onSubscriptionDeleted($event->data->object);
                break;
        }
    }

    private function onInvoicePaymentSucceeded(\Stripe\Invoice $invoice): void
    {
        $sub = $this->subscriptionRepository->findOneBy([
            'stripeSubscriptionId' => $invoice->subscription,
        ]);

        if ($sub) {
            $sub->setIsPay(true);
            $this->em->flush();
        }
    }

    private function onInvoicePaymentFailed(\Stripe\Invoice $invoice): void
    {
        $sub = $this->subscriptionRepository->findOneBy([
            'stripeSubscriptionId' => $invoice->subscription,
        ]);

        if ($sub) {
            $sub->setIsPay(false);
            $this->em->flush();
        }
    }

    private function onSubscriptionDeleted(\Stripe\Subscription $stripeSubscription): void
    {
        $sub = $this->subscriptionRepository->findOneBy([
            'stripeSubscriptionId' => $stripeSubscription->id,
        ]);

        if ($sub) {
            $sub->setIsPay(false);
            $this->em->flush();
        }
    }

    /**
     * Retourne l'abonnement actif d'une company, ou null.
     */
    public function getActiveSubscription(Company $company): ?Subscription
    {
        return $this->subscriptionRepository->findOneBy([
            'company' => $company,
            'isPay'   => true,
        ]);
    }
}
