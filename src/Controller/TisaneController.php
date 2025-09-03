<?php

namespace App\Controller;

use App\Entity\Tisane;
use App\Repository\TisaneRepository;
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
        TisaneRepository $tisaneRepository,
        PaginatorInterface $paginator
    ): Response {
        $q    = trim((string) $request->query->get('q', ''));
        $page = max(1, (int) $request->query->get('page', 1));

        if ($q !== '') {
            // Recherche fuzzy (tolérante aux fautes)
            $results = $tisaneRepository->fuzzySearch(
                $q,
                limitCandidates: 400,
                minScore: 18
            );

            // Pagination sur tableau (KNP gère très bien)
            $tisanes = $paginator->paginate($results, $page, 9);
        } else {
            // Recherche simple (LIKE) paginée en SQL
            $qb = $tisaneRepository->queryIndex('');
            $tisanes = $paginator->paginate(
                $qb,
                $page,
                9,
                ['wrap-queries' => true]
            );
        }

        return $this->render('tisane/index.html.twig', [
            'tisanes' => $tisanes,
            'query'   => $q,
        ]);
    }

    #[Route('/{id}', name: 'tisane_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): Response
    {
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
