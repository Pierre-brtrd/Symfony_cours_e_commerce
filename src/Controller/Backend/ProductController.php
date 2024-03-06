<?php

namespace App\Controller\Backend;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/products', 'admin.products')]
class ProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepo
    ) {
    }

    #[Route('', '.index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('Backend/Products/index.html.twig', [
            'products' => $this->productRepo->findAll(),
        ]);
    }
}
