<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Repository\CommentaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CommentaireType;

#[Route('/admin/commentaires')]
class CommentaireController extends AbstractController
{
    #[Route('/', name: 'commentaire_index', methods: ['GET'])]
    public function index(Request $request, CommentaireRepository $commentaireRepository): Response
{
    $signale = $request->query->get('signale');

    if ($signale) {
        $commentaires = $commentaireRepository->findBy(['signaler' => true], ['date' => 'DESC']);
    } else {
        $commentaires = $commentaireRepository->findBy([], ['signaler' => 'DESC', 'date' => 'DESC']);
    }

    return $this->render('commentaire/index.html.twig', [
        'commentaires' => $commentaires,
    ]);
}



    #[Route('/voir/{id}', name: 'commentaire_show', methods: ['GET'])]
    public function show(Commentaire $commentaire): Response
    {
           if (!$commentaire) {
        throw $this->createNotFoundException("Commentaire introuvable.");
    }
        return $this->render('commentaire/show.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('/supprimer/{id}', name: 'commentaire_delete', methods: ['POST'])]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $commentaire->getId(), $request->request->get('_token'))) {
            $em->remove($commentaire);
            $em->flush();
        }

        return $this->redirectToRoute('commentaire_index');
    }

   #[Route('/signaler/{id}', name: 'commentaire_signaler', methods: ['POST'])]
public function signaler(Request $request, Commentaire $commentaire, EntityManagerInterface $em): Response
{
    if ($this->isCsrfTokenValid('signaler' . $commentaire->getId(), $request->request->get('_token'))) {
        $utilisateur = $this->getUser();

        // Empêche de signaler son propre commentaire
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

    // Redirection conditionnelle selon le type de contenu
    if ($commentaire->getType() === 1 && $commentaire->getRecette()) {
        return $this->redirectToRoute('recette_show', ['id' => $commentaire->getRecette()->getId()]);
    } elseif ($commentaire->getType() === 2 && $commentaire->getArticle()) {
        return $this->redirectToRoute('article_show', ['id' => $commentaire->getArticle()->getId()]);
    }

    return $this->redirectToRoute('home');
}


    #[Route('/signales', name: 'commentaire_signales', methods: ['GET'])]
public function commentairesSignales(CommentaireRepository $commentaireRepository): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    $commentaires = $commentaireRepository->findBy(['signaler' => true]);

    return $this->render('commentaire/signales.html.twig', [
        'commentaires' => $commentaires,
    ]);
}

#[Route('/designaler/{id}', name: 'commentaire_designaler', methods: ['POST'])]
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
