<?php

namespace App\Controller;

use App\Services\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StripeWebhookController extends AbstractController
{
    #[Route('/stripe/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function webhook(
        Request $request,
        StripeService $stripeService,
        #[Autowire('%env(STRIPE_WEBHOOK_SECRET)%')]
        string $webhookSecret,
    ): Response {
        $payload   = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature');

        if (!$sigHeader) {
            return new Response('Missing Stripe-Signature header', Response::HTTP_BAD_REQUEST);
        }

        try {
            $stripeService->handleWebhook($payload, $sigHeader, $webhookSecret);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return new Response('Invalid signature', Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new Response('Webhook error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response('OK', Response::HTTP_OK);
    }
}
