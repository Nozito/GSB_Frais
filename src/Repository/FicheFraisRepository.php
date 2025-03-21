<?php
namespace App\Repository;

use App\Entity\FicheFrais;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FicheFraisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FicheFrais::class);
    }

    public function findByUserMois(User $user, string $mois): ?FicheFrais
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.mois = :mois')
            ->andWhere('f.user = :user')
            ->setParameter('mois', $mois)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findTop3(string $mois): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.mois = :mois')
            ->setParameter('mois', $mois)
            ->orderBy('f.montantValide', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult()
        ;
    }
}