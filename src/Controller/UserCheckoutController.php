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
class UserCheckoutController extends AbstractController
{
    #[Route('/commander/{id}', name: 'app_user_checkout')]
    public function checkout(
        int $id,
        Request $request,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response
    {
        $product = $productRepository->find($id);
        
        if (!$product) {
            $this->addFlash('error', 'Produit introuvable');
            return $this->redirectToRoute('app_home');
        }

        if ($product->getStock() <= 0) {
            $this->addFlash('error', 'Ce produit est en rupture de stock.');
            return $this->redirectToRoute('app_home');
        }
        
        $order = new Order();
        $order->setUser($this->getUser());
        
        $form = $this->createForm(OrderCheckoutType::class, $order);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $order->setOrderNumber('CMD-' . time());
            $order->setStatus('en_cours');
            $order->setCreatedAt(new \DateTime());
            $order->setTotalPrice($product->getPrix());
            
            $orderItem = new OrderItem();
            $orderItem->setOrderRef($order);
            $orderItem->setProduct($product);
            $orderItem->setQuantity(1);
            $orderItem->setPrice($product->getPrix());

            $product->setStock($product->getStock() - 1);
            
            $entityManager->persist($orderItem);
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

            $this->addFlash('success', 'Votre commande a été validée ! Un email de confirmation vous a été envoyé.');
            
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
