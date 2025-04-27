<?php

namespace App\Controller;

use App\Entity\FicheFrais;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class Top3Controller extends AbstractController
{
    #[Route('/top3', name: 'app_top3')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        //Get the month of 2024
        $moislist = [
            '01' => 'Janvier',
            '02' => 'Février',
            '03' => 'Mars',
            '04' => 'Avril',
            '05' => 'Mai',
            '06' => 'Juin',
            '07' => 'Juillet',
            '08' => 'Août',
            '09' => 'Septembre',
            '10' => 'Octobre',
            '11' => 'Novembre',
            '12' => 'Décembre',
        ];

        $selectedMonth = $request->request->get('mois');
        //$selectedMonth = $request->request->get('mois');
        //$selectedMonth = '2024' . $request->request->get('mois');
        if($selectedMonth){
            //create datetime object corresponding to the selected month in 2024
            $selectedMonth = new DateTime('2024-' . $selectedMonth . '-01');
            $ficheFrais = $doctrine->getRepository(FicheFrais::class)->findBy(['mois' => $selectedMonth]);
            usort($ficheFrais, function($a, $b) {
                return $a->getMontantValid() < $b->getMontantValid();
            });

            $ficheFrais = array_slice($ficheFrais, 0, 3);
        }

        return $this->render('top3/index.html.twig', [
            'controller_name' => 'Top3Controller',
            'moislist' => $moislist,
            'ficheFrais' => $ficheFrais ?? [],
        ]);
    }
}
