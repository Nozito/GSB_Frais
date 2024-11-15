<?php

namespace App\Controller;

use App\Entity\FicheFrais;
use App\Entity\User;
use App\Form\FicheFraisType;
use App\Form\MoisType;
use App\Repository\FicheFraisRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/fiche/frais')]
final class FicheFraisController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private FicheFraisRepository $ficheFraisRepository;

    public function __construct(EntityManagerInterface $entityManager, FicheFraisRepository $ficheFraisRepository)
    {
        $this->entityManager = $entityManager;
        $this->ficheFraisRepository = $ficheFraisRepository;
    }

    #[Route('/', name: 'app_fiche_frais_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $fichefrais = null;

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non valide.');
        }

        $ficheFraisList = $this->ficheFraisRepository->findBy(['user' => $user]);


        $form = $this->createForm(MoisType::class, null, [
            'fiche_frais_collection' => $ficheFraisList,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fichefrais = $form->get('ficheFrais')->getData();
            //dd($fichefrais);

            if (!$fichefrais instanceof FicheFrais) {
                throw new \LogicException('La sélection n\'est pas valide.');
            }
        }

        return $this->render('fiche_frais/index.html.twig', [
            'form' => $form->createView(),
            'fichefrais' => $fichefrais,
        ]);
    }

    #[Route('/new', name: 'app_fiche_frais_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $ficheFrais = new FicheFrais();
        $form = $this->createForm(FicheFraisType::class, $ficheFrais);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Lier la fiche à l'utilisateur connecté
            $ficheFrais->setUser($this->getUser());
            $this->entityManager->persist($ficheFrais);
            $this->entityManager->flush();

            $this->addFlash('success', 'Fiche de frais créée avec succès.');
            return $this->redirectToRoute('app_fiche_frais_index');
        }

        return $this->render('fiche_frais/new.html.twig', [
            'fiche_frais' => $ficheFrais,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_fiche_frais_show', methods: ['GET'])]
    public function show(FicheFrais $ficheFrais): Response
    {
        return $this->render('fiche_frais/show.html.twig', [
            'fiche_frais' => $ficheFrais,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_fiche_frais_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FicheFrais $ficheFrais): Response
    {
        $form = $this->createForm(FicheFraisType::class, $ficheFrais);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Fiche de frais modifiée avec succès.');
            return $this->redirectToRoute('app_fiche_frais_index');
        }

        return $this->render('fiche_frais/edit.html.twig', [
            'fiche_frais' => $ficheFrais,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_fiche_frais_delete', methods: ['POST'])]
    public function delete(Request $request, FicheFrais $ficheFrais): Response
    {
        // Vérification CSRF pour la suppression
        if ($this->isCsrfTokenValid('delete' . $ficheFrais->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($ficheFrais);
            $this->entityManager->flush();

            $this->addFlash('success', 'Fiche de frais supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_fiche_frais_index');
    }
}