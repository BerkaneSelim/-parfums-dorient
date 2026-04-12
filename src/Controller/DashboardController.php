<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\MessageRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        UserRepository $userRepository,
        OrderRepository $orderRepository,
        MessageRepository $messageRepository
    ): Response
    {
        $totalProducts = $productRepository->count([]);
        $totalCategories = $categoryRepository->count([]);
        $totalUsers = $userRepository->count([]);
        $totalOrders = $orderRepository->count([]);
        $totalMessages = $messageRepository->count([]);

        $orders = $orderRepository->findAll();
        $totalRevenue = 0;
        foreach ($orders as $order) {
            $totalRevenue += $order->getTotalPrice();
        }

        $latestOrders = $orderRepository->findBy([], ['createdAt' => 'DESC'], 5);
        $latestMessages = $messageRepository->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('dashboard/index.html.twig', [
            'totalProducts' => $totalProducts,
            'totalCategories' => $totalCategories,
            'totalUsers' => $totalUsers,
            'totalOrders' => $totalOrders,
            'totalMessages' => $totalMessages,
            'totalRevenue' => $totalRevenue,
            'latestOrders' => $latestOrders,
            'latestMessages' => $latestMessages,
        ]);
    }
}