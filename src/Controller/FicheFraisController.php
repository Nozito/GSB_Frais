<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\FicheFrais;
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
        $this->cloturerFichesFrais();
        $user = $this->getUser();
        $ficheFraisCollection = $this->ficheFraisRepository->findBy(['user' => $user]);

        $form = $this->createForm(MoisType::class, null, [
            'ficheFraisCollection' => $ficheFraisCollection, // Transmettre la collection des fiches de frais
        ]);

        $form->handleRequest($request);

        $selectedFicheFrais = null;
        $ligneFraisForfait = null;
        $ligneFraisHorsForfait = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $ficheFraisId = $form->get('mois')->getData();
            if ($ficheFraisId) {
                $selectedFicheFrais = $this->ficheFraisRepository->find($ficheFraisId);
                $ligneFraisForfait = $selectedFicheFrais?->getLignefraisforfaits();
                $ligneFraisHorsForfait = $selectedFicheFrais?->getLignesfraishorsforfait();
            } else {
                $this->addFlash('error', 'Aucun mois sélectionné');
            }
        }

        return $this->render('fiche_frais/index.html.twig', [
            'form' => $form,
            'selectedFicheFrais' => $selectedFicheFrais,
            'ligneFraisForfait' => $ligneFraisForfait,
            'ligneFraisHorsForfait' => $ligneFraisHorsForfait,
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

    public function cloturerFichesFrais(): void
    {
        // Récupérer toutes les fiches de frais
        $ficheFraisList = $this->ficheFraisRepository->findAll();

        // Obtenir la date actuelle
        $currentDate = new DateTime();
        $currentMonth = $currentDate->format('m/Y');

        foreach ($ficheFraisList as $fiche) {
            if ($fiche->getMois()->format('m/Y') < $currentMonth && $fiche->getEtat()->getLibelle() !== 'CL') {
                $etatCloturee = $this->entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'CL']);

                if ($etatCloturee) {
                    $fiche->setEtat($etatCloturee);
                    $fiche->setDateModif(new DateTime());
                    $this->entityManager->flush();
                }
            }
        }
    }
}
