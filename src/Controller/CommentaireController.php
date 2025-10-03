<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commentaires')]
class CommentaireController extends AbstractController
{
    #[Route('/', name: 'commentaire_index', methods: ['GET'])]
    public function index(
        Request $request,
        CommentaireRepository $commentaireRepository,
        PaginatorInterface $paginator
    ): Response {
        $signale = $request->query->get('signale'); // "1" pour signalés

        $qb = $commentaireRepository->createQueryBuilder('c')
            ->orderBy('c.signaler', 'DESC')
            ->addOrderBy('c.date', 'DESC');

        // Filtre "signalés uniquement" réservé aux admins
        if ($signale && $this->isGranted('ROLE_ADMIN')) {
            $qb->andWhere('c.signaler = :true')->setParameter('true', true);
        }

        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('commentaire/index.html.twig', [
            'commentaires' => $pagination,
        ]);
    }

    #[Route('/voir/{id}', name: 'commentaire_show', methods: ['GET'])]
    public function show(?Commentaire $commentaire = null): Response
    {
        if (!$commentaire) {
            throw $this->createNotFoundException('Commentaire introuvable.');
        }

        return $this->render('commentaire/show.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('/supprimer/{id}', name: 'commentaire_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $commentaire->getId(), $request->request->get('_token'))) {
            $em->remove($commentaire);
            $em->flush();
            $this->addFlash('success', 'Commentaire supprimé.');
        }

        return $this->redirectToRoute('commentaire_index');
    }

    #[Route('/signaler/{id}', name: 'commentaire_signaler', methods: ['POST'])]
    public function signaler(Request $request, Commentaire $commentaire, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($this->isCsrfTokenValid('signaler' . $commentaire->getId(), $request->request->get('_token'))) {
            $utilisateur = $this->getUser();

            if ($commentaire->getUtilisateur() === $utilisateur) {
                $this->addFlash('danger', 'Vous ne pouvez pas signaler votre propre commentaire.');
            } elseif ($commentaire->isSignaler() && $commentaire->getSignalePar() === $utilisateur) {
                $this->addFlash('warning', 'Vous avez déjà signalé ce commentaire.');
            } else {
                $commentaire->setSignaler(true);
                $commentaire->setSignalePar($utilisateur);
                $commentaire->setSignaleLe(new \DateTimeImmutable());
                $em->flush();
                $this->addFlash('success', 'Le commentaire a été signalé.');
            }
        }

        // Retour vers la ressource liée
        if ($commentaire->getRecette()) {
            return $this->redirectToRoute('recette_show', ['id' => $commentaire->getRecette()->getId()]);
        }
        if ($commentaire->getArticle()) {
            return $this->redirectToRoute('article_show', ['id' => $commentaire->getArticle()->getId()]);
        }
        return $this->redirectToRoute('app_home');
    }

    #[Route('/signales', name: 'commentaire_signales', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function commentairesSignales(CommentaireRepository $commentaireRepository): Response
    {
        $commentaires = $commentaireRepository->findBy(
            ['signaler' => true],
            ['signaleLe' => 'DESC', 'date' => 'DESC']
        );

        return $this->render('commentaire/signales.html.twig', [
            'commentaires' => $commentaires,
        ]);
    }

    #[Route('/designaler/{id}', name: 'commentaire_designaler', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function designaler(Request $request, Commentaire $commentaire, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('designaler' . $commentaire->getId(), $request->request->get('_token'))) {
            $commentaire->setSignaler(false);
            $commentaire->setSignalePar(null);
            $commentaire->setSignaleLe(null);

            $em->flush();
            $this->addFlash('success', 'Le commentaire a été désignalé.');
        }

        return $this->redirectToRoute('commentaire_signales');
    }
}
