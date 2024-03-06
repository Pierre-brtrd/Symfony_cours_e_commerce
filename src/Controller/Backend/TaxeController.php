<?php

namespace App\Controller\Backend;

use App\Entity\Taxe;
use App\Form\TaxeType;
use App\Repository\TaxeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/taxes', 'admin.taxes')]
class TaxeController extends AbstractController
{
    #[Route('', name: '.index', methods: ['GET'])]
    public function index(TaxeRepository $taxeRepository): Response
    {
        return $this->render('Backend/Taxes/index.html.twig', [
            'taxes' => $taxeRepository->findAll(),
        ]);
    }

    #[Route('/create', name: '.create', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $taxe = new Taxe();
        $form = $this->createForm(TaxeType::class, $taxe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($taxe);
            $entityManager->flush();

            $this->addFlash('success', 'Taxe ajoutée avec succès');

            return $this->redirectToRoute('admin.taxes.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('Backend/Taxes/new.html.twig', [
            'taxe' => $taxe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: '.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Taxe $taxe, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TaxeType::class, $taxe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Taxe modifiée avec succès');

            return $this->redirectToRoute('admin.taxes.index');
        }

        return $this->render('Backend/Taxes/edit.html.twig', [
            'taxe' => $taxe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: '.delete', methods: ['POST'])]
    public function delete(Request $request, Taxe $taxe, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $taxe->getId(), $request->request->get('token'))) {
            $entityManager->remove($taxe);
            $entityManager->flush();

            $this->addFlash('success', 'Taxe supprimée avec succès');
        } else {
            $this->addFlash('error', 'Token CSRF invalide');
        }

        return $this->redirectToRoute('admin.taxes.index');
    }
}
