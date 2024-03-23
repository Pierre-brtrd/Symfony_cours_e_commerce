<?php

namespace App\Twig;

use App\Manager\CartManager;
use Twig\Extension\AbstractExtension;

class CartExtension extends AbstractExtension
{
    public function __construct(
        private CartManager $cartManager
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction('getCartNumber', [$this, 'cart']),
        ];
    }

    public function cart(): int
    {
        $cart = $this->cartManager->getCurrentCart();

        $numberItem = 0;

        foreach ($cart->getItems() as $orderDetail) {
            $numberItem += $orderDetail->getQuantity();
        }

        return $numberItem;
    }
}
