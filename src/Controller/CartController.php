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
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        
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
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        
        if (isset($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }
        
        $session->set('cart', $cart);
        
        $this->addFlash('success', 'Produit ajouté au panier !');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/panier/modifier/{id}/{quantity}', name: 'app_cart_update')]
    public function update(int $id, int $quantity, Request $request): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        if ($quantity <= 0) {
            unset($cart[$id]);
        } else {
            $cart[$id] = $quantity;
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('app_cart');
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
