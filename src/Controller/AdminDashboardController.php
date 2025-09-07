<?php

namespace App\Controller;

use App\Repository\CommentaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    public function index(CommentaireRepository $commentaireRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Compter les commentaires signalés
        $nbSignalements = $commentaireRepository->count(['signaler' => true]);

        return $this->render('admin_dashboard/index.html.twig', [
            'user'           => $this->getUser(),
            'nbSignalements' => $nbSignalements,
        ])->setStatusCode(Response::HTTP_OK); // 200 explicite (optionnel)
    }
}
