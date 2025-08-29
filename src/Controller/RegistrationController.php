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
        // Si déjà connecté, redirige où tu veux
        if ($this->getUser()) {
            return $this->redirectToRoute('utilisateurs_dashboard'); // ou 'app_home'
        }

        $user = new Utilisateur();
        $user->setRoles(['ROLE_USER']);

        $form = $this->createForm(UtilisateurType::class, $user, [
            'is_edit' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gère les 2 variantes possibles dans ton FormType :
            //  - password (simple)
            //  - plainPassword (RepeatedType)
            $plain = null;
            if ($form->has('password')) {
                $plain = (string) $form->get('password')->getData();
            } elseif ($form->has('plainPassword')) {
                // RepeatedType : on prend le 1er champ
                $plain = (string) $form->get('plainPassword')->getData();
            }

            if ($plain !== '') {
                $user->setPassword($hasher->hashPassword($user, $plain));
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Compte créé. Vous pouvez vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(), // ✅ passe la FormView à Twig
        ]);
    }
}
