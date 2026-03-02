<?php

namespace App\Controller;

use App\Entity\Plan;
use App\Entity\Subscription;
use App\Form\SubscriptionType;
use App\Repository\DevisRepository;
use App\Repository\PlanRepository;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/subscription')]
final class SubscriptionController extends AbstractController
{
    #[Route(name: 'app_subscription_index', methods: ['GET'])]
    public function index(DevisRepository $devisRepository, SubscriptionRepository $subscriptionRepository, PlanRepository $planRepository): Response
    {
    $companyId = $this->getUser()->getCompany()->getId();

    $count = $devisRepository->countDevisByCompany($companyId);


    $subscriptions = $subscriptionRepository->findBy(['user' => $this->getUser(),]);
    $pro = null;
    $starter = null;
    if($subscriptions){
        foreach ($subscriptions as $value) {
            if($value->getType() === 'pro') {
                $pro = $value->getId();
            } elseif($value->getType() === 'starter') {
                $starter = $value->getId();
            }
        }
    }

    return $this->render('subscription/index.html.twig', [
        'devisCount' => $count,
        'pro' => $pro,
        'starter' => $starter,
        'plan' =>$planRepository->findby(["isActive"=>true])
    ]);
}

   #[Route('/{id<\d+>}', name: 'app_subscription_new', methods: ['GET', 'POST'])]
    public function new(
        EntityManagerInterface $entityManager,
        string $id,
        PlanRepository $planRepository,
        SubscriptionRepository $subscriptionRepository
    ): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $plan = $planRepository->findOneBy(['id'=> $id]);
        
        // 1 seule requête pour récupérer starter et pro
        $subscriptions = $subscriptionRepository->findBy([
            'user' => $user,
            'plan' => [1, 2]
        ]);

        // Vérifie si le type demandé existe déjà
        foreach ($subscriptions as $sub) {
            if ($sub->getPlan() === $plan) {
                return $this->render('subscription/check.html.twig', [
                    'subscriptions' => $subscriptions,
                    'alreadySubscribed' => true,
                ]);
            }
        }

        // sinon création
        if ($subscriptions[0]->getPlan() != $plan) {
            $subscription = new Subscription();
            $subscription
                ->setUser($user)
                ->setCompany($user->getCompany())
                ->setType($plan->name)
                ->setMontant($plan->getPrice())
                ->setStripeId($plan->getStripeId());


            $entityManager->persist($subscription);
            $entityManager->flush();

            // ajoute le nouveau à la liste existante
            $subscriptions[] = $subscription;
        }

        return $this->render('subscription/check.html.twig', [
            'subscriptions' => $subscriptions,
            'alreadySubscribed' => false,
        ]);
    }
 
    #[Route('/create-session-stripe', name: 'app_payment_stripe', methods: ['POST'])]
public function checkout(Request $request): RedirectResponse
{
    Stripe::setApiKey($this->getParameter('app.stripe'));

    $lookupKey = $request->request->get('lookup_key');

    if (!$lookupKey) {
        throw $this->createNotFoundException('lookup_key manquant');
    }

    $YOUR_DOMAIN = 'https://127.0.0.1:8000';

    // CRÉER la session Stripe (et non retrieve)
    $checkout_session = Session::create([
        'mode' => 'subscription',

        'line_items' => [[
            'price' => $lookupKey, // price_xxx
            'quantity' => 1,
        ]],

        'success_url' => $YOUR_DOMAIN . '/subscription/success?session_id={CHECKOUT_SESSION_ID}',

        'cancel_url' => $YOUR_DOMAIN . '/subscription/cancel',
    ]);


    return new RedirectResponse($checkout_session->url);
}

    #[Route('/{id<\d+>}', name: 'app_subscription_show', methods: ['GET'])]
    public function show(Subscription $subscription): Response
    {
        return $this->render('subscription/show.html.twig', [
            'subscription' => $subscription,
        ]);
    }

     #[Route('/success', name: 'subscription_success')]
public function success(Request $request)
{
    Stripe::setApiKey($this->getParameter('app.stripe'));

    $sessionId = $request->query->get('session_id');

    if (!$sessionId) {
        throw $this->createNotFoundException();
    }

    $session = Session::retrieve($sessionId);

    $subscriptionId = $session->subscription;

    dd($subscriptionId);
}
 #[Route('/cancel', name: 'subscription_cancel')]
public function cancel(Request $request)
{
    Stripe::setApiKey($this->getParameter('app.stripe'));

    $sessionId = $request->query->get('session_id');

    if (!$sessionId) {
        throw $this->createNotFoundException();
    }

    $session = Session::retrieve($sessionId);

    $subscriptionId = $session->subscription;

    dd($subscriptionId);
}

    #[Route('/{id<\d+>}', name: 'app_subscription_delete', methods: ['POST'])]
    public function delete(Request $request, Subscription $subscription, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$subscription->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($subscription);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_subscription_index', [], Response::HTTP_SEE_OTHER);
    }
}
