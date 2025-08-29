<?php

namespace App\Controller;

use App\Entity\Tisane;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tisanes')]
class TisaneController extends AbstractController
{
    #[Route('', name: 'tisane_index', methods: ['GET'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        PaginatorInterface $paginator
    ): Response {
        $q    = trim((string) $request->query->get('q', ''));
        $page = max(1, (int) $request->query->get('page', 1));

        // IMPORTANT :
        // - pas de groupBy sur t.id
        // - DISTINCT t pour éviter les doublons dus aux joins
        // - on ne addSelect pas deux collections to-many à la fois
        $qb = $em->getRepository(Tisane::class)
            ->createQueryBuilder('t')
            ->select('DISTINCT t')
            ->leftJoin('t.bienfaits', 'b') // utile pour filtrer sur b
            ->leftJoin('t.plantes',  'p') // utile pour filtrer sur p
            ->orderBy('t.nom', 'ASC');

        if ($q !== '') {
            $qb->andWhere(
                't.nom LIKE :q
                 OR t.modePreparation LIKE :q
                 OR b.nom LIKE :q
                 OR p.nomCommun LIKE :q'
            )->setParameter('q', '%'.$q.'%');
        }

        $tisanes = $paginator->paginate(
            $qb,
            $page,
            9,
            ['wrap-queries' => true]
        );

        return $this->render('tisane/index.html.twig', [
            'tisanes' => $tisanes,
            'query'   => $q,
        ]);
    }

    #[Route('/{id}', name: 'tisane_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        // Ici on peut précharger les deux relations (on ne récupère qu'UNE tisane)
        $tisane = $em->getRepository(Tisane::class)->createQueryBuilder('t')
            ->select('t, p, b')
            ->leftJoin('t.plantes', 'p')
            ->leftJoin('t.bienfaits', 'b')
            ->andWhere('t.id = :id')->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$tisane) {
            throw $this->createNotFoundException("Tisane non trouvée.");
        }

        return $this->render('tisane/show.html.twig', [
            'tisane' => $tisane,
        ]);
    }
}
