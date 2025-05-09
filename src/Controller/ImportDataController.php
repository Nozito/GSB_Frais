<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\FicheFrais;
use App\Entity\FraisForfait;
use App\Entity\LigneFraisForfait;
use App\Entity\LigneFraisHorsForfait;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ImportDataController extends AbstractController
{
    private $doctrine;
    private $passwordHasher;

    public function __construct(ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher)
    {
        $this->doctrine = $doctrine;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/import-data', name: 'app_import_data')]
    public function index(): Response
    {
        $path = $this->getParameter('kernel.project_dir') . '/public/visiteur.json';

        $jsonData = file_get_contents($path);
        $data = json_decode($jsonData);

        foreach ($data as $item) {
            $user = new User();
            $user->setOldId($item->id);
            $user->setNom($item->nom);
            $user->setPrenom($item->prenom);
            $user->setPassword($this->passwordHasher->hashPassword($user, $item->mdp)); // Hash the password here            $user->setAdresse($item->adresse);
            $user->setCp($item->cp);
            $user->setAdresse($item->adresse);
            $user->setVille($item->ville);
            $user->setDateEmbauche(new DateTime($item->dateEmbauche));
            $email = strtolower($item->prenom . '.' . $item->nom . '@gsb.ch');
            $user->setEmail($email);

            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($user);
        }

        $entityManager = $this->doctrine->getManager();
        $entityManager->flush();

        return $this->render('import_data/index.html.twig', [
            'controller_name' => 'ImportDataController',
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/import-data-ff', name: 'app_import_data_ff')]
    public function importFicheFrais(): Response
    {
        $path = $this->getParameter('kernel.project_dir') . '/public/fichefrais.json';
        $jsonData = file_get_contents($path);
        $data = json_decode($jsonData);

        foreach ($data as $item) {
            $user = $this->doctrine->getRepository(User::class)->findOneBy(['old_id' => $item->idVisiteur]);
            if ($user) {
                $ficheFrais = new FicheFrais();
                if (isset($item->id)) {
                    $ficheFrais->setOldId($item->id);
                }
                switch ($item->idEtat) {
                    case 'CL':
                        $ficheFrais->setEtat($this->doctrine->getRepository(Etat::class)->findOneBy(['id' => 1]));
                        break;

                    case 'CR':
                        $ficheFrais->setEtat($this->doctrine->getRepository(Etat::class)->findOneBy(['id' => 2]));
                        break;

                    case 'RB':
                        $ficheFrais->setEtat($this->doctrine->getRepository(Etat::class)->findOneBy(['id' => 3]));
                        break;

                    case 'VA':
                        $ficheFrais->setEtat($this->doctrine->getRepository(Etat::class)->findOneBy(['id' => 4]));
                        break;
                }
                $ficheFrais->setMois($item->mois);
                $ficheFrais->setNbJustificatifs($item->nbJustificatifs);
                $ficheFrais->setMontantValid($item->montantValide);
                $ficheFrais->setDateModif(new DateTime($item->dateModif));
                $ficheFrais->setUser($user);

                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($ficheFrais);
            }
        }

        $entityManager = $this->doctrine->getManager();
        $entityManager->flush();

        return $this->render('import_data/index.html.twig', [
            'controller_name' => 'ImportDataController',
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/import-data-lf', name: 'app_import_data_lf')]
    public function importLigneFrais(): Response
    {
        $path = $this->getParameter('kernel.project_dir') . '/public/lignefraisforfait.json';
        $jsonData = file_get_contents($path);
        $data = json_decode($jsonData);

        if ($data === null) {
            throw new Exception('Failed to decode JSON data.');
        }

        $entityManager = $this->doctrine->getManager();

        foreach ($data as $item) {
            $user = $this->doctrine->getRepository(User::class)->findOneBy(['old_id' => $item->idVisiteur]);
            $mois = DateTime::createFromFormat('Ym', $item->mois);

            if ($user && $mois) {
                $ficheFrais = $this->doctrine->getRepository(FicheFrais::class)->findOneBy([
                    'user' => $user,
                    'mois' => $mois
                ]);

                if ($ficheFrais) {
                    $ligneFrais = new LigneFraisForfait();

                    $ligneFrais->setFichesFrais($ficheFrais);

                    switch ($item->idFraisForfait) {
                        case 'ETP':
                            $ligneFrais->setFraisForfaits($this->doctrine->getRepository(FraisForfait::class)->findOneBy(['id' => 1]));
                            break;

                        case 'KM':
                            $ligneFrais->setFraisForfaits($this->doctrine->getRepository(FraisForfait::class)->findOneBy(['id' => 2]));
                            break;

                        case 'NUI':
                            $ligneFrais->setFraisForfaits($this->doctrine->getRepository(FraisForfait::class)->findOneBy(['id' => 3]));
                            break;

                        case 'REP':
                            $ligneFrais->setFraisForfaits($this->doctrine->getRepository(FraisForfait::class)->findOneBy(['id' => 4]));
                            break;
                    }

                    $ligneFrais->setQuantite($item->quantite);

                    $entityManager->persist($ligneFrais);
                } else {
                    throw new Exception('FicheFrais not found for idVisiteur: ' . $item->idVisiteur . ' and mois: ' . $item->mois);
                }
            } else {
                throw new Exception('User not found for idVisiteur: ' . $item->idVisiteur . ' or invalid mois format: ' . $item->mois);
            }
        }

        $entityManager->flush();

        return $this->render('import_data/index.html.twig', [
            'controller_name' => 'ImportDataController',
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/import-data-lfhf', name: 'app_import_data_lfhf')]
    public function lireLigneFraisHorsForfaitJson(EntityManagerInterface $entityManager): JsonResponse
    {
        $chemin = $this->getParameter('kernel.project_dir') . '/public/lignefraishorsforfait.json';

        if (!file_exists($chemin)) {
            return new JsonResponse(['error' => 'Fichier non trouvé'], 404);
        }

        $contenuJson = file_get_contents($chemin);
        $ligneFraisHorsForfaitData = json_decode($contenuJson);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Fichier JSON invalide: ' . json_last_error_msg()], 500);
        }

        foreach ($ligneFraisHorsForfaitData as $ligneFraisHorsForfait) {
            $ligneFraisHorsForfaitVisiteur = new LigneFraisHorsForfait();

            $ligneFraisHorsForfaitVisiteur->setLibelle($ligneFraisHorsForfait->libelle);
            $ligneFraisHorsForfaitVisiteur->setDate(new DateTime($ligneFraisHorsForfait->date));
            $ligneFraisHorsForfaitVisiteur->setMontant($ligneFraisHorsForfait->montant);

            // Parse the 'mois' field
            $mois = DateTime::createFromFormat('Ym', $ligneFraisHorsForfait->mois);
            if ($mois === false) {
                error_log("Invalid 'mois' format: " . $ligneFraisHorsForfait->mois);
                continue;
            }

            $user = $entityManager->getRepository(User::class)->findOneBy(['old_id' => $ligneFraisHorsForfait->idVisiteur]);
            if (!$user) {
                error_log("User not found for idVisiteur: " . $ligneFraisHorsForfait->idVisiteur);
                continue;
            }

            $ficheFrais = $entityManager->getRepository(FicheFrais::class)->findOneBy([
                'mois' => $mois,
                'user' => $user
            ]);

            if (!$ficheFrais) {
                error_log("FicheFrais not found for mois: " . $mois->format('Y-m') . " and user id: " . $user->getId());
                continue;
            }

            $ligneFraisHorsForfaitVisiteur->setFichesFrais($ficheFrais);

            $entityManager->persist($ligneFraisHorsForfaitVisiteur);
        }

        $entityManager->flush();

        return new JsonResponse(['success' => 'Lignes de frais hors forfait importées avec succès'], 200);
    }

    /**
     * @throws Exception
     */
    #[Route('/jaioublielemdp', name: 'app_patch_hash_passwords')]
    public function hashUserPasswords(Request $request): Response
    {
        $userRepository = $this->doctrine->getRepository(User::class);
        $users = $userRepository->findAll();

        $entityManager = $this->doctrine->getManager();

        foreach ($users as $user) {
            if (!$this->passwordHasher->isPasswordValid($user, $user->getPassword())) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
                $user->setPassword($hashedPassword);

                $entityManager->persist($user);
            }
        }

        $entityManager->flush();

        return new Response('Les mots de passe des utilisateurs ont été hachés avec succès.', 200);
    }

    // Patch to set all the month to the first of each one
    #[Route('/set-month-first', name: 'app_set_month_first')]
    public function setMonthFirst(ManagerRegistry $registry): Response
    {
        $entityManager = $registry->getManager();
        $ficheFrais = $entityManager->getRepository(FicheFrais::class)->findAll();

        foreach ($ficheFrais as $fiche) {
            $fiche->setMois(new DateTime($fiche->getMois()->format('Y-m') . '-01'));
            $entityManager->persist($fiche);
        }
        $entityManager->flush();

        return new Response('Les mois ont été mis à jour avec succès.', 200);
    }
}