<?php

namespace App\Controller\Frontend;

use App\Entity\OrderItem;
use App\Entity\Product;
use App\Form\AddToCartType;
use App\Manager\CartManager;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Role\Role;

#[Route('/products', 'app.products')]
class ProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepo,
    ) {
    }

    #[Route('', '.index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('Frontend/Products/index.html.twig', [
            'products' => $this->productRepo->createListShop(),
        ]);
    }

    #[Route('/{code}', '.show', methods: ['GET', 'POST'])]
    public function show(?Product $product, Request $request, CartManager $cartManager): Response|RedirectResponse
    {
        if (!$product) {
            $this->addFlash('error', 'Produit non trouvé');

            return $this->redirectToRoute('app.products.index');
        }

        $orderItem = new OrderItem;

        $form = $this->createForm(AddToCartType::class, $orderItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $orderItem->setProduct($product);

            $cart = $cartManager->getCurrentCart();
            $cart->addItem($orderItem);

            $cartManager->save($cart);

            $this->addFlash('success', 'Produit ajouté au panier');

            return $this->redirectToRoute('app.products.show', ['code' => $product->getCode()]);
        }

        return $this->render('Frontend/Products/show.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }
}
