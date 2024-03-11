<?php

namespace App\Controller\Frontend;

use App\Entity\Address;
use App\Form\AddressType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/compte', name: 'app.user')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    #[Route('', '.index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('Frontend/User/index.html.twig');
    }

    #[Route('/modifier', '.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request): Response|RedirectResponse
    {
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user, ['isEdit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', 'Votre compte a été mis à jour avec succès');

            return $this->redirectToRoute('app.user.index');
        }

        return $this->render('Frontend/User/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/commandes', '.orders', methods: ['GET'])]
    public function order(): Response
    {
        return $this->render('Frontend/User/orders.html.twig');
    }

    #[Route('/adresses', '.address', methods: ['GET'])]
    public function address(): Response
    {
        return $this->render('Frontend/User/address.html.twig');
    }

    #[Route('/adresses/ajouter', '.address.add', methods: ['GET', 'POST'])]
    public function addAddress(Request $request): Response
    {
        $address = new Address();

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setUser($this->getUser());

            $this->em->persist($address);
            $this->em->flush();

            $this->addFlash('success', 'Adresse ajoutée avec succès');

            return $this->redirectToRoute('app.user.address');
        }

        return $this->render('Frontend/User/addAddress.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/adresses/{id}/modifier', '.address.edit', methods: ['GET', 'POST'])]
    public function editAddress(?Address $address, Request $request): Response
    {
        if (!$address || $address->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Adresse introuvable');

            return $this->redirectToRoute('app.user.address');
        }

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($address);
            $this->em->flush();

            $this->addFlash('success', 'Adresse modifiée avec succès');

            return $this->redirectToRoute('app.user.address');
        }

        return $this->render('Frontend/User/editAddress.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/adressses/{id}/delete', '.address.delete', methods: ['POST'])]
    public function deleteAddress(?Address $address, Request $request): RedirectResponse
    {
        if (!$address || $address->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Adresse introuvable');

            return $this->redirectToRoute('app.user.address');
        }

        if ($this->isCsrfTokenValid('delete' . $address->getId(), $request->request->get('token'))) {
            $this->em->remove($address);
            $this->em->flush();

            $this->addFlash('success', 'Adresse supprimée avec succès');
        } else {
            $this->addFlash('danger', 'Token CSRF invalide');
        }

        return $this->redirectToRoute('app.user.address');
    }

    #[Route('/adresses/{id}/defaut', '.address.default', methods: ['GET'])]
    public function defaultAddress(?Address $address): RedirectResponse
    {
        if (!$address || $address->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Adresse introuvable');

            return $this->redirectToRoute('app.user.address');
        }

        /** @var User $user */
        $user = $this->getUser();
        $user->setDefaultAddress($address);

        $this->em->persist($user);
        $this->em->flush();

        $this->addFlash('success', 'Adresse par défaut modifiée avec succès');

        return $this->redirectToRoute('app.user.address');
    }
}
