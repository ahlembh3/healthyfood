<?php

namespace App\Controller;

use App\Entity\Plante;
use App\Form\PlanteType;
use App\Repository\PlanteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\SecurityBundle\Attribute\IsGranted;

#[Route('/admin/plantes')]
#[IsGranted('ROLE_ADMIN')]
class AdminPlanteController extends AbstractController
{
    #[Route('/', name: 'admin_plante_index', methods: ['GET'])]
    public function index(PlanteRepository $repo): Response
    {
        return $this->render('admin_dashboard/plante/index.html.twig', [
            'plantes' => $repo->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_plante_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $plante = new Plante();
        $form = $this->createForm(PlanteType::class, $plante);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Upload image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $map  = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
                $mime = $imageFile->getMimeType();
                $ext  = $map[$mime] ?? $imageFile->guessExtension() ?? 'bin';

                $original = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safe     = $slugger->slug($original);
                $newName  = $safe.'-'.uniqid().'.'.$ext;

                try {
                    $imageFile->move($this->getParameter('plantes_directory'), $newName);
                    $plante->setImage($newName);
                } catch (FileException $e) {
                    $form->get('imageFile')->addError(new FormError(
                        "Impossible d'enregistrer l'image. Vérifiez les droits d'écriture."
                    ));
                }
            }

            if ($form->isValid()) {
                $em->persist($plante);
                $em->flush();

                $this->addFlash('success', 'Plante créée avec succès');
                return $this->redirectToRoute('admin_plante_index');
            }
        }

        $status = ($form->isSubmitted() && !$form->isValid()) ? 422 : 200;

        return $this->render('admin_dashboard/plante/new.html.twig', [
            'plante' => $plante,
            'form'   => $form,
        ])->setStatusCode($status);
    }

    #[Route('/{id}', name: 'admin_plante_show', methods: ['GET'])]
    public function show(Plante $plante): Response
    {
        return $this->render('admin_dashboard/plante/show.html.twig', [
            'plante' => $plante,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_plante_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Plante $plante, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $oldImage = $plante->getImage();

        $form = $this->createForm(PlanteType::class, $plante);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                // supprimer l’ancienne
                if ($oldImage) {
                    $oldPath = $this->getParameter('plantes_directory').'/'.$oldImage;
                    if (is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                $map  = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
                $mime = $imageFile->getMimeType();
                $ext  = $map[$mime] ?? $imageFile->guessExtension() ?? 'bin';

                $original = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safe     = $slugger->slug($original);
                $newName  = $safe.'-'.uniqid().'.'.$ext;

                try {
                    $imageFile->move($this->getParameter('plantes_directory'), $newName);
                    $plante->setImage($newName);
                } catch (FileException $e) {
                    $form->get('imageFile')->addError(new FormError(
                        "Impossible d'enregistrer l'image. Vérifiez les droits d'écriture."
                    ));
                }
            } else {
                // conserver l’ancienne si pas de nouvelle
                $plante->setImage($oldImage);
            }

            if ($form->isValid()) {
                $em->flush();
                $this->addFlash('success', 'Plante modifiée avec succès');
                return $this->redirectToRoute('admin_plante_index');
            }
        }

        $status = ($form->isSubmitted() && !$form->isValid()) ? 422 : 200;

        return $this->render('admin_dashboard/plante/edit.html.twig', [
            'plante' => $plante,
            'form'   => $form,
        ])->setStatusCode($status);
    }

    #[Route('/{id}', name: 'admin_plante_delete', methods: ['POST'])]
    public function delete(Request $request, Plante $plante, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$plante->getId(), $request->request->get('_token'))) {
            if ($plante->getImage()) {
                $path = $this->getParameter('plantes_directory').'/'.$plante->getImage();
                if (is_file($path)) @unlink($path);
            }
            $em->remove($plante);
            $em->flush();
            $this->addFlash('success', 'Plante supprimée avec succès');
        }
        return $this->redirectToRoute('admin_plante_index');
    }
}
