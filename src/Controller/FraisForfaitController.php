<?php

namespace App\Controller;

use App\Entity\FraisForfait;
use App\Form\FraisForfaitType;
use App\Repository\FraisForfaitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/frais/forfait')]
final class FraisForfaitController extends AbstractController
{
    #[Route(name: 'app_frais_forfait_index', methods: ['GET'])]
    public function index(FraisForfaitRepository $fraisForfaitRepository): Response
    {
        return $this->render('frais_forfait/index.html.twig', [
            'frais_forfaits' => $fraisForfaitRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_frais_forfait_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $fraisForfait = new FraisForfait();
        $form = $this->createForm(FraisForfaitType::class, $fraisForfait);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($fraisForfait);
            $entityManager->flush();

            return $this->redirectToRoute('app_frais_forfait_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('frais_forfait/new.html.twig', [
            'frais_forfait' => $fraisForfait,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_frais_forfait_show', methods: ['GET'])]
    public function show(FraisForfait $fraisForfait): Response
    {
        return $this->render('frais_forfait/show.html.twig', [
            'frais_forfait' => $fraisForfait,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_frais_forfait_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FraisForfait $fraisForfait, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FraisForfaitType::class, $fraisForfait);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_frais_forfait_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('frais_forfait/edit.html.twig', [
            'frais_forfait' => $fraisForfait,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_frais_forfait_delete', methods: ['POST'])]
    public function delete(Request $request, FraisForfait $fraisForfait, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$fraisForfait->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($fraisForfait);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_frais_forfait_index', [], Response::HTTP_SEE_OTHER);
    }
}
