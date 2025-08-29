<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use App\Repository\RecetteRepository;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Attribute\IsGranted;

#[Route('/utilisateurs', name: 'utilisateurs_')]
final class UtilisateurController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UtilisateurRepository $userRepo,
        private readonly RecetteRepository $recetteRepo,
        private readonly ArticleRepository $articleRepo,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}



    // --- ADMIN ---
    #[Route('', name: 'liste', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function liste(): Response
    {
        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $this->userRepo->findAll(),
        ]);
    }

    #[Route('/ajouter', name: 'ajouter', methods: ['GET','POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function ajouter(Request $request): Response
    {
        $utilisateur = new Utilisateur();
        $utilisateur->setRoles(['ROLE_USER']);

        $form = $this->createForm(UtilisateurType::class, $utilisateur, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('password')->getData();
            $utilisateur->setPassword($this->passwordHasher->hashPassword($utilisateur, $plain));

            $this->em->persist($utilisateur);
            $this->em->flush();

            $this->addFlash('success', 'Utilisateur créé.');
            return $this->redirectToRoute('utilisateurs_liste');
        }

        return $this->render('utilisateur/new.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'afficher', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function afficher(Utilisateur $utilisateur): Response
    {
        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/{id<\d+>}/modifier', name: 'modifier', methods: ['GET','POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function modifier(Request $request, Utilisateur $utilisateur): Response
    {
        $form = $this->createForm(UtilisateurType::class, $utilisateur, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Utilisateur modifié.');
            return $this->redirectToRoute('utilisateurs_liste');
        }

        return $this->render('utilisateur/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'supprimer', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function supprimer(Request $request, Utilisateur $utilisateur): Response
    {
        // Empêcher la suppression de soi-même
        if ($utilisateur === $this->getUser()) {
            $this->addFlash('danger', "Vous ne pouvez pas supprimer votre propre compte.");
            return $this->redirectToRoute('utilisateurs_liste');
        }

        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->getPayload()->getString('_token'))) {
            // (Optionnel) empêcher de supprimer le dernier admin
            // if (in_array('ROLE_ADMIN', $utilisateur->getRoles(), true) && $this->userRepo->countAdmins() <= 1) { ... }

            $this->em->remove($utilisateur);
            $this->em->flush();
            $this->addFlash('success', 'Utilisateur supprimé.');
        } else {
            $this->addFlash('danger', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('utilisateurs_liste');
    }


    // --- ESPACE UTILISATEUR ---
    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function dashboard(): Response
    {
        $user = $this->getUser();
        $mesRecettes = $this->recetteRepo->findBy(['utilisateur' => $user]);
        $mesArticles = $this->articleRepo->findBy(['utilisateur' => $user]);

        return $this->render('utilisateur/dashboard.html.twig', [
            'utilisateur' => $user,
            'mesRecettes' => $mesRecettes,
            'mesArticles' => $mesArticles,
        ]);
    }

    #[Route('/mon-profil', name: 'mon_profil', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function monProfil(): Response
    {
        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $this->getUser(),
        ]);
    }

    #[Route('/mon-profil/modifier', name: 'modifier_mon_profil', methods: ['GET','POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function modifierMonProfil(Request $request): Response
    {
        $utilisateur = $this->getUser();

        $form = $this->createForm(UtilisateurType::class, $utilisateur, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Votre profil a bien été mis à jour.');
            return $this->redirectToRoute('utilisateurs_mon_profil');
        }

        return $this->render('utilisateur/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }
}
