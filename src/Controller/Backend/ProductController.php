<?php

namespace App\Controller\Backend;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/products', 'admin.products')]
class ProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepo,
        private EntityManagerInterface $em,
    ) {
    }

    #[Route('', '.index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('Backend/Products/index.html.twig', [
            'products' => $this->productRepo->findAll(),
        ]);
    }

    #[Route('/create', '.create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response|RedirectResponse
    {
        $product = new Product;

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($product);
            $this->em->flush();

            return $this->redirectToRoute('admin.products.index');
        }

        return $this->render('Backend/Products/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', '.edit', methods: ['GET', 'POST'])]
    public function edit(?Product $product, Request $request): Response|RedirectResponse
    {
        if (!$product) {
            $this->addFlash('error', 'Produit introuvable');

            return $this->redirectToRoute('admin.products.index');
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Produit modifié avec succès');

            return $this->redirectToRoute('admin.products.index');
        }

        return $this->render('Backend/Products/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', '.delete', methods: ['POST'])]
    public function delete(?Product $product, Request $request): RedirectResponse
    {
        if (!$product) {
            $this->addFlash('error', 'Produit introuvable');

            return $this->redirectToRoute('admin.products.index');
        }

        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('token'))) {
            $this->em->remove($product);
            $this->em->flush();

            $this->addFlash('success', 'Produit supprimé avec succès');
        } else {
            $this->addFlash('error', 'Token CSRF invalide');
        }

        return $this->redirectToRoute('admin.products.index');
    }
}
