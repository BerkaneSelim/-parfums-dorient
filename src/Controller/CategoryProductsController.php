<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryProductsController extends AbstractController
{
    #[Route('/categorie/{id}', name: 'app_category_products')]
    public function index(int $id, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->find($id);
        
        if (!$category) {
            throw $this->createNotFoundException('Catégorie introuvable');
        }
        
        return $this->render('category_products/index.html.twig', [
            'category' => $category,
            'products' => $category->getProducts(),
        ]);
    }
}
