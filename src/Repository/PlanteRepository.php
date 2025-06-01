<?php

namespace App\Repository;

use App\Entity\Plante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Plante>
 *
 * @method Plante|null find($id, $lockMode = null, $lockVersion = null)
 * @method Plante|null findOneBy(array $criteria, array $orderBy = null)
 * @method Plante[]    findAll()
 * @method Plante[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plante::class);
    }

    // Ajoute ici tes méthodes personnalisées si besoin
}