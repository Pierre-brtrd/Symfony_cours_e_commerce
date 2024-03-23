<?php

namespace App\Factory;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Class OrderFactory
 * @package App\Factory
 */
class OrderFactory
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    /**
     * Creates an order
     *
     * @return Order
     */
    public function create(): Order
    {
        $order = new Order();

        if ($this->security->getUser()) {
            $order->setUser($this->security->getUser());
        }

        return $order
            ->setStatus(Order::STATUS_CART);
    }

    /**
     * Create an item for a product
     *
     * @param Product $product
     *
     * @return OrderItem
     */
    public function createItem(Product $product): OrderItem
    {
        return (new OrderItem())
            ->setProduct($product)
            ->setQuantity(1);
    }
}
