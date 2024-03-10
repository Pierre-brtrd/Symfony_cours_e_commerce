<?php

namespace App\Controller\Api;

use App\Factory\StripeFactory;
use App\Repository\OrderRepository;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/payment/stripe', 'api.payment.stripe')]
class StripeApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private PaymentRepository $paymentRepository,
        private OrderRepository $orderRepository,
        private EventDispatcherInterface $dispatcher
    ) {
    }

    #[Route('/notify', '.notify', methods: ['POST'])]
    public function notify(Request $request, StripeFactory $stripeFactory): JsonResponse
    {
        $signature = $request->headers->get('stripe-signature');

        if (!$signature) {
            throw new BadRequestHttpException('Missing header stripe-signature');
        }

        $response = $stripeFactory->handle($signature, $request->getContent());

        return $response;
    }
}
