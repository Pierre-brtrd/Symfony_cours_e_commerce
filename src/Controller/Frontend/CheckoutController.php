<?php

namespace App\Controller\Frontend;

use App\Entity\Address;
use App\Entity\User;
use App\Form\AddressType;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

            return $this->redirectToRoute('app.cart.index');
        }

        /** @var User $user */
        $address = $user->getAddresses()->first();

        if (!$address) {
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

    #[Route('/payment', '.payment', methods: ['GET'])]
    public function recap(): Response|RedirectResponse
    {
        $user = $this->getUser();
        $order = $this->orderRepository->findLastCartUser($user);

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour passer commande');

            return $this->redirectToRoute('app.login');
        }

        if (!$order) {
            $this->addFlash('error', 'Votre panier est vide');

            return $this->redirectToRoute('app.cart.index');
        }

        return $this->render('Frontend/Checkout/recap.html.twig', [
            'cart' => $order,
        ]);
    }
}
