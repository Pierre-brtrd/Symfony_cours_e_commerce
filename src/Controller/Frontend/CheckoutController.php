<?php

namespace App\Controller\Frontend;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\User;
use App\Factory\StripeFactory;
use App\Form\AddressType;
use App\Form\PaymentType;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/checkout', name: 'app.checkout')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private OrderRepository $orderRepository
    ) {
    }

    #[Route('/address', '.address', methods: ['GET', 'POST'])]
    public function address(Request $request): Response|RedirectResponse
    {
        $user = $this->getUser();
        $order = $this->orderRepository->findLastCartUser($user);

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour passer commande');

            return $this->redirectToRoute('app.login');
        }

        if (!$order) {
            $this->addFlash('error', 'Votre panier est vide');

            return $this->redirectToRoute('app.cart');
        }

        /** @var User $user */
        if ($user->getDefaultAddress()) {
            $address = $user->getDefaultAddress();
        } else if ($user->getAddresses()->count() > 0) {
            $address = $user->getAddresses()->first();
        } else {
            $address = new Address();
            $address->setUser($user);
        }

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$user->hasAddress($address)) {
                $address->setUser($user);
            }

            $this->em->persist($address);
            $this->em->flush();

            return $this->redirectToRoute('app.checkout.payment');
        }

        return $this->render('Frontend/Checkout/address.html.twig', [
            'form' => $form,
            'cart' => $order,
        ]);
    }

    #[Route('/payment', '.payment', methods: ['GET', 'POST'])]
    public function recap(Request $request, StripeFactory $stripeFactory): Response|RedirectResponse
    {
        $user = $this->getUser();
        $order = $this->orderRepository->findLastCartUser($user);

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour passer commande');

            return $this->redirectToRoute('app.login');
        }

        if (!$order) {
            $this->addFlash('error', 'Votre panier est vide');

            return $this->redirectToRoute('app.cart');
        }

        $payment = (new Payment)
            ->setUser($user)
            ->setOrderRef($order)
            ->setStatus(Payment::STATUS_NEW);

        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($payment);
            $this->em->flush();

            $session = $stripeFactory->createPayment(
                $payment,
                $this->generateUrl('app.checkout.success', [
                    'id' => $payment->getId(),
                ], UrlGeneratorInterface::ABSOLUTE_URL),
                $this->generateUrl('app.checkout.cancel', [
                    'id' => $payment->getId(),
                ], UrlGeneratorInterface::ABSOLUTE_URL)
            );

            return $this->redirect($session->url);
        }

        return $this->render('Frontend/Checkout/recap.html.twig', [
            'cart' => $order,
            'formStripe' => $form,
        ]);
    }

    #[Route('/success/{id}', '.success', methods: ['GET'])]
    public function success(Payment $payment): Response
    {
        if (!$payment) {
            $this->addFlash('error', 'Une erreur sur le paiement est survenue');

            return $this->redirectToRoute('app.home');
        }

        return $this->render('Frontend/Checkout/success.html.twig', [
            'payment' => $payment,
        ]);
    }

    #[Route('/cancel/{id}', '.cancel', methods: ['GET'])]
    public function cancel(?Payment $payment): Response
    {
        if (!$payment) {
            $this->addFlash('error', 'Une erreur au moment du paiement est survenue');

            return $this->redirectToRoute('app.home');
        }

        $payment->setStatus(Payment::STATUS_REFUSED);

        $order = $payment->getOrderRef();
        $order->setStatus(Order::STATUS_PAYMENT_FAILED);

        $this->em->persist($payment);
        $this->em->persist($order);
        $this->em->flush();

        return $this->render('Frontend/Checkout/cancel.html.twig', [
            'payment' => $payment,
        ]);
    }
}
