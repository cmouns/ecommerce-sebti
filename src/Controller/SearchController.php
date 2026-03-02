<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $query = (string) $request->query->get('q', '');

        $products = [];

        if ('' !== $query) {
            $products = $productRepository->searchByName($query);
        }

        return $this->render('search/index.html.twig', [
            'products' => $products,
            'query' => $query,
        ]);
    }
}
