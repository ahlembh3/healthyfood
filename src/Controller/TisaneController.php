<?php

namespace App\Controller;

use App\Entity\Tisane;
use App\Entity\Plante;
use App\Entity\Bienfait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tisanes')]
class TisaneController extends AbstractController
{
    #[Route('', name: 'tisane_index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $query = $request->query->get('q');

        $tisanes = $em->getRepository(Tisane::class)
            ->createQueryBuilder('t')
            ->leftJoin('t.bienfaits', 'b')
            ->leftJoin('t.plantes', 'p')
            ->addSelect('b', 'p')
            ->where('t.nom LIKE :query OR b.nom LIKE :query OR p.nomCommun LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('t.nom', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('tisane/index.html.twig', [
            'tisanes' => $tisanes,
            'query' => $query,
        ]);
    }
#[Route('/{id}', name: 'tisane_show', requirements: ['id' => '\d+'])]
public function show(int $id, EntityManagerInterface $em): Response
    {
    $tisane = $em->getRepository(Tisane::class)->find($id);

    if (!$tisane) {
        throw $this->createNotFoundException("Tisane non trouvée.");
    }

    return $this->render('tisane/show.html.twig', [
        'tisane' => $tisane,
    ]);
    }
    #[Route('/test-tisane', name: 'app_test_tisane')]
public function test(EntityManagerInterface $em): Response
{
    $tisane = new Tisane();
    $tisane->setNom('Tisane test');
    $tisane->setModePreparation('Infuser 10 minutes');

    // Suppose qu’on a déjà des Plantes et Bienfaits en BDD
    $plante = $em->getRepository(Plante::class)->find(1);
    $bienfait = $em->getRepository(Bienfait::class)->find(1);

    if ($plante) $tisane->addPlante($plante);
    if ($bienfait) $tisane->addBienfait($bienfait);

    $em->persist($tisane);
    $em->flush();

    return new Response('Tisane test enregistrée avec une plantes et un bienfait.');
}


}
