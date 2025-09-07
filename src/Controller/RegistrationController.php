<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_inscription', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('utilisateurs_dashboard');
        }

        $user = new Utilisateur();
        $user->setRoles(['ROLE_USER']);

        $form = $this->createForm(UtilisateurType::class, $user, [
            'is_edit' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = (string) $form->get('password')->getData();
            $user->setPassword($hasher->hashPassword($user, $plain));

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Compte créé. Vous pouvez vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        // Si le formulaire est soumis mais invalide, le template affichera automatiquement les erreurs
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Veuillez corriger les erreurs ci-dessous.');
        }
        // 422 si invalide pour que Turbo mette à jour la page
        $status = ($form->isSubmitted() && !$form->isValid()) ? 422 : 200;

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ], new Response('', $status));
    }
}
