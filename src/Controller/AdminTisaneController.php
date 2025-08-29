<?php

namespace App\Controller;

use App\Entity\Tisane;
use App\Form\TisaneType;
use App\Repository\TisaneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Bundle\SecurityBundle\Attribute\IsGranted;

#[Route('/admin/tisanes')]
#[IsGranted('ROLE_ADMIN')]
class AdminTisaneController extends AbstractController
{
    #[Route('/', name: 'admin_tisane_index', methods: ['GET'])]
    public function index(TisaneRepository $tisaneRepository): Response
    {
        return $this->render('admin_dashboard/tisane/index.html.twig', [
            'tisanes' => $tisaneRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_tisane_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $tisane = new Tisane();
        $form = $this->createForm(TisaneType::class, $tisane);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $mime = $imageFile->getMimeType();
                $map  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                $ext  = $map[$mime] ?? $imageFile->guessExtension() ?? 'bin';

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$ext;

                try {
                    $imageFile->move($this->getParameter('tisanes_directory'), $newFilename);
                    $tisane->setImage($newFilename);
                } catch (\Throwable $e) {
                    // feedback clair dans le formulaire
                    $form->get('imageFile')->addError(new \Symfony\Component\Form\FormError(
                        "Impossible d'enregistrer l'image. Vérifiez les droits d'écriture du dossier."
                    ));
                    // on n'interrompt pas : laisse l’utilisateur corriger
                }
            }



            $entityManager->persist($tisane);
            $entityManager->flush();

            $this->addFlash('success', 'Tisane créée avec succès');

            return $this->redirectToRoute('admin_tisane_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_dashboard/tisane/new.html.twig', [
            'tisane' => $tisane,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_tisane_show', methods: ['GET'])]
    public function show(Tisane $tisane): Response
    {
        return $this->render('admin_dashboard/tisane/show.html.twig', [
            'tisane' => $tisane,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_tisane_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tisane $tisane, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(TisaneType::class, $tisane);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                // Supprimer l'ancienne image si elle existe
                if ($tisane->getImage()) {
                    $oldImage = $this->getParameter('tisanes_directory').'/'.$tisane->getImage();
                    if (file_exists($oldImage)) {
                        unlink($oldImage);
                    }
                }

                $mime = $imageFile->getMimeType();
                $map  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                $ext  = $map[$mime] ?? $imageFile->guessExtension() ?? 'bin';

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$ext;

                try {
                    $imageFile->move($this->getParameter('tisanes_directory'), $newFilename);
                    $tisane->setImage($newFilename);
                } catch (\Throwable $e) {
                    // feedback clair dans le formulaire
                    $form->get('imageFile')->addError(new \Symfony\Component\Form\FormError(
                        "Impossible d'enregistrer l'image. Vérifiez les droits d'écriture du dossier."
                    ));
                    // on n'interrompt pas : laisse l’utilisateur corriger
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Tisane modifiée avec succès');

            return $this->redirectToRoute('admin_tisane_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_dashboard/tisane/edit.html.twig', [
            'tisane' => $tisane,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_tisane_delete', methods: ['POST'])]
    public function delete(Request $request, Tisane $tisane, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tisane->getId(), $request->request->get('_token'))) {
            // Supprimer l'image associée
            if ($tisane->getImage()) {
                $imagePath = $this->getParameter('tisanes_directory').'/'.$tisane->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $entityManager->remove($tisane);
            $entityManager->flush();

            $this->addFlash('success', 'Tisane supprimée avec succès');
        }

        return $this->redirectToRoute('admin_tisane_index', [], Response::HTTP_SEE_OTHER);
    }
}