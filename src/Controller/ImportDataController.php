<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\FicheFrais;
use App\Entity\FraisForfait;
use App\Entity\LigneFraisForfait;
use App\Entity\LigneFraisHorsForfait;
use App\Entity\User;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
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
    public function importLigneFraisHorsForfait(): Response
    {
        $path = $this->getParameter('kernel.project_dir') . '/public/lignefraishorsforfait.json';
        $jsonData = file_get_contents($path);
        $data = json_decode($jsonData);

        if ($data === null) {
            throw new Exception('Failed to decode JSON data.');
        }

        $entityManager = $this->doctrine->getManager();

        foreach ($data as $item) {
            $user = $this->doctrine->getRepository(User::class)->findOneBy(['old_id' => $item->idVisiteur]);

            if ($user) {
                $mois = $item->mois;
                if (empty($mois)) {
                    throw new Exception('Mois field is missing or empty in JSON data.');
                }

                $dateMois = DateTime::createFromFormat('Ym', $mois);
                if ($dateMois === false) {
                    throw new Exception('Invalid mois format: ' . $mois);
                }

                $ficheFrais = $this->doctrine->getRepository(FicheFrais::class)->findOneBy([
                    'user' => $user,
                    'mois' => $dateMois
                ]);

                if ($ficheFrais) {
                    $ligneFrais = new LigneFraisHorsForfait();

                    $ligneFrais->setFichesFrais($ficheFrais);
                    $ligneFrais->setOldId($item->id);
                    $ligneFrais->setMois($dateMois);
                    $ligneFrais->setLibelle($item->libelle);
                    $ligneFrais->setDate(new DateTime($item->date));
                    $ligneFrais->setMontant($item->montant);

                    $entityManager->persist($ligneFrais);
                } else {
                    throw new Exception('FicheFrais not found for idVisiteur: ' . $item->idVisiteur . ' and mois: ' . $item->mois);
                }
            } else {
                throw new Exception('User not found for idVisiteur: ' . $item->idVisiteur);
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
}