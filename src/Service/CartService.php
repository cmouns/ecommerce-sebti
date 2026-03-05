<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    public function __construct(
        private RequestStack $requestStack,
        private ProductRepository $productRepository,
    ) {
    }

    /**
     * Ajoute un produit avec une quantité spécifique (par défaut 1).
     */
    public function add(int $id, int $quantity = 1): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        $cart[$id] = ($cart[$id] ?? 0) + $quantity;

        $session->set('cart', $cart);
    }

    /**
     * Force une quantité précise (Style Amazon).
     * Si 0, on supprime l'article.
     */
    public function setQuantity(int $id, int $quantity): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        if ($quantity <= 0) {
            $this->remove($id);

            return;
        }

        $cart[$id] = $quantity;
        $session->set('cart', $cart);
    }

    public function remove(int $id): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);
    }

    public function empty(): void
    {
        $this->requestStack->getSession()->remove('cart');
    }

    /**
     * @return array<int, array{product: \App\Entity\Product, quantity: int}>
     */
    public function getFullCart(): array
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        $fullCart = [];

        foreach ($cart as $id => $quantity) {
            $product = $this->productRepository->find($id);
            if ($product) {
                $fullCart[] = ['product' => $product, 'quantity' => $quantity];
            } else {
                $this->remove($id);
            }
        }

        return $fullCart;
    }

    public function getTotal(): int
    {
        $total = 0;
        foreach ($this->getFullCart() as $item) {
            $total += $item['product']->getPrice() * $item['quantity'];
        }

        return $total;
    }

    /**
     * Calcule le nombre total d'articles.
     */
    public function getQuantityCount(): int
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        $count = 0;
        foreach ($cart as $quantity) {
            $count += $quantity;
        }

        return $count;
    }
}
