<?php
// src/Controller/PlanteController.php

namespace App\Controller;

use App\Entity\Plante;
use App\Repository\PlanteRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/plantes')]
class PlanteController extends AbstractController
{
    #[Route('/', name: 'plante_index', methods: ['GET'])]
    public function index(
        PlanteRepository $planteRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $q = trim((string) $request->query->get('q', ''));
        $page = $request->query->getInt('page', 1);

        $qb = $planteRepository->createQueryBuilder('p');

        if ($q !== '') {
            // recherche simple insensible à la casse (nom commun / scientifique / description)
            $qb->andWhere('LOWER(p.nomCommun) LIKE :q OR LOWER(p.nomScientifique) LIKE :q OR LOWER(p.description) LIKE :q')
                ->setParameter('q', '%'.mb_strtolower($q).'%');
        }

        $qb->orderBy('p.nomCommun', 'ASC');

        $pagination = $paginator->paginate(
            $qb,   // QueryBuilder accepté par KnpPaginator
            $page,
            6
        );

        return $this->render('plante/index.html.twig', [
            'pagination' => $pagination,
            // pas nécessaire mais utile si tu veux pré-remplir le champ recherche
            'q'          => $q,
        ]);
    }

    #[Route('/{id}', name: 'plante_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Plante $plante, PlanteRepository $planteRepository): Response
    {
        // Liste triée pour calculer précédent/suivant
        $allPlantes = $planteRepository->createQueryBuilder('p')
            ->orderBy('p.nomCommun', 'ASC')
            ->getQuery()
            ->getResult();

        // On travaille par ID (plus fiable que comparer des objets/proxies)
        $ids = array_map(static fn(Plante $p) => $p->getId(), $allPlantes);
        $currentIndex = array_search($plante->getId(), $ids, true);

        $previousPlante = null;
        $nextPlante = null;

        if ($currentIndex !== false) {
            if ($currentIndex > 0) {
                $previousPlante = $allPlantes[$currentIndex - 1] ?? null;
            }
            if ($currentIndex < count($allPlantes) - 1) {
                $nextPlante = $allPlantes[$currentIndex + 1] ?? null;
            }
        }

        return $this->render('plante/show.html.twig', [
            'plante'          => $plante,
            'previousPlante'  => $previousPlante,
            'nextPlante'      => $nextPlante,
        ]);
    }

    #[Route('/recherche', name: 'plante_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        // Canonicalise vers l’index avec le paramètre q
        $q = $request->query->get('q', '');
        return $this->redirectToRoute('plante_index', ['q' => $q]);
    }
}
