<?php

namespace App\Repository;

use App\Entity\Tisane;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tisane>
 *
 * @method Tisane|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tisane|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tisane[]    findAll()
 * @method Tisane[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TisaneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tisane::class);
    }

    // Ajoute ici tes méthodes personnalisées si besoin
}