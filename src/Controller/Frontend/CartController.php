<?php

namespace App\Controller\Frontend;

use App\Form\CartType;
use App\Manager\CartManager;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart', 'app.cart')]
class CartController extends AbstractController
{
    #[Route('', '', methods: ['GET', 'POST'])]
    public function index(CartManager $cartManager, Request $request, ProductRepository $productRepo): Response
    {
        $cart = $cartManager->getCurrentCart();

        $form = $this->createForm(CartType::class, $cart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cart->setUpdatedAt(new \DateTimeImmutable());
            $cartManager->save($cart);

            $this->addFlash('success', 'Panier mis à jour avec succès !');

            return $this->redirectToRoute('app.cart');
        }

        return $this->render('Frontend/Cart/index.html.twig', [
            'cart' => $cart,
            'form' => $form,
        ]);
    }
}
