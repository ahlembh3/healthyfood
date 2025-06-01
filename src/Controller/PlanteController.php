<?php
// src/Controller/PlanteController.php

namespace App\Controller;

use App\Entity\Plante;
use App\Repository\PlanteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/plantes')]
class PlanteController extends AbstractController
{
    #[Route('/{id}', name: 'plante_show', requirements: ['id' => '\d+'])]
    public function show(Plante $plante): Response
    {
        return $this->render('plante/show.html.twig', [
            'plante' => $plante,
        ]);
    }

    #[Route('/recherche', name: 'plante_search')]
    public function search(Request $request, PlanteRepository $planteRepository): Response
    {
        $query = $request->query->get('q', '');
     $plantes = $planteRepository->createQueryBuilder('p')
    ->where('p.nomCommun LIKE :query')
    ->orWhere('p.nomScientifique LIKE :query')
    ->orWhere('p.description LIKE :query')
    ->setParameter('query', '%' . $query . '%')
    ->getQuery()
    ->getResult();


        return $this->render('plante/search.html.twig', [
            'plantes' => $plantes,
            'query' => $query,
        ]);
    }
}
