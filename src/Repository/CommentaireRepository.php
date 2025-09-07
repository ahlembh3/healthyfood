<?php

namespace App\Repository;

use App\Entity\Commentaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommentaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commentaire::class);
    }

    /**
     * Moyenne des notes par recette (type=1), uniquement quand la note n'est pas nulle.
     * Retourne un tableau de lignes: ['recette_id' => int, 'moyenne' => float]
     */
    public function getMoyenneNoteParRecette(): array
    {
        return $this->createQueryBuilder('c')
            ->select('IDENTITY(c.recette) AS recette_id, AVG(c.note) AS moyenne')
            ->where('c.type = 1')                 // on limite aux commentaires de recette
            ->andWhere('c.recette IS NOT NULL')   // sécurité
            ->andWhere('c.note IS NOT NULL')
            ->groupBy('c.recette')
            ->getQuery()
            ->getResult();
    }

    public function findLastNotFlagged(): ?Commentaire
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.signaler = :s')
            ->setParameter('s', false)
            ->orderBy('c.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
