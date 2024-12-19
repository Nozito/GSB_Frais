<?php
// src/Repository/FicheFraisRepository.php

namespace App\Repository;

use App\Entity\FicheFrais;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FicheFraisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FicheFrais::class);
    }

    public function findTopVisitorsByMonth($mois)
    {
        $qb = $this->createQueryBuilder('f')
            ->select('f.user', 'SUM(f.montantValid) as totalFrais')
            ->where('f.mois = :mois')
            ->groupBy('f.user')
            ->orderBy('totalFrais', 'DESC')
            ->setMaxResults(3)
            ->setParameter('mois', $mois);

        return $qb->getQuery()->getResult();
    }
}