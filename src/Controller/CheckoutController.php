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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[IsGranted('ROLE_USER')]
class CheckoutController extends AbstractController
{
    #[Route('/commande-panier', name: 'app_checkout_cart')]
    public function checkoutCart(
        Request $request,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response
    {
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

            foreach ($cart as $productId => $quantity) {
                $product = $productRepository->find($productId);
                if ($product && $product->getStock() < $quantity) {
                    $this->addFlash('error', 'Stock insuffisant pour le produit : ' . $product->getNom());
                    return $this->redirectToRoute('app_cart');
                }
            }

            $order->setOrderNumber('CMD-' . time());
            $order->setStatus('en_cours');
            $order->setCreatedAt(new \DateTime());
            
            $totalPrice = 0;
            
            foreach ($cart as $productId => $quantity) {
                $product = $productRepository->find($productId);
                
                if ($product) {
                    $orderItem = new OrderItem();
                    $orderItem->setOrderRef($order);
                    $orderItem->setProduct($product);
                    $orderItem->setQuantity($quantity);
                    $orderItem->setPrice($product->getPrix());

                    $totalPrice += $product->getPrix() * $quantity;

                    $product->setStock($product->getStock() - $quantity);
                    
                    $entityManager->persist($orderItem);
                }
            }
            
            $order->setTotalPrice($totalPrice);
            
            $entityManager->persist($order);
            $entityManager->flush();
            
            /** @var \App\Entity\User $user */
            $user = $this->getUser();
            $email = (new Email())
                ->from('noreply@parfumdorient.fr')
                ->to($user->getEmail())
                ->subject('Confirmation de votre commande #' . $order->getOrderNumber())
                ->html($this->renderView('emails/confirmation_commande.html.twig', [
                    'order' => $order
                ]));
            
            $mailer->send($email);
            
            $session->remove('cart');
            
            $this->addFlash('success', 'Votre commande a été validée ! Un email de confirmation vous a été envoyé.');
            
            return $this->render('checkout/success.html.twig', [
                'order' => $order,
            ]);
        }
        
        $cartItems = [];
        $totalPrice = 0;
        
        foreach ($cart as $productId => $quantity) {
            $product = $productRepository->find($productId);
            if ($product) {
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $product->getPrix() * $quantity
                ];
                $totalPrice += $product->getPrix() * $quantity;
            }
        }
        
        return $this->render('checkout/index.html.twig', [
            'form' => $form,
            'cartItems' => $cartItems,
            'totalPrice' => $totalPrice,
        ]);
    }
}