<?php

namespace App\Controller\Frontend;

use App\Entity\OrderItem;
use App\Entity\Product;
use App\Filter\ProductFilter;
use App\Form\AddToCartType;
use App\Form\ProductFilterType;
use App\Manager\CartManager;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products', 'app.products')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepo,
    ) {
    }

    #[Route('', '.index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $filter = (new ProductFilter)
            ->setPage(
                $request->get('page', 1)
            )
            ->setSort(
                $request->get('sort', 'p.title')
            )
            ->setDirection(
                $request->get('direction', 'asc')
            );

        $form = $this->createForm(ProductFilterType::class, $filter);
        $form->handleRequest($request);

        $products = $this->productRepo->createFilterListShop($filter, 6);

        if ($request->get('ajax')) {
            return new JsonResponse([
                'content' => $this->renderView('Frontend/Products/_list.html.twig', [
                    'products' => $products['data'],
                ]),
                'sorting' => $this->renderView('Frontend/Products/_sorting.html.twig', [
                    'products' => $products['data'],
                ]),
                'pagination' => $this->renderView('Frontend/Products/_pagination.html.twig', [
                    'products' => $products['data'],
                ]),
                'count' => $this->renderView('Frontend/Products/_count.html.twig', [
                    'products' => $products['data'],
                ]),
                'pages' => ceil($products['data']->getTotalItemCount() / $products['data']->getItemNumberPerPage()),
            ]);
        }

        return $this->render('Frontend/Products/index.html.twig', [
            'form' => $form,
            'products' => $products['data'],
            'min' => $products['min'],
            'max' => $products['max'],
            'totalPage' => ceil($products['data']->getTotalItemCount() / $products['data']->getItemNumberPerPage()),
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
            $cart->setUpdatedAt(new \DateTimeImmutable());

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
