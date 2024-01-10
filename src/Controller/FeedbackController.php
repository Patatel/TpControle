<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Feedback;
use App\Form\FeedbackType;

class FeedbackController extends AbstractController
{
    #[Route('/feedback', name: 'feedback')]
    public function feedback(Request $request, ManagerRegistry $doctrine): Response 
    {
        $feedback = new Feedback();
        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traitement des données soumises, par exemple, enregistrement dans la base de données.
            $entityManager = $doctrine->getManager();
            $client = $form->getData();
            $entityManager->persist($client);
            $entityManager->flush();
            return $this->redirectToRoute('product_list');
        }

        return $this->render('feedback/index.html.twig', [
        'feedbackform' => $form->createView(),
        ]);
    } 

    #[Route('/feedbacks', name: 'feedback_list')]
    public function list(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Feedback::class);
        $feed = $repository->findAll();
        return $this->render('feedback/list.html.twig', [
            'feedbacks' => $feed,
        ]);
    }

    #[Route('/feedback/edit/{id}', name: 'feedback_edit')]
    public function edit(Request $request, ManagerRegistry $doctrine, $id): Response
    {
        $entityManager = $doctrine->getManager();
        $feed = $entityManager->getRepository(Feedback::class)->find($id);
        if (!$feed) {
            throw $this->createNotFoundException('No feedback found for id '.$id);
        }
        $feedForm = $this->createForm(FeedbackType::class, $feed);
        $feedForm->handleRequest($request);
        if ($feedForm->isSubmitted() && $feedForm->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('feedback_list');
        }
        return $this->render('feedback/edit.html.twig', [
            'feedbackform' => $feedForm->createView(),
        ]);
    }

    #[Route('/feedback/delete/{id}', name: 'feedback_delete')]
    public function delete(ManagerRegistry $doctrine, $id): Response
    {
        $entityManager = $doctrine->getManager();
        $feed = $entityManager->getRepository(Feedback::class)->find($id);
        if (!$feed) {
            throw $this->createNotFoundException('No feedback found for id '.$id);
        }
        $entityManager->remove($feed);
        $entityManager->flush();
        return $this->redirectToRoute('feedback_list');
    }
}
