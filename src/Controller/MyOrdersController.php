<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class MyOrdersController extends AbstractController
{
    #[Route('/mes-commandes', name: 'app_my_orders')]
    public function index(OrderRepository $orderRepository): Response
    {
        // Récupérer uniquement les commandes de l'utilisateur connecté
        $orders = $orderRepository->findBy(
            ['user' => $this->getUser()],
            ['createdAt' => 'DESC']
        );
        
        return $this->render('my_orders/index.html.twig', [
            'orders' => $orders,
        ]);
    }
    
    #[Route('/mes-commandes/{id}', name: 'app_my_orders_show')]
    public function show(OrderRepository $orderRepository, int $id): Response
    {
        $order = $orderRepository->find($id);
        
        // Vérifier que la commande appartient bien à l'utilisateur connecté
        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Commande introuvable');
        }
        
        return $this->render('my_orders/show.html.twig', [
            'order' => $order,
        ]);
    }
}
