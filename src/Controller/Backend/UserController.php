<?php

namespace App\Controller\Backend;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/users', 'admin.users')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepo
    ) {
    }

    #[Route('', '.index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('Backend/Users/index.html.twig', [
            'users' => $this->userRepo->findAll(),
        ]);
    }
}
