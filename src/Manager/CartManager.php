<?php

namespace App\Manager;

use App\Entity\Order;
use App\Factory\OrderFactory;
use App\Storage\CartSessionStorage;
use Doctrine\ORM\EntityManagerInterface;

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
        private EntityManagerInterface $em
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
}
