<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Customer;
use App\Form\CustomerType; 


class CustomerController extends AbstractController
{
    #[Route('/customerform', name: 'customerform')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $customer = new Customer();
        $customerForm = $this->createForm(CustomerType::class, $customer);
        $customerForm->handleRequest($request);
        // Ajouter la logique de traitement du formulaire si nécessaire...
        if ($customerForm->isSubmitted() && $customerForm->isValid()) {
            // Traitement des données soumises, par exemple, enregistrement dans la base de données.
            $entityManager = $doctrine->getManager();
            $client = $customerForm->getData();
            $entityManager->persist($client);
            $entityManager->flush();
            return $this->redirectToRoute('customer_list');
        }
        return $this->render('index.html.twig', [
            'customerForm' => $customerForm->createView(),
        ]);
    }

    #[Route('/customers', name: 'customer_list')]
    public function list(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Customer::class);
        $customers = $repository->findAll();
        return $this->render('list.html.twig', [
            'customers' => $customers,
        ]);
    }

    #[Route('/customer/edit/{id}', name: 'customer_edit')]
    public function edit(Request $request, ManagerRegistry $doctrine, $id): Response
    {
        $entityManager = $doctrine->getManager();
        $customer = $entityManager->getRepository(Customer::class)->find($id);
        if (!$customer) {
            throw $this->createNotFoundException('No customer found for id '.$id);
        }
        $customerForm = $this->createForm(CustomerType::class, $customer);
        $customerForm->handleRequest($request);
        if ($customerForm->isSubmitted() && $customerForm->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('customer_list');
        }
        return $this->render('edit.html.twig', [
            'customerForm' => $customerForm->createView(),
        ]);
    }

    #[Route('/customer/delete/{id}', name: 'customer_delete')]
    public function delete(ManagerRegistry $doctrine, $id): Response
    {
        $entityManager = $doctrine->getManager();
        $customer = $entityManager->getRepository(Customer::class)->find($id);
        if (!$customer) {
            throw $this->createNotFoundException('No customer found for id '.$id);
        }
        $entityManager->remove($customer);
        $entityManager->flush();
        return $this->redirectToRoute('customer_list');
    }
}