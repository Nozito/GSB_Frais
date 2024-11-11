<?php

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

    public function findDistinctMoisByUser($user)
    {
        return $this->createQueryBuilder('f')
            ->select('DISTINCT f.mois')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->orderBy('f.mois', 'ASC')
            ->getQuery()
            ->getResult();
    }

}