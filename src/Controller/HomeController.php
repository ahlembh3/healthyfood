<?php
namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\PlanteRepository;
use App\Repository\RecetteRepository;
use App\Repository\TisaneRepository;
use App\Repository\CommentaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
#[Route('/', name: 'app_home', methods: ['GET'])]
public function index(
RecetteRepository $recettes,
TisaneRepository $tisanes,
PlanteRepository $plantes,
ArticleRepository $articles,
CommentaireRepository $commentaires
): Response {


$latestTisanes = $tisanes->createQueryBuilder('t')
->orderBy('t.id','DESC')->setMaxResults(3)->getQuery()->getResult();

$latestPlantes = $plantes->createQueryBuilder('p')
->orderBy('p.id','DESC')->setMaxResults(10)->getQuery()->getResult(); // on en passe plusieurs pour la rotation





return $this->render('home/index.html.twig', [
    'latestRecettes' => $recettes->findLatestValidated(3),
    'latestTisanes'  => $latestTisanes,
    'latestPlantes'  => $latestPlantes,
    'latestArticles' => $articles->findLatestValidated(2),
    'lastComment'    => $commentaires->findLastNotFlagged(),

]);
}
}
