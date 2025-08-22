<?php

namespace App\Repository;

use App\Entity\Ingredient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ingredient>
 *
 * @method Ingredient|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ingredient|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ingredient[]    findAll()
 * @method Ingredient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IngredientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ingredient::class);
    }

    public function findDistinctTypes(): array
    {
        $rows = $this->createQueryBuilder('i')
            ->select('DISTINCT i.type AS type')
            ->andWhere('i.type IS NOT NULL')
            ->andWhere('i.type <> :empty')->setParameter('empty', '')
            ->orderBy('i.type', 'ASC')
            ->getQuery()->getScalarResult();

        return array_map(fn($r) => trim((string)$r['type']), $rows);
    }
    
}