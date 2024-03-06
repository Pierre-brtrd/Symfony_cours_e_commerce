<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', 'app.login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authUtils): Response
    {
        return $this->render('login.html.twig', [
            'error' => $authUtils->getLastAuthenticationError(),
            'lastUsername' => $authUtils->getLastUsername(),
        ]);
    }

    #[Route('/register', 'app.register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response|RedirectResponse {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Votre compte a bien été créé');

            return $this->redirectToRoute('app.login');
        }

        return $this->render('register.html.twig', [
            'form' => $form,
        ]);
    }
}
