<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderCheckoutType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;

#[IsGranted('ROLE_USER')]
class CheckoutController extends AbstractController
{
    #[Route('/commande-panier', name: 'app_checkout_cart')]
    public function checkoutCart(
        Request $request,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        
        if (empty($cart)) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('app_cart');
        }
        
        $order = new Order();
        $order->setUser($this->getUser());
        
        $form = $this->createForm(OrderCheckoutType::class, $order);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            // Vérification des stocks
            foreach ($cart as $productId => $quantity) {
                $product = $productRepository->find($productId);
                if ($product && $product->getStock() < $quantity) {
                    $this->addFlash('error', 'Stock insuffisant pour : ' . $product->getNom());
                    return $this->redirectToRoute('app_cart');
                }
            }

            // Calcul du total
            $totalPrice = 0;
            foreach ($cart as $productId => $quantity) {
                $product = $productRepository->find($productId);
                if ($product) {
                    $totalPrice += $product->getPrix() * $quantity;
                }
            }

            // Stocke les infos en session pour après le paiement Stripe
            $session->set('stripe_total', $totalPrice);
            $session->set('checkout_nom', $order->getShippingName());
            $session->set('checkout_adresse', $order->getShippingAddress());
            $session->set('checkout_ville', $order->getShippingCity());
            $session->set('checkout_telephone', $order->getShippingPhone());

            // Redirige vers Stripe
            return $this->redirectToRoute('app_stripe_checkout');
        }
        
        $cartItems = [];
        $totalPrice = 0;
        
        foreach ($cart as $productId => $quantity) {
            $product = $productRepository->find($productId);
            if ($product) {
                $cartItems[] = [
                    'product'  => $product,
                    'quantity' => $quantity,
                    'subtotal' => $product->getPrix() * $quantity
                ];
                $totalPrice += $product->getPrix() * $quantity;
            }
        }

        return $this->render('checkout/index.html.twig', [
            'form'       => $form,
            'cartItems'  => $cartItems,
            'totalPrice' => $totalPrice,
        ]);
    }
}