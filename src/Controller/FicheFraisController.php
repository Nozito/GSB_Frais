<?php

namespace App\Controller;

use App\Entity\FicheFrais;
use App\Form\FicheFraisType;
use App\Form\MoisType;
use App\Repository\FicheFraisRepository;
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
        $ficheFraisCollection = $this->ficheFraisRepository->findBy(['user' => $user]);

        $form = $this->createForm(MoisType::class, null, [
            'ficheFraisCollection' => $ficheFraisCollection,
        ]);

        $form->handleRequest($request);

        $selectedFicheFrais = null;
        $montantTotalForfait = 0;
        $montantTotal = 0;
        $montantsParType = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $ficheFraisId = $form->get('mois')->getData();
            if ($ficheFraisId) {
                $selectedFicheFrais = $this->ficheFraisRepository->find($ficheFraisId);

                if ($selectedFicheFrais) {
                    // Calcul des montants
                    $montantsParType = $selectedFicheFrais->calculerMontantParTypeForfait();
                    $montantTotalForfait = $selectedFicheFrais->calculerMontantTotalForfait();
                    $montantTotal = $selectedFicheFrais->calculerMontantTotal();
                }
            } else {
                $this->addFlash('error', 'Aucun mois sélectionné');
            }
        }

        return $this->render('fiche_frais/index.html.twig', [
            'form' => $form,
            'selectedFicheFrais' => $selectedFicheFrais,
            'montantTotalForfait' => $montantTotalForfait,
            'montantTotal' => $montantTotal,
            'montantsParType' => $montantsParType,
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

    #[Route('/top-visiteurs', name: 'app_fiche_frais_top_visiteurs', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_COMPTABLE')]
    public function topVisitors(Request $request, FicheFraisRepository $ficheFraisRepository): Response
    {
        // Créer un formulaire pour sélectionner le mois
        $form = $this->createForm(MoisType::class);
        $form->handleRequest($request);

        // Initialiser les variables
        $topVisitors = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $mois = $form->get('mois')->getData(); // Récupérer le mois sélectionné

            // Récupérer les 3 visiteurs médicaux avec les frais validés les plus élevés
            $topVisitors = $ficheFraisRepository->findTopVisitorsByMonth($mois);
        }

        return $this->render('fiche_frais/top_visiteurs.html.twig', [
            'form' => $form->createView(),
            'topVisitors' => $topVisitors,
        ]);
    }
}
