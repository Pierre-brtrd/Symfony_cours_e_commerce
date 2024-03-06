<?php

namespace App\Factory;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use Symfony\Bundle\SecurityBundle\Security;
use Webmozart\Assert\Assert;

/**
 * Class OrderFactory
 * @package App\Factory
 */
class OrderFactory
{
    public function __construct(
        private Security $security
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
        Assert::isInstanceOf($product, Product::class, sprintf('The product must be an instance of %s', Product::class));

        return (new OrderItem())
            ->setProduct($product)
            ->setQuantity(1);
    }
}
