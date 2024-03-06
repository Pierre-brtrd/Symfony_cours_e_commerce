<?php

namespace App\Manager;

use App\Entity\Order;
use App\Factory\OrderFactory;
use App\Repository\OrderRepository;
use App\Storage\CartSessionStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Manager for the cart
 * 
 * @package App\Manager
 */
class CartManager
{
    public function __construct(
        private CartSessionStorage $cartStorage,
        private OrderFactory $orderFactory,
        private EntityManagerInterface $em,
        private Security $security,
        private OrderRepository $orderRepo,
    ) {
    }

    /**
     * Gets the current cart
     * 
     * @return Order
     */
    public function getCurrentCart(): Order
    {
        $cart = $this->cartStorage->getCart();
        $user = $this->security->getUser();

        if (null === $cart) {
            if ($user) {
                $cart = $this->orderRepo->findLastCartUser($user);
            }
        } elseif (null === $cart->getUser() && $user) {
            $cartOld = $this->orderRepo->findLastCartUser($user);

            if ($cartOld) {
                $cart = $this->mergeCarts($cart, $cartOld);
            }

            $cart->setUser($user);
        }

        return $cart ?? $this->orderFactory->create();
    }

    /**
     * Persists the cart in database and session
     *
     * @param Order $cart
     */
    public function save(Order $cart): void
    {
        $this->em->persist($cart);
        $this->em->flush();
        $this->cartStorage->setCart($cart);
    }

    private function mergeCarts(Order $cart, Order $cartOld): Order
    {
        foreach ($cartOld->getItems() as $item) {
            $cart->addItem($item);
        }

        $this->em->remove($cartOld);
        $this->em->flush();

        return $cart;
    }
}
