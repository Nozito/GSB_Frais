<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\FicheFrais;
use App\Entity\FraisForfait;
use App\Entity\LigneFraisForfait;
use App\Entity\LigneFraisHorsForfait;
use App\Form\MoisType;
use App\Form\SaisieFicheForfaitType;
use App\Form\SaisieFicheHorsForfaitType;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SaisieFicheController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/saisiefiche', name: 'app_saisie_fiche', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        // Définition de la date du mois
        $moisActuel = new DateTime('first day of this month');

        // Vérification que la date du mois est correctement définie (ce qui est garanti par la ligne ci-dessus)
        if (!$moisActuel instanceof DateTimeInterface) {
            throw new Exception("La date du mois ne peut pas être nulle.");
        }

        // Recherche de la fiche de frais pour l'utilisateur et le mois courant
        $ficheFrais = $em->getRepository(FicheFrais::class)
            ->findOneBy([
                'user' => $user,
                'mois' => $moisActuel
            ]);

        // Si aucune fiche de frais n'est trouvée, on la crée
        if (!$ficheFrais) {
            $ficheFrais = new FicheFrais();
            $ficheFrais->setUser($this->getUser());

            // Définir la date du mois si elle est correctement initialisée
            $ficheFrais->setMois($moisActuel);

            // Autres champs à initialiser
            $ficheFrais->setDateModif(new DateTime());
            $ficheFrais->setNbJustificatifs(0);
            $ficheFrais->setMontantValid(0);

            // Récupération de l'état de la fiche (état "en cours", par exemple)
            $etat = $em->getRepository(Etat::class)->find(2); // Vérifiez que l'ID 2 correspond bien à l'état souhaité
            $ficheFrais->setEtat($etat);

            // Création des lignes de frais forfaitisés
            $fraisForfaitRepo = $em->getRepository(FraisForfait::class);
            $fraisKm = $fraisForfaitRepo->find(1);
            $fraisEtape = $fraisForfaitRepo->find(2);
            $fraisNuitee = $fraisForfaitRepo->find(3);
            $fraisRepas = $fraisForfaitRepo->find(4);

            // Créer et persister les lignes de frais
            $ligneFraisForfaitKm = new LigneFraisForfait();
            $ligneFraisForfaitKm->setFichesFrais($ficheFrais);
            $ligneFraisForfaitKm->setFraisForfaits($fraisKm);
            $ligneFraisForfaitKm->setQuantite(0);
            $em->persist($ligneFraisForfaitKm);

            $ligneFraisForfaitEtape = new LigneFraisForfait();
            $ligneFraisForfaitEtape->setFichesFrais($ficheFrais);
            $ligneFraisForfaitEtape->setFraisForfaits($fraisEtape);
            $ligneFraisForfaitEtape->setQuantite(0);
            $em->persist($ligneFraisForfaitEtape);

            $ligneFraisForfaitNuitee = new LigneFraisForfait();
            $ligneFraisForfaitNuitee->setFichesFrais($ficheFrais);
            $ligneFraisForfaitNuitee->setFraisForfaits($fraisNuitee);
            $ligneFraisForfaitNuitee->setQuantite(0);
            $em->persist($ligneFraisForfaitNuitee);

            $ligneFraisForfaitRepas = new LigneFraisForfait();
            $ligneFraisForfaitRepas->setFichesFrais($ficheFrais);
            $ligneFraisForfaitRepas->setFraisForfaits($fraisRepas);
            $ligneFraisForfaitRepas->setQuantite(0);
            $em->persist($ligneFraisForfaitRepas);

            // Ajouter les lignes de frais forfaitisés à la fiche de frais
            $ficheFrais->addLignefraisforfait($ligneFraisForfaitKm);
            $ficheFrais->addLignefraisforfait($ligneFraisForfaitEtape);
            $ficheFrais->addLignefraisforfait($ligneFraisForfaitNuitee);
            $ficheFrais->addLignefraisforfait($ligneFraisForfaitRepas);

            // Persister la fiche de frais
            $em->persist($ficheFrais);
        }

        // Collecte des données pour pré-remplir le formulaire
        $data = [];
        foreach ($ficheFrais->getLignefraisforfaits() as $ligne) {
            $type = $ligne->getFraisForfaits()->getId();  // Identifiant du type de frais
            switch ($type) {
                case 1:
                    $data['forfaitKm'] = $ligne->getQuantite();
                    break;
                case 2:
                    $data['forfaitEtape'] = $ligne->getQuantite();
                    break;
                case 3:
                    $data['forfaitNuitee'] = $ligne->getQuantite();
                    break;
                case 4:
                    $data['forfaitRepas'] = $ligne->getQuantite();
                    break;
            }
        }

        // Création du formulaire pour saisir les quantités des frais forfaitisés
        $formFraisForfaits = $this->createForm(SaisieFicheForfaitType::class, $data);
        $formFraisForfaits->handleRequest($request);

        if ($formFraisForfaits->isSubmitted() && $formFraisForfaits->isValid()) {
            // Mise à jour des quantités dans la fiche de frais
            $km = $formFraisForfaits->get('forfaitKm')->getData();
            $etape = $formFraisForfaits->get('forfaitEtape')->getData();
            $nuitee = $formFraisForfaits->get('forfaitNuitee')->getData();
            $repas = $formFraisForfaits->get('forfaitRepas')->getData();

            // Mise à jour des quantités dans les lignes de frais forfaitisés
            $ficheFrais->getLignefraisforfaits()[0]->setQuantite($km);
            $ficheFrais->getLignefraisforfaits()[1]->setQuantite($etape);
            $ficheFrais->getLignefraisforfaits()[2]->setQuantite($nuitee);
            $ficheFrais->getLignefraisforfaits()[3]->setQuantite($repas);

            // Mettre à jour la date de modification
            $ficheFrais->setDateModif(new DateTime());

            // Persister et sauvegarder les modifications
            $em->persist($ficheFrais);
            $em->flush();
        }

        // Création du formulaire pour saisir les frais hors forfait
        $formHorsForfaits = $this->createForm(SaisieFicheHorsForfaitType::class);
        $formHorsForfaits->handleRequest($request);

        if ($formHorsForfaits->isSubmitted() && $formHorsForfaits->isValid()) {
            // Enregistrement des frais hors forfait
            $dataHorsForfait = $formHorsForfaits->getData();

            $dateFrais = $dataHorsForfait['date'];
            $anneeActuelle = (new DateTime())->format('Y');

            if ($dateFrais->format('Y') !== $anneeActuelle) {
                $this->addFlash('error', 'La date d’engagement doit se situer dans l’année écoulée');
                return $this->redirectToRoute('app_saisie_fiche');
            }
            $ligneHorsForfait = new LigneFraisHorsForfait();
            $ligneHorsForfait->setFichesFrais($ficheFrais);
            $ligneHorsForfait->setDate($dataHorsForfait['date']);
            $ligneHorsForfait->setLibelle($dataHorsForfait['libelle']);
            $ligneHorsForfait->setMontant($dataHorsForfait['montant']);

            // Persister et ajouter à la fiche de frais
            $em->persist($ligneHorsForfait);
            $ficheFrais->addLignesFraisHorsForfait($ligneHorsForfait);

            // Mettre à jour la date de modification
            $ficheFrais->setDateModif(new DateTime());
            $em->flush();

            // Message de confirmation
            $this->addFlash('success', 'Frais hors forfait enregistré avec succès');
            return $this->redirectToRoute('app_saisie_fiche');
        }

        // Finaliser l'enregistrement avec un seul appel à flush
        $em->flush();

        // Format du mois pour l'affichage
        $moisFormatte = $moisActuel->format('M Y');
        $lignesFraisHorsForfait = $ficheFrais->getLignesFraisHorsForfait();

        // Rendu de la vue
        return $this->render('saisie_fiche/index.html.twig', [
            'formForfaits' => $formFraisForfaits->createView(),
            'moisActuel' => $moisFormatte,
            'formHorsForfaits' => $formHorsForfaits->createView(),
            'lignesFraisHorsForfait' => $lignesFraisHorsForfait,
        ]);
    }

    #[Route('/saisiefiche/delete/{id}', name: 'app_saisie_fiche_delete', methods: ['POST'])]
    public function delete(Request $request, LigneFraisHorsForfait $ligneFraisHorsForfait, EntityManagerInterface $em): Response
    {
        // Suppression d'une ligne de frais hors forfait
        $em->remove($ligneFraisHorsForfait);
        $em->flush();

        $this->addFlash('success', 'Frais hors forfait supprimé avec succès');
        return $this->redirectToRoute('app_saisie_fiche');
    }
}