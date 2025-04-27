<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\FraisForfait;
use App\Entity\LigneFraisForfait;
use App\Entity\LigneFraisHorsForfait;
use App\Repository\FicheFraisRepository;
use App\Repository\LigneFraisForfaitRepository;
use App\Repository\LigneFraisHorsForfaitRepository;
use App\Repository\UserRepository;
use App\Entity\FicheFrais;
use DateMalformedStringException;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comptable')]
#[IsGranted('ROLE_COMPTABLE')]
class ComptableController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/', name: 'comptable_dashboard')]
    public function dashboard(FicheFraisRepository $ficheFraisRepository): Response
    {
        // Comptage des fiches
        $totalFiches = $ficheFraisRepository->count([]);

        // Récupération des fiches validées (état = RB ou VA)
        $ficheValidees = $ficheFraisRepository->createQueryBuilder('f')
            ->leftJoin('f.etat', 'e')  // Jointure avec l'entité Etat
            ->where('e.libelle IN (:validStates)')  // Utilisation de l'attribut "libelle" de l'entité Etat
            ->setParameter('validStates', ['RB', 'VA'])
            ->getQuery()
            ->getResult();

        // Comptage des fiches validées
        $fichesValidees = count($ficheValidees);

        // Calcul du montant total des fiches (validées ou non validées)
        $totalMontant = array_reduce($ficheFraisRepository->findAll(), function ($carry, $fiche) {
            return $carry + $fiche->getMontantValid();
        }, 0);
        $totalMontantFormatted = number_format($totalMontant, 0, ',', ' ');

        // Dernières fiches (5 dernières)
        $dernieresFiches = $ficheFraisRepository->findBy([], ['dateModif' => 'DESC'], 5);

        // Mois pour les graphiques
        $moisLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sept', 'Oct', 'Nov', 'Déc'];
        $fichesCount = array_fill(0, 12, 0);

        foreach ($ficheFraisRepository->findAll() as $fiche) {
            $mois = (int) $fiche->getMois()->format('n');
            $fichesCount[$mois - 1]++;
        }

        return $this->render('comptable/index.html.twig', [
            'totalFiches' => $totalFiches,
            'fichesValidees' => $fichesValidees,
            'totalMontantFormatted' => $totalMontantFormatted,
            'dernieresFiches' => $dernieresFiches,
            'moisLabels' => $moisLabels,
            'fichesCount' => $fichesCount,
        ]);
    }

    /**
     * @throws DateMalformedStringException
     */
    #[Route('/fiches-frais', name: 'comptable_fiches_frais')]
    public function fichesFrais(Request $request, FicheFraisRepository $ficheFraisRepository, UserRepository $userRepository): Response
    {
        $userId = $request->query->get('user');
        $mois = $request->query->get('month');
        $etat = $request->query->get('etat');

        // Crée un queryBuilder pour récupérer les fiches de frais
        $queryBuilder = $ficheFraisRepository->createQueryBuilder('f');

        // Filtrer par utilisateur si un utilisateur est sélectionné
        if ($userId) {
            $queryBuilder->andWhere('f.user = :user')->setParameter('user', $userId);
        }

        // Filtrer par mois si un mois est sélectionné
        if ($mois && preg_match('/^\d{4}-\d{2}$/', $mois)) {
            $dateDebut = new \DateTimeImmutable($mois . '-01');  // On utilise le 1er du mois
            $queryBuilder->andWhere('f.mois >= :startMonth')
                ->andWhere('f.mois < :endMonth')
                ->setParameter('startMonth', $dateDebut->format('Y-m-d'))
                ->setParameter('endMonth', $dateDebut->modify('+1 month')->format('Y-m-d'));
        }

        // Filtrer par état si un état est sélectionné
        if (!empty($etat)) {
            $queryBuilder->andWhere('f.etat = :etat')->setParameter('etat', $etat);
        }

        // Récupérer les résultats filtrés
        $ficheFrais = $queryBuilder->getQuery()->getResult();

        // Calcul du montant total validé pour les fiches avec état "Remboursée" ou "Validée"
        $totalMontant = array_reduce($ficheFrais, fn($carry, $fiche) =>
        in_array($fiche->getEtat()->getLibelle(), ['RB', 'VA'])
            ? $carry + $fiche->getMontantValid()
            : $carry, 0);
        $totalMontantFormatted = number_format($totalMontant, 0, ',', ' ');

        // Récupérer les années distinctes
        $years = array_unique(array_map(fn($fiche) => $fiche->getMois()->format('Y'), $ficheFrais));
        $numberOfYears = count($years);

        // Récupérer les mois distincts pour l'utilisateur si sélectionné
        $months = [];
        if ($userId) {
            $qb = $ficheFraisRepository->createQueryBuilder('f')
                ->select('DISTINCT f.mois')
                ->where('f.user = :user')
                ->setParameter('user', $userId);

            if ($etat) {
                $qb->andWhere('f.etat = :etat')->setParameter('etat', $etat);
            }

            $userFiches = $qb->orderBy('f.mois', 'ASC')->getQuery()->getResult();

            foreach ($userFiches as $fiche) {
                $moisString = $fiche['mois']->format('Y-m');
                $months[$moisString] = $fiche['mois']->format('m/Y');
            }
        }

        // Récupérer tous les utilisateurs
        $users = $userRepository->findAll();

        return $this->render('comptable/fiches_frais.html.twig', [
            'users' => $users,
            'selectedUser' => $userId,
            'selectedMonth' => $mois,
            'ficheFrais' => $ficheFrais,
            'numberOfYears' => $numberOfYears,
            'totalMontantFormatted' => $totalMontantFormatted,
            'months' => $months,
            'selectedEtat' => $etat,
        ]);
    }



    #[Route('/fiche-frais/{id}', name: 'comptable_fiche_frais_detail')]
    public function ficheFraisDetail(FicheFrais $ficheFrais, Request $request, EntityManagerInterface $em): Response
    {
        // Si un formulaire pour modifier une ligne de frais hors forfait a été soumis
        if ($request->isMethod('POST')) {
            $ligneFraisHorsForfaitId = $request->request->get('ligneFraisHorsForfaitId');
            $action = $request->request->get('action');

            $ligneFraisHorsForfait = $em->getRepository(LigneFraisHorsForfait::class)->find($ligneFraisHorsForfaitId);

            if ($ligneFraisHorsForfait && $action === 'refuser') {
                $prefixe = 'REFUSE : ';
                $libelleActuel = $ligneFraisHorsForfait->getLibelle();
                $maxLength = 255;

                $nouveauLibelle = $prefixe . $libelleActuel;
                if (strlen($nouveauLibelle) > $maxLength) {
                    $tailleAutorisee = $maxLength - strlen($prefixe);
                    $libelleTronque = substr($libelleActuel, 0, $tailleAutorisee);
                    $nouveauLibelle = $prefixe . $libelleTronque;
                }

                $ligneFraisHorsForfait->setLibelle($nouveauLibelle);
                $em->flush();

                $this->addFlash('success', 'Le frais hors forfait a été marqué comme refusé.');
            } else {
                $this->addFlash('error', 'Erreur : ligne de frais hors forfait non trouvée.');
            }

            return $this->redirectToRoute('comptable_fiche_frais_detail', ['id' => $ficheFrais->getId()]);
        }

        // Afficher la page normale
        return $this->render('comptable/fiche_frais_detail.html.twig', [
            'fiche' => $ficheFrais,
            'ligneFraisForfaits' => $ficheFrais->getLignefraisforfaits(),
            'ligneFraisHorsForfaits' => $ficheFrais->getLignesfraishorsforfait(),
        ]);
    }

    #[Route('/fiche-frais/{id}/valider', name: 'comptable_fiche_frais_valider', methods: ['POST'])]
    public function validerFiche(FicheFrais $ficheFrais, EntityManagerInterface $em): Response
    {
        $etatValide = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'VA']);

        if ($etatValide) {
            $ficheFrais->setEtat($etatValide);

            $ficheFrais->setDateModif(new DateTime());

            $em->flush();

            $this->addFlash('success', 'La fiche de frais a été validée avec succès.');

            return $this->redirectToRoute('comptable_fiches_frais');
        }

        $this->addFlash('error', 'Impossible de valider cette fiche de frais.');

        return $this->redirectToRoute('comptable_fiches_frais');
    }

    #[Route('/fiche-frais/{ficheid}/ligne-frais-hors-forfait/{ligneid}/refuser', name: 'comptable_frais_hors_forfait_refuser', methods: ['POST'])]
    public function refuserFraisHorsForfait(int $ficheid, int $ligneid, EntityManagerInterface $em): Response
    {
        $ficheFrais = $em->getRepository(FicheFrais::class)->find($ficheid);
        if (!$ficheFrais) {
            $this->addFlash('error', 'Fiche de frais non trouvée.');
            return $this->redirectToRoute('comptable_fiches_frais');
        }

        $ligneFraisHorsForfait = $em->getRepository(LigneFraisHorsForfait::class)->find($ligneid);
        if (!$ligneFraisHorsForfait) {
            $this->addFlash('error', 'Ligne de frais hors forfait non trouvée.');
            return $this->redirectToRoute('comptable_fiches_frais');
        }

        if (strpos($ligneFraisHorsForfait->getLibelle(), 'REFUSE :') === false) {
            $libelle = 'REFUSE : ' . $ligneFraisHorsForfait->getLibelle();

            $maxLength = 255;

            if (strlen($libelle) > $maxLength) {
                $libelle = substr($libelle, 0, $maxLength);
            }

            $ligneFraisHorsForfait->setLibelle($libelle);
            $em->flush();

            $this->addFlash('success', 'Frais hors forfait refusé et libellé mis à jour avec succès.');
        } else {
            $this->addFlash('error', 'Cette ligne de frais a déjà été refusée.');
        }

        return $this->redirectToRoute('comptable_fiche_frais_detail', ['id' => $ficheFrais->getId()]);
    }

    #[Route('/fiche-frais/{ficheid}/modifier-frais-forfait/{ligneid}', name: 'comptable_edit_frais_forfait')]
    public function modifierFraisForfait($ficheid, $ligneid, Request $request, FicheFraisRepository $ficheFraisRepository, LigneFraisForfaitRepository $ligneFraisForfaitRepository): Response
    {
        // Récupérer la fiche de frais et la ligne de frais forfait
        $fiche = $ficheFraisRepository->find($ficheid);
        $ligneFraisForfait = $ligneFraisForfaitRepository->find($ligneid);

        if (!$fiche || !$ligneFraisForfait) {
            throw $this->createNotFoundException('Fiche de frais ou ligne de frais forfait non trouvée.');
        }

        // Traitement du formulaire de modification
        if ($request->isMethod('POST')) {
            $quantite = $request->request->get('quantite');

            // Mise à jour de la quantité
            $ligneFraisForfait->setQuantite($quantite);

            // Sauvegarde en base de données
            $em = $this->entityManager;
            $em->flush();

            $this->recalculerMontant($fiche, $em);
            $this->addFlash('success', 'Montant recalculé avec succès.');

            // Après la modification, on redirige vers la même page des détails de la fiche de frais
            return $this->redirectToRoute('comptable_fiche_frais_detail', ['id' => $ficheid]);
        }

        // Rendre la page fiche_frais_detail.html.twig après modification
        return $this->render('comptable/fiche_frais_detail.html.twig', [
            'fiche' => $fiche,
            'ligneFraisForfaits' => $fiche->getLignefraisforfaits(),
            'ligneFraisHorsForfaits' => $fiche->getLignesfraishorsforfait(),
        ]);
    }

    #[Route('/fiche-frais/{ficheid}/modifier-frais-hors-forfait/{ligneid}', name: 'modifier_frais_hors_forfait')]
    public function modifierFraisHorsForfait($ficheid, $ligneid, Request $request, FicheFraisRepository $ficheFraisRepository, LigneFraisHorsForfaitRepository $ligneFraisHorsForfaitRepository): Response
    {
        // Récupérer la fiche de frais et la ligne de frais hors forfait
        $fiche = $ficheFraisRepository->find($ficheid);
        $ligneFraisHorsForfait = $ligneFraisHorsForfaitRepository->find($ligneid);

        if (!$fiche || !$ligneFraisHorsForfait) {
            throw $this->createNotFoundException('Fiche de frais ou ligne de frais hors forfait non trouvée.');
        }

        // Traitement du formulaire de modification
        if ($request->isMethod('POST')) {
            $libelle = $request->request->get('libelle');
            $montant = $request->request->get('montant');

            // Mise à jour du libellé et du montant
            $ligneFraisHorsForfait->setLibelle($libelle);
            $ligneFraisHorsForfait->setMontant($montant);

            // Sauvegarde en base de données
            $em = $this->entityManager;
            $em->flush();

            // Après la modification, on redirige vers la même page des détails de la fiche de frais
            return $this->redirectToRoute('comptable_fiche_frais_detail', ['id' => $ficheid]);
        }

        // Rendre la page fiche_frais_detail.html.twig après modification
        return $this->render('comptable/fiche_frais_detail.html.twig', [
            'fiche' => $fiche,
            'ligneFraisForfaits' => $fiche->getLignefraisforfaits(),
            'ligneFraisHorsForfaits' => $fiche->getLignesfraishorsforfait(),
        ]);
    }

    #[Route('/fiche-frais/{ficheid}/report-ligne/{ligneid}', name: 'comptable_report_ligne', methods: ['POST'])]
    public function reportLigne(int $ficheid, int $ligneid, EntityManagerInterface $em): Response
    {
        // Récupérer la fiche de frais à partir de l'ID
        $ficheFrais = $em->getRepository(FicheFrais::class)->find($ficheid);
        if (!$ficheFrais) {
            $this->addFlash('error', 'Fiche de frais non trouvée.');
            return $this->redirectToRoute('comptable_fiches_frais');
        }

        // Récupérer la ligne de frais hors forfait à partir de l'ID
        $ligneFraisHorsForfait = $em->getRepository(LigneFraisHorsForfait::class)->find($ligneid);
        if (!$ligneFraisHorsForfait) {
            $this->addFlash('error', 'Ligne de frais hors forfait non trouvée.');
            return $this->redirectToRoute('comptable_fiches_frais');
        }

        $moisSuivant = $ficheFrais->getMois()->modify('+1 month');

        $ficheFraisSuivante = $em->getRepository(FicheFrais::class)->findOneBy([
            'user' => $ficheFrais->getUser(),
            'mois' => $moisSuivant,
        ]);

        if (!$ficheFraisSuivante) {
            $ficheFraisSuivante = new FicheFrais();
            $ficheFraisSuivante->setUser($ficheFrais->getUser());
            $ficheFraisSuivante->setMois($moisSuivant);
            $ficheFraisSuivante->setEtat($em->getRepository(Etat::class)->findOneBy(['libelle' => 'CR']));
            $ficheFraisSuivante->setMontantValid(0);
            $ficheFraisSuivante->setDateModif(new \DateTime());

            $fraisForfaitRepo = $em->getRepository(FraisForfait::class);
            $fraisKm = $fraisForfaitRepo->find(1);
            $fraisEtape = $fraisForfaitRepo->find(2);
            $fraisNuitee = $fraisForfaitRepo->find(3);
            $fraisRepas = $fraisForfaitRepo->find(4);

            $ligneFraisForfaitKm = new LigneFraisForfait();
            $ligneFraisForfaitKm->setFichesFrais($ficheFraisSuivante);
            $ligneFraisForfaitKm->setFraisForfaits($fraisKm);
            $ligneFraisForfaitKm->setQuantite(0);
            $em->persist($ligneFraisForfaitKm);

            $ligneFraisForfaitEtape = new LigneFraisForfait();
            $ligneFraisForfaitEtape->setFichesFrais($ficheFraisSuivante);
            $ligneFraisForfaitEtape->setFraisForfaits($fraisEtape);
            $ligneFraisForfaitEtape->setQuantite(0);
            $em->persist($ligneFraisForfaitEtape);

            $ligneFraisForfaitNuitee = new LigneFraisForfait();
            $ligneFraisForfaitNuitee->setFichesFrais($ficheFraisSuivante);
            $ligneFraisForfaitNuitee->setFraisForfaits($fraisNuitee);
            $ligneFraisForfaitNuitee->setQuantite(0);
            $em->persist($ligneFraisForfaitNuitee);

            $ligneFraisForfaitRepas = new LigneFraisForfait();
            $ligneFraisForfaitRepas->setFichesFrais($ficheFraisSuivante);
            $ligneFraisForfaitRepas->setFraisForfaits($fraisRepas);
            $ligneFraisForfaitRepas->setQuantite(0);
            $em->persist($ligneFraisForfaitRepas);

            $em->persist($ficheFraisSuivante);
            $em->flush();
        }

        $nouvelleLigne = new LigneFraisHorsForfait();
        $nouvelleLigne->setFichesFrais($ficheFraisSuivante);
        $nouvelleLigne->setLibelle(str_replace('REFUSE : ', '', $ligneFraisHorsForfait->getLibelle()));
        $nouvelleLigne->setMontant($ligneFraisHorsForfait->getMontant());
        $nouvelleLigne->setDate($ligneFraisHorsForfait->getDate());
        $em->persist($nouvelleLigne);

        $em->remove($ligneFraisHorsForfait);
        $em->flush();

        $this->addFlash('success', 'La ligne a été reportée au mois suivant.');

        return $this->redirectToRoute('comptable_fiche_frais_detail', ['id' => $ficheFraisSuivante->getId()]);
    }

    #[Route('/fiche-valider', name: 'comptable_fiche_valide', methods: ['GET'])]
    public function payerFicheValide(FicheFraisRepository $ficheFraisRepository): Response
    {
        $fiches = $ficheFraisRepository->findBy(['etat' => 4]);

        return $this->render('comptable/fiche_valide.html.twig', [
            'fiches' => $fiches,
        ]);
    }
    #[Route('/payer-fiche/{id}', name: 'comptable_payer_fiche', methods: ['POST'])]
    public function payerFiche(FicheFrais $ficheFrais, EntityManagerInterface $entityManager): Response
    {
        if ($ficheFrais->getEtat()->getLibelle() === 'Validée') {
            $etatMiseEnPaiement = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'Mise en paiement']);

            if ($etatMiseEnPaiement) {
                $ficheFrais->setEtat($etatMiseEnPaiement);
                $ficheFrais->setDateModif(new DateTime());
                $entityManager->flush();

                $this->addFlash('success', 'La fiche a été mise en paiement avec succès.');
            }
        } else {
            $this->addFlash('error', 'La fiche ne peut pas être mise en paiement car elle n\'est pas validée.');
        }

        return $this->redirectToRoute('comptable_fiche_valide');
    }

    #[Route('/fiche-frais/{id}/mettre-en-paiement', name: 'comptable_fiche_frais_mettre_en_paiement', methods: ['POST'])]
    public function mettreEnPaiement(FicheFrais $ficheFrais, EntityManagerInterface $em): Response
    {
        if ($ficheFrais->getEtat()->getId() == 4) {
            $etatRembourse = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'RB']);
            $ficheFrais->setEtat($etatRembourse);

            $ficheFrais->setDateModif(new DateTime());

            $em->flush();

            $this->addFlash('success', 'La fiche de frais a été mise en paiement.');
        } else {
            $this->addFlash('error', 'La fiche de frais ne peut pas être mise en paiement car elle n\'est pas validée.');
        }

        return $this->redirectToRoute('comptable_fiche_frais_detail', ['id' => $ficheFrais->getId()]);
    }

    #[Route('/fetch-mois/{userId}', name: 'comptable_fetch_mois')]
    public function fetchMois(int $userId, FicheFraisRepository $ficheFraisRepository): Response    {
        $fiches = $ficheFraisRepository->findBy(['user' => $userId]);

        $mois = [];

        foreach ($fiches as $fiche) {
            $moisString = $fiche->getMois()->format('Y-m');
            $mois[$moisString] = [
                'value' => $moisString,
                'label' => $fiche->getMois()->format('m/Y'),
            ];
        }

        return $this->json(['mois' => array_values($mois)]);
    }

    private function recalculerMontant(FicheFrais $ficheFrais, EntityManagerInterface $em): void
    {
        $total = 0;

        // Frais forfaitisés
        foreach ($ficheFrais->getLignefraisforfaits() as $ligneForfait) {
            $quantite = $ligneForfait->getQuantite();
            $montantUnitaire = $ligneForfait->getFraisForfaits()->getMontant();
            $total += $quantite * $montantUnitaire;
        }

        // Frais hors forfait
        foreach ($ficheFrais->getLignesfraishorsforfait() as $ligneHorsForfait) {
            $total += $ligneHorsForfait->getMontant();
        }

        // Mise à jour
        $ficheFrais->setMontantValid($total);
        $ficheFrais->setDateModif(new \DateTime());

        $em->persist($ficheFrais);
        $em->flush();
    }
}
