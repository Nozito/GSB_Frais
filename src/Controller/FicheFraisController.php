<?php

namespace App\Controller;

use App\Entity\FicheFrais;
use App\Form\MoisType;
use App\Form\FicheFraisType;
use App\Repository\FicheFraisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use \IntlDateFormatter;

#[Route('/fiche/frais')]
final class FicheFraisController extends AbstractController
{
    #[Route(name: 'app_fiche_frais_index', methods: ['GET', 'POST'])]
    public function index(FicheFraisRepository $ficheFraisRepository, Request $request): Response
    {
        $user = $this->getUser(); // Récupère l'utilisateur connecté

        // Récupère les mois distincts pour l'utilisateur
        $moisDisponibles = $ficheFraisRepository->findDistinctMoisByUser($user);

        // Utilise IntlDateFormatter pour afficher les mois en français
        $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);
        $formatter->setPattern('MMMM yyyy'); // Exemple : "janvier 2023"

        $moisChoices = array_combine(
            array_map(fn($item) => $formatter->format($item['mois']), $moisDisponibles),
            array_map(fn($item) => $item['mois']->format('Y-m'), $moisDisponibles)
        );

        // Crée le formulaire
        $form = $this->createForm(MoisType::class, null, [
            'mois_choices' => $moisChoices,
        ]);

        $form->handleRequest($request);

        // Vérifier la donnée du mois
        if ($form->isSubmitted() && $form->isValid()) {
            $selectedMois = $form->get('mois')->getData(); // Récupère la valeur 'Y-m'
            dump($selectedMois); // Log pour vérifier la valeur de mois

            // Convertir le mois sélectionné en DateTime
            $dateMois = \DateTime::createFromFormat('Y-m', $selectedMois);

            if ($dateMois) {
                // Log pour vérifier la conversion du mois
                dump($dateMois);

                // Filtrer les fiches de frais pour ce mois
                $ficheFrais = $ficheFraisRepository->findBy([
                    'user' => $user,
                    'mois' => $dateMois,
                ]);

                // Log pour vérifier les fiches récupérées
                dump($ficheFrais);
            }

            // Si pas de fiche trouvée, ajouter un message d'erreur
            if (empty($ficheFrais)) {
                $this->addFlash('warning', 'Aucune fiche de frais trouvée pour ce mois.');
            }

            return $this->render('fiche_frais/index.html.twig', [
                'fiche_frais' => $ficheFrais,
                'form' => $form->createView(),
            ]);
        }

        // Si le formulaire n'est pas soumis ou invalide
        return $this->render('fiche_frais/index.html.twig', [
            'fiche_frais' => [],  // Aucune fiche à afficher au départ
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_fiche_frais_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ficheFrai = new FicheFrais();
        $form = $this->createForm(FicheFraisType::class, $ficheFrai);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ficheFrai);
            $entityManager->flush();

            return $this->redirectToRoute('app_fiche_frais_index');
        }

        return $this->render('fiche_frais/new.html.twig', [
            'fiche_frai' => $ficheFrai,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_fiche_frais_show', methods: ['GET'])]
    public function show(FicheFrais $ficheFrai): Response
    {
        return $this->render('fiche_frais/show.html.twig', [
            'fiche_frai' => $ficheFrai,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_fiche_frais_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FicheFrais $ficheFrai, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FicheFraisType::class, $ficheFrai);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_fiche_frais_index');
        }

        return $this->render('fiche_frais/edit.html.twig', [
            'fiche_frai' => $ficheFrai,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_fiche_frais_delete', methods: ['POST'])]
    public function delete(Request $request, FicheFrais $ficheFrai, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $ficheFrai->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ficheFrai);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_fiche_frais_index');
    }
}