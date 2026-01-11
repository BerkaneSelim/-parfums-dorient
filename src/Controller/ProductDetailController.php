<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductDetailController extends AbstractController
{
    #[Route('/produit/{id}', name: 'app_product_detail')]
    public function index(int $id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);
        
        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable');
        }
        
        return $this->render('product_detail/index.html.twig', [
            'product' => $product,
        ]);
    }
}
