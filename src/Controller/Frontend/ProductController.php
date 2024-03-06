<?php

namespace App\Controller\Frontend;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    #[Route('/{code}', '.show', methods: ['GET'])]
    public function show(?Product $product): Response|RedirectResponse
    {
        if (!$product) {
            $this->addFlash('error', 'Produit non trouvé');

            return $this->redirectToRoute('app.products.index');
        }

        return $this->render('Frontend/Products/show.html.twig', [
            'product' => $product,
        ]);
    }
}
