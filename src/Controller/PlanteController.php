<?php
// src/Controller/PlanteController.php

namespace App\Controller;

use App\Entity\Plante;
use App\Repository\PlanteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/plantes')]
class PlanteController extends AbstractController
{
    #[Route('/', name: 'plante_index')]
    public function index(
        PlanteRepository $planteRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $query = $planteRepository->createQueryBuilder('p')
            ->orderBy('p.nomCommun', 'ASC');

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('plante/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/{id}', name: 'plante_show', requirements: ['id' => '\d+'])]
    public function show(Plante $plante,PlanteRepository $planteRepository): Response
    {
        $allPlantes = $planteRepository->createQueryBuilder('p')
            ->orderBy('p.nomCommun', 'ASC')
            ->getQuery()
            ->getResult();

        $currentIndex = array_search($plante, $allPlantes);
        $previousPlante = $currentIndex > 0 ? $allPlantes[$currentIndex - 1] : null;
        $nextPlante = $currentIndex < count($allPlantes) - 1 ? $allPlantes[$currentIndex + 1] : null;

        return $this->render('plante/show.html.twig', [
            'plante' => $plante,
            'previousPlante' => $previousPlante,
            'nextPlante' => $nextPlante,
        ]);
    }

    #[Route('/recherche', name: 'plante_search')]
    public function search(
        Request $request,
        PlanteRepository $planteRepository,
        PaginatorInterface $paginator
    ): Response {
        $queryText = $request->query->get('q', '');

        $query = $planteRepository->createQueryBuilder('p')
            ->where('p.nomCommun LIKE :query')
            ->orWhere('p.nomScientifique LIKE :query')
            ->orWhere('p.description LIKE :query')
            ->setParameter('query', '%' . $queryText . '%');

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('plante/search.html.twig', [
            'pagination' => $pagination,
            'query' => $queryText,
        ]);
    }
}
