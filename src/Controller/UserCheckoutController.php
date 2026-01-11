<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Form\OrderCheckoutType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class UserCheckoutController extends AbstractController
{
    #[Route('/commander/{id}', name: 'app_user_checkout')]
    public function checkout(
        int $id,
        Request $request,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        // Récupérer le produit
        $product = $productRepository->find($id);
        
        if (!$product) {
            $this->addFlash('error', 'Produit introuvable');
            return $this->redirectToRoute('app_home');
        }
        
        // Créer une nouvelle commande
        $order = new Order();
        $order->setUser($this->getUser());
        
        // Créer le formulaire
        $form = $this->createForm(OrderCheckoutType::class, $order);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Créer la commande
            $order->setOrderNumber('CMD-' . time());
            $order->setStatus('en_cours');
            $order->setCreatedAt(new \DateTime());
            $order->setTotalPrice($product->getPrix());
            
            // Créer l'item de commande
            $orderItem = new OrderItem();
            $orderItem->setOrderRef($order);
            $orderItem->setProduct($product);
            $orderItem->setQuantity(1);
            $orderItem->setPrice($product->getPrix());
            
            $entityManager->persist($orderItem);
            $entityManager->persist($order);
            $entityManager->flush();
            
            return $this->render('user_checkout/success.html.twig', [
                'order' => $order,
            ]);
        }
        
        return $this->render('user_checkout/index.html.twig', [
            'form' => $form,
            'product' => $product,
        ]);
    }
}
