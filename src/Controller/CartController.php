<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    #[Route('/panier', name: 'app_cart')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        // Récupérer le panier depuis la session
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        
        // Récupérer les produits
        $cartItems = [];
        $total = 0;
        
        foreach ($cart as $productId => $quantity) {
            $product = $productRepository->find($productId);
            if ($product) {
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $product->getPrix() * $quantity
                ];
                $total += $product->getPrix() * $quantity;
            }
        }
        
        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }
    
    #[Route('/panier/ajouter/{id}', name: 'app_cart_add')]
    public function add(int $id, Request $request): Response
    {
        // Récupérer le panier
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        
        // Ajouter le produit
        if (isset($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }
        
        // Sauvegarder
        $session->set('cart', $cart);
        
        $this->addFlash('success', 'Produit ajouté au panier !');
        return $this->redirectToRoute('app_home');
    }
    
    #[Route('/panier/supprimer/{id}', name: 'app_cart_remove')]
    public function remove(int $id, Request $request): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        
        if (isset($cart[$id])) {
            unset($cart[$id]);
        }
        
        $session->set('cart', $cart);
        
        return $this->redirectToRoute('app_cart');
    }
    
    #[Route('/panier/vider', name: 'app_cart_clear')]
    public function clear(Request $request): Response
    {
        $session = $request->getSession();
        $session->remove('cart');
        
        return $this->redirectToRoute('app_cart');
    }
}