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
// 3 dernières recettes/tisanes/plantes (adapte les critères si besoin: validation=true, etc.)
$latestRecettes = $recettes->createQueryBuilder('r')
->orderBy('r.id','DESC')->setMaxResults(3)->getQuery()->getResult();

$latestTisanes = $tisanes->createQueryBuilder('t')
->orderBy('t.id','DESC')->setMaxResults(3)->getQuery()->getResult();

$latestPlantes = $plantes->createQueryBuilder('p')
->orderBy('p.id','DESC')->setMaxResults(10)->getQuery()->getResult(); // on en passe plusieurs pour la rotation

// 2 derniers articles par date de création
$latestArticles = $articles->createQueryBuilder('a')
->orderBy('a.date', 'DESC')      // change en a.createdAt si ton champ s’appelle ainsi
->setMaxResults(2)
->getQuery()->getResult();

// dernier commentaire (tous types confondus).
$lastComment = $commentaires->createQueryBuilder('c')
->orderBy('c.date','DESC')
->setMaxResults(1)
->getQuery()->getOneOrNullResult();

return $this->render('home/index.html.twig', [
'latestRecettes' => $latestRecettes,
'latestTisanes'  => $latestTisanes,
'latestPlantes'  => $latestPlantes,
'latestArticles' => $latestArticles,
'lastComment'    => $lastComment,
]);
}
}
