<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Product;
use App\Form\ProductType;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'product_list')]
    public function list(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Product::class);
        $product = $repository->findAll();
        return $this->render('product/list.html.twig', [
            'products' => $product,
        ]);
    }

    #[Route('/productform', name: 'productform')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $product = new Product();
        $productForm = $this->createForm(ProductType::class, $product);
        $productForm->handleRequest($request);
        // Ajouter la logique de traitement du formulaire si nécessaire...
        if ($productForm->isSubmitted() && $productForm->isValid()) {
            // Traitement des données soumises, par exemple, enregistrement dans la base de données.
            $entityManager = $doctrine->getManager();
            $client = $productForm->getData();
            $entityManager->persist($client);
            $entityManager->flush();
            return $this->redirectToRoute('product_list');
        }
        return $this->render('product/index.html.twig', [
            'productform' => $productForm->createView(),
        ]);
    }

    #[Route('/product/edit/{id}', name: 'product_edit')]
    public function edit(Request $request, ManagerRegistry $doctrine, $id): Response
    {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);
        if (!$product) {
            throw $this->createNotFoundException('No product found for id '.$id);
        }
        $productForm = $this->createForm(ProductType::class, $product);
        $productForm->handleRequest($request);
        if ($productForm->isSubmitted() && $productForm->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('product_list');
        }
        return $this->render('product/edit.html.twig', [
            'productform' => $productForm->createView(),
        ]);
    }

    #[Route('/product/delete/{id}', name: 'product_delete')]
    public function delete(ManagerRegistry $doctrine, $id): Response
    {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);
        if (!$product) {
            throw $this->createNotFoundException('No product found for id '.$id);
        }
        $entityManager->remove($product);
        $entityManager->flush();
        return $this->redirectToRoute('product_list');
    }
}
