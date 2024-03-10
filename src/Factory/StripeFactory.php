<?php

namespace App\Factory;

use App\Entity\OrderItem;
use App\Entity\Payment;
use App\Event\StripeEvent;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Stripe;
use Stripe\Webhook;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Webmozart\Assert\Assert;

class StripeFactory
{
    public function __construct(
        private string $stripeSecretKey,
        private string $webhook,
        private EventDispatcherInterface $eventDispatcher
    ) {
        Stripe::setApiKey($this->stripeSecretKey);
        Stripe::setApiVersion('2020-08-27');
    }

    public function createPayment(Payment $payment, string $successUrl, string $cancelUrl): Session
    {
        $order = $payment->getOrderRef();
        Assert::notNull($order, 'Order must not be null');

        return Session::create([
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'customer_email' => $payment->getUser()->getEmail(),
            'line_items' => array_map(fn (OrderItem $order) => [
                'quantity' => $order->getQuantity(),
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $order->getQuantity() . ' x - ' . $order->getProduct()->getTitle(),
                        'description' => $order->getProduct()->getshortDescription(),
                    ],
                    'unit_amount' => bcmul($order->getProduct()->getPriceTTC(), 100),
                    'tax_behavior' => 'inclusive',
                ],
            ], $order->getItems()->toArray()),
            'metadata' => [
                'order_id' => $order->getId(),
                'payment_id' => $payment->getId(),
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'order_id' => $order->getId(),
                    'payment_id' => $payment->getId(),
                ]
            ]
        ]);
    }

    public function handle(string $signature, mixed $body): JsonResponse
    {
        if (!$body) {
            return new JsonResponse(['status' => 'error', 'message' => 'Body does not be empty'], 400);
        }

        $event = new StripeEvent($this->getEvent($body, $signature));

        if ($event instanceof JsonResponse) {
            return $event;
        }

        $this->eventDispatcher->dispatch($event, $event->getName());

        return new JsonResponse(['status' => 'success', 'event' => $event], 200);
    }

    public function getEvent(mixed $body, string $signature): Event|JsonResponse
    {
        try {
            $event = Webhook::constructEvent($body, $signature, $this->webhook);
        } catch (\UnexpectedValueException $e) {
            return new JsonResponse(['Error parsing payload: ' => $e->getMessage()], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return new JsonResponse(['Error verifying webhook signature: ' => $e->getMessage()], 400);
        }

        return $event;
    }
}
