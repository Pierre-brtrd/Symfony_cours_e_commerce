<?php

namespace App\EventListener;

use App\Entity\Order;
use App\Entity\Payment;
use App\Event\StripeEvent;
use App\Repository\OrderRepository;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'payment_intent.created', method: 'onPaymentIntentCreated')]
#[AsEventListener(event: 'payment_intent.succeeded', method: 'onPaymentIntentSucceeded')]
#[AsEventListener(event: 'payment_intent.failed', method: 'onPaymentIntentFailed')]
#[AsEventListener(event: 'checkout.session.completed', method: 'onStripeCheckoutComplete')]
class StripeEventListener
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PaymentRepository $paymentRepository,
        private readonly OrderRepository $orderRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function onPaymentIntentCreated(StripeEvent $event): void
    {
        $resource = $event->getResource();

        if (!$resource) {
            throw new \InvalidArgumentException('Resource not found');
        }

        $payment = $this->paymentRepository->find($resource->metadata->payment_id);
        $order = $this->orderRepository->find($resource->metadata->order_id);

        if (!$payment || !$order) {
            throw new \InvalidArgumentException('Payment not found');
        }

        $payment->setStatus(Payment::STATUS_AWAITING_PAYMENT);
        $order->setStatus(Order::STATUS_NEW);

        $this->em->flush();
    }

    public function onPaymentIntentSucceeded(StripeEvent $event): void
    {
        $resource = $event->getResource();

        if (!$resource) {
            throw new \InvalidArgumentException('Resource not found');
        }

        $payment = $this->paymentRepository->find($resource->metadata->payment_id);
        $order = $this->orderRepository->find($resource->metadata->order_id);

        if (!$payment || !$order) {
            throw new \InvalidArgumentException('Payment not found');
        }

        $payment->setStatus(Payment::STATUS_PAID);
        $order->setStatus(Order::STATUS_PAID);

        $this->em->flush();
    }

    public function onPaymentIntentFailed(StripeEvent $event): void
    {
        $resource = $event->getResource();

        if (!$resource) {
            throw new \InvalidArgumentException('Resource not found');
        }

        $payment = $this->paymentRepository->find($resource->metadata->payment_id);
        $order = $this->orderRepository->find($resource->metadata->order_id);

        if (!$payment || !$order) {
            throw new \InvalidArgumentException('Payment not found');
        }

        $payment->setStatus(Payment::STATUS_REFUSED);
        $order->setStatus(Order::STATUS_PAYMENT_FAILED);

        $this->em->flush();
    }

    public function onStripeCheckoutComplete(StripeEvent $event): void
    {
        $resource = $event->getResource();

        if (!$resource) {
            throw new \InvalidArgumentException('Resource not found');
        }

        $order = $this->orderRepository->find($resource->metadata->order_id);
        $payment = $this->paymentRepository->find($resource->metadata->payment_id);

        if (!$order || !$payment) {
            throw new \InvalidArgumentException('Payment or Order not found');
        }

        if ($payment->getStatus() !== Payment::STATUS_PAID) {
            $order->setStatus(Order::STATUS_PAYMENT_FAILED);
            $this->em->flush();
        } elseif ($order->getStatus() !== Order::STATUS_PAID) {
            $order->setStatus(Order::STATUS_PAID);
            $this->em->flush();
        }
    }
}
