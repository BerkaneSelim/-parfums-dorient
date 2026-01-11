<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $message = new Message();
        $message->setUser($this->getUser());
        $message->setCreatedAt(new \DateTime());
        $message->setStatus('nouveau');
        
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($message);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre message a été envoyé avec succès !');
            return $this->redirectToRoute('app_contact');
        }
        
        return $this->render('contact/index.html.twig', [
            'form' => $form,
        ]);
    }
}
