<?php

namespace App\Controller\Backend;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/order', name: 'admin.orders')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderRepository $orderRepository,
    ) {
    }

    #[Route('', '.index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('Backend/Orders/index.html.twig', [
            'orders' => $this->orderRepository->findOrder(),
        ]);
    }

    #[Route('/{id}', '.show', methods: ['GET'])]
    public function show(Order $order): Response|RedirectResponse
    {
        if (!$order || $order->getStatus() === Order::STATUS_CART) {
            $this->addFlash('error', 'Order not found or invalid status.');

            return $this->redirectToRoute('admin.orders.index');
        }

        return $this->render('Backend/Orders/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/cancel', '.cancel', methods: ['POST'])]
    public function canceled(?Order $order, Request $request): RedirectResponse
    {
        if (!$order || $order->getStatus() === Order::STATUS_CART) {
            $this->addFlash('error', 'Order not found or invalid status.');

            return $this->redirectToRoute('admin.orders.index');
        }

        if ($this->isCsrfTokenValid('delete' . $order->getId(), $request->request->get('token'))) {
            $order->setStatus(Order::STATUS_CANCELLED);
            $this->entityManager->flush();

            $this->addFlash('success', 'Order has been canceled.');
        } else {
            $this->addFlash('error', 'Invalid token.');
        }

        return $this->redirectToRoute('admin.orders.index');
    }
}
