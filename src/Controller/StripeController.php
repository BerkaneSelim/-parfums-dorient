<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\ProductRepository;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class StripeController extends AbstractController
{
    #[Route('/paiement', name: 'app_stripe_checkout')]
    public function checkout(
        Request $request,
        StripeService $stripeService
    ): Response {
        $cart = $request->getSession()->get('cart', []);

        if (empty($cart)) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('app_cart');
        }

        $total = $request->getSession()->get('stripe_total', 0);

        return $this->render('stripe/checkout.html.twig', [
            'stripe_public_key' => $stripeService->getPublicKey(),
            'total'             => $total,
        ]);
    }

    #[Route('/paiement/intent', name: 'app_stripe_intent', methods: ['POST'])]
    public function createIntent(
        Request $request,
        StripeService $stripeService
    ): JsonResponse {
        $data   = json_decode($request->getContent(), true);
        $amount = $data['amount'] ?? 0;

        try {
            $intent = $stripeService->createPaymentIntent((int) $amount);
            return new JsonResponse(['clientSecret' => $intent->client_secret]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/paiement/success', name: 'app_stripe_success')]
    public function success(
        Request $request,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $session = $request->getSession();
        $cart    = $session->get('cart', []);

        if (empty($cart)) {
            return $this->redirectToRoute('app_home');
        }

        // Créer la commande
        $order = new Order();
        $order->setUser($this->getUser());
        $order->setOrderNumber('CMD-' . time());
        $order->setStatus('payee');
        $order->setCreatedAt(new \DateTime());
        $order->setShippingName($session->get('checkout_nom', ''));
        $order->setShippingAddress($session->get('checkout_adresse', ''));
        $order->setShippingCity($session->get('checkout_ville', ''));
        $order->setShippingPhone($session->get('checkout_telephone', ''));

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

        // Email de confirmation
        /** @var \App\Entity\User $user */
        $user  = $this->getUser();
        $email = (new Email())
            ->from('noreply@parfumdorient.fr')
            ->to($user->getEmail())
            ->subject('Confirmation commande #' . $order->getOrderNumber())
            ->html($this->renderView('emails/confirmation_commande.html.twig', [
                'order' => $order
            ]));
        $mailer->send($email);

        // Nettoyer la session
        $session->remove('cart');
        $session->remove('stripe_total');
        $session->remove('checkout_nom');
        $session->remove('checkout_adresse');
        $session->remove('checkout_ville');
        $session->remove('checkout_telephone');

        $this->addFlash('success', 'Paiement réussi ! Votre commande a été confirmée.');

        return $this->render('checkout/success.html.twig', [
            'order' => $order,
        ]);
    }
}
