<?php

namespace App\Controller;

use App\Entity\Company;
use App\Repository\PlanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout', methods: ['POST'])]
    public function checkout(Request $request, EntityManagerInterface $em, PlanRepository $planRepository): RedirectResponse
    {
        Stripe::setApiKey($this->getParameter('app.stripe'));

        $user = $this->getUser();
        $company = $user->getCompany();

        $priceId = $request->request->get('price_id');

        // 🔥 créer customer si inexistant
        if (!$company->getStripeCustomerId()) {
            $customer = Customer::create([
                'email' => $user->getEmail(),
                'name' => $company->getName(),
            ]);

            $company->setStripeCustomerId($customer->id);
            $em->flush();
        }

            $plan = $planRepository->find($request->request->get('plan_id'));

        $session = Session::create([
            'mode' => 'subscription',

            'customer' => $company->getStripeCustomerId(),

            'line_items' => [[
                'price' => $plan->getStripePriceId(), // 🔥 clé ici
                'quantity' => 1,
            ]],

            'metadata' => [
                'company_id' => $company->getId(),
                'plan_id' => $plan->getId(),
            ],

            'success_url' => 'https://127.0.0.1:8000/subscription/success',
            'cancel_url' => 'https://127.0.0.1:8000/subscription/cancel',
        ]);

        return new RedirectResponse($session->url);
    }
}