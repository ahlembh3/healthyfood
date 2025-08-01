<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response; 
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\FormError;
use App\Repository\RecetteRepository;
use App\Repository\CommentaireRepository;

#[Route('/utilisateurs')]
final class UtilisateurController extends AbstractController
{
    #[Route('', name: 'utilisateurs_liste', methods: ['GET'])]
    public function liste(UtilisateurRepository $utilisateurRepository): Response
    {
        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll(),
        ]);
    }
    #[Route('/dashboard', name: 'utilisateurs_dashboard')]
    public function dashboard(
        RecetteRepository $recetteRepo,
        CommentaireRepository $commentaireRepo
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à votre espace.');
        }

        $mesRecettes = $recetteRepo->findBy(['utilisateur' => $user]);
        $mesCommentaires = $commentaireRepo->findBy(['utilisateur' => $user]);

        return $this->render('utilisateur/dashboard.html.twig', [
            'utilisateur' => $user,
            'mesRecettes' => $mesRecettes,
            'mesCommentaires' => $mesCommentaires,
        ]);
    }



   #[Route('/inscription', name: 'utilisateurs_ajouter', methods: ['GET', 'POST'])]
public function ajouter(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
{
    $utilisateur = new Utilisateur();
    $utilisateur->setRoles(['ROLE_USER']);

    // Passe l'option personnalisée is_edit à false
    $form = $this->createForm(UtilisateurType::class, $utilisateur, [
        'is_edit' => false,
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $plainPassword = $form->get('password')->getData();
        $confirmPassword = $form->get('confirmPassword')->getData();

        if ($plainPassword !== $confirmPassword) {
            $form->get('confirmPassword')->addError(new FormError('Les mots de passe ne correspondent pas.'));
        } else {
            $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
            $utilisateur->setPassword($hashedPassword);

            $entityManager->persist($utilisateur);
            $entityManager->flush();

            return $this->redirectToRoute('utilisateurs_liste');
        }
    }

    return $this->render('utilisateur/new.html.twig', [
        'utilisateur' => $utilisateur,
        'form' => $form,
    ]);
}


    #[Route('/{id}', name: 'utilisateurs_afficher', methods: ['GET'])]
    public function afficher(Utilisateur $utilisateur): Response
    {
        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/{id}/modifier', name: 'utilisateurs_modifier', methods: ['GET', 'POST'])]
    public function modifier(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UtilisateurType::class, $utilisateur,['is_edit' => true,]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('utilisateurs_liste');
        }

        return $this->render('utilisateur/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'utilisateurs_supprimer', methods: ['POST'])]
    public function supprimer(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($utilisateur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('utilisateurs_liste');
    }
        
}
