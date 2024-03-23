<?php

namespace App\Controller\Frontend;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('', name: 'app.home', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('Frontend/Home/index.html.twig', [
            'products' => $productRepository->findLatest(3)
        ]);
    }
}
