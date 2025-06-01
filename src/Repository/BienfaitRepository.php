<?php

namespace App\Repository;

use App\Entity\Bienfait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bienfait>
 *
 * @method Bienfait|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bienfait|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bienfait[]    findAll()
 * @method Bienfait[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BienfaitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bienfait::class);
    }

    // Ajoute ici tes méthodes personnalisées si besoin
}