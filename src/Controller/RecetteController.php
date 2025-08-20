<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Entity\RecetteIngredient;
use App\Form\RecetteForm;
use App\Form\CommentaireType;
use App\Repository\RecetteRepository;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\Commentaire;
use App\Service\TisaneSuggestionService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/recettes')]
final class RecetteController extends AbstractController
{
    #[Route('/', name: 'recette_index', methods: ['GET'])]
    public function index(
        RecetteRepository $recetteRepository,
        CommentaireRepository $commentaireRepository
    ): Response {
        $recettes = $recetteRepository->findBy(['validation' => true]);
        $moyennes = $commentaireRepository->getMoyenneNoteParRecette();

        $moyennesParRecette = [];
        foreach ($moyennes as $item) {
            $moyennesParRecette[$item['recette_id']] = round($item['moyenne'], 2);
        }

        return $this->render('recette/recettes_liste.html.twig', [
            'recettes' => $recettes,
            'moyennes' => $moyennesParRecette,
        ]);
    }

    #[Route('/admin/liste', name: 'recette_liste_admin', methods: ['GET'])]
    public function listeAdmin(RecetteRepository $recetteRepository, CommentaireRepository $commentaireRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $recettes = $recetteRepository->findAll();
        $moyennes = $commentaireRepository->getMoyenneNoteParRecette();

        $moyennesParRecette = [];
        foreach ($moyennes as $item) {
            $moyennesParRecette[$item['recette_id']] = round($item['moyenne'], 2);
        }

        return $this->render('recette/index.html.twig', [
            'recettes' => $recettes,
            'moyennes' => $moyennesParRecette,
        ]);
    }

    #[Route('/mes-recettes', name: 'recette_mes_recettes', methods: ['GET'])]
    public function mesRecettes(RecetteRepository $recetteRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException("Vous devez être connecté pour voir vos recettes.");
        }

        $recettes = $recetteRepository->findBy(['utilisateur' => $user]);

        return $this->render('recette/mes_recettes.html.twig', [
            'recettes' => $recettes,
        ]);
    }

    #[Route('/new', name: 'recette_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $recette = new Recette();

        if ($recette->getRecetteIngredients()->count() === 0) {
            $recette->addRecetteIngredient(new RecetteIngredient());
        }

        $form = $this->createForm(RecetteForm::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('recettes_directory'),
                        $newFilename
                    );
                    $recette->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors du téléchargement de l\'image.');
                }
            }

            foreach ($recette->getRecetteIngredients() as $ri) {
                $ri->setRecette($recette);
                $em->persist($ri);
            }

            $recette->setUtilisateur($this->getUser());

            $em->persist($recette);
            $em->flush();

            $this->addFlash('success', 'Recette ajoutée avec succès !');

            return $this->redirectToRoute(
                $this->isGranted('ROLE_ADMIN') ? 'recette_liste_admin' : 'recette_mes_recettes'
            );
        }

        return $this->render('recette/new.html.twig', [
            'form' => $form->createView(),
            'recette' => $recette,
        ]);
    }

    #[Route('/{id}', name: 'recette_show', methods: ['GET', 'POST'])]
    public function show(
        Request $request,
        Recette $recette,
        TisaneSuggestionService $sugg,
        CommentaireRepository $commentaireRepository,
        EntityManagerInterface $em
    ): Response {
        // --- Formulaire commentaire ---
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire, [
            'is_recette' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user) {
                throw $this->createAccessDeniedException("Vous devez être connecté pour commenter.");
            }

            // IMPORTANT : marquer le commentaire comme lié à une recette
            $commentaire->setType(1);
            $commentaire->setDate(new \DateTimeImmutable());
            $commentaire->setUtilisateur($user);
            $commentaire->setRecette($recette);

            // (optionnel) sécurité côté contrôleur si ton form n'a pas déjà la contrainte
            if (null !== $commentaire->getNote()) {
                $note = max(0, min(5, (int) $commentaire->getNote()));
                $commentaire->setNote($note);
            }

            $em->persist($commentaire);
            $em->flush();

            $this->addFlash('success', 'Votre commentaire a été ajouté.');
            return $this->redirectToRoute('recette_show', ['id' => $recette->getId()]);
        }

        // --- Commentaires de cette recette (type = 1), triés du plus récent au plus ancien ---
        $commentaires = $commentaireRepository->createQueryBuilder('c')
            ->andWhere('c.recette = :recette')
            ->andWhere('c.type = 1')
            ->setParameter('recette', $recette)
            ->orderBy('c.date', 'DESC')
            ->getQuery()
            ->getResult();

        // --- Moyenne des notes sur ces commentaires (type = 1 + note non nulle) ---
        $moyenne = $commentaireRepository->createQueryBuilder('c')
            ->select('AVG(c.note) as moyenne')
            ->andWhere('c.recette = :recette')
            ->andWhere('c.type = 1')
            ->andWhere('c.note IS NOT NULL')
            ->setParameter('recette', $recette)
            ->getQuery()
            ->getSingleScalarResult();
        $moyenne = $moyenne !== null ? round((float)$moyenne, 2) : null;

        // --- Suggestions de tisanes (avec scores) ---
        $suggestions = $sugg->suggestForRecette($recette, limit: 3, wB: 2.0, wA: 1.0);
        $tisanesSuggerees = array_map(fn($r) => $r['tisane'], $suggestions);
        $tisanesReasons = [];
        foreach ($suggestions as $row) {
            $tisanesReasons[$row['tisane']->getId()] = [
                'scoreBienfaits' => (int) $row['scoreB'],
                'scoreAromes'    => (float) $row['scoreA'],
                'scoreTotal'     => (float) $row['scoreTotal'],
            ];
        }

        return $this->render('recette/show.html.twig', [
            'recette'           => $recette,
            'form'              => $form->createView(),
            'commentaires'      => $commentaires,
            'moyenne'           => $moyenne,
            'tisanesSuggerees'  => $tisanesSuggerees,
            'tisanesReasons'    => $tisanesReasons, // optionnel : pour afficher "pourquoi cette tisane"
        ]);
    }


    #[Route('/{id}/edit', name: 'recette_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Recette $recette, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        if (!$user || (!$this->isGranted('ROLE_ADMIN') && $recette->getUtilisateur() !== $user)) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres recettes.');
        }

        $ancienneImage = $recette->getImage();

        $form = $this->createForm(RecetteForm::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    // Supprimer l'ancienne image si elle existe
                    if ($ancienneImage && file_exists($this->getParameter('uploads_directory').'/'.$ancienneImage)) {
                        unlink($this->getParameter('recettes_directory').'/'.$ancienneImage);
                    }

                    // Upload de la nouvelle image
                    $imageFile->move(
                        $this->getParameter('recettes_directory'),
                        $newFilename
                    );
                    $recette->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors du téléchargement de l\'image.');
                }
            } else {
                // Garder l'ancienne image si aucune nouvelle n'est fournie
                $recette->setImage($ancienneImage);
            }

            $em->flush();

            $this->addFlash('success', 'Recette modifiée avec succès.');

            return $this->redirectToRoute(
                $this->isGranted('ROLE_ADMIN') ? 'recette_liste_admin' : 'recette_mes_recettes'
            );
        }

        return $this->render('recette/edit.html.twig', [
            'form' => $form->createView(),
            'recette' => $recette,
        ]);
    }





    #[Route('/{id}/delete', name: 'recette_delete', methods: ['POST'])]
    public function delete(Request $request, Recette $recette, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user || (!$this->isGranted('ROLE_ADMIN') && $recette->getUtilisateur() !== $user)) {
            throw $this->createAccessDeniedException('Suppression interdite.');
        }

        if ($recette->isValidation() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('warning', 'Impossible de supprimer une recette déjà validée.');
            return $this->redirectToRoute(
                $this->isGranted('ROLE_ADMIN') ? 'recette_liste_admin' : 'recette_mes_recettes'
            );
        }

        if ($this->isCsrfTokenValid('delete' . $recette->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($recette);
            $em->flush();

            $this->addFlash('success', 'Recette supprimée avec succès.');
        }

        return $this->redirectToRoute(
            $this->isGranted('ROLE_ADMIN') ? 'recette_liste_admin' : 'recette_mes_recettes'
        );
    }

    #[Route('/{id}/valider', name: 'recette_valider', methods: ['POST'])]
    public function valider(Request $request, Recette $recette, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Action réservée à l’administrateur.');
        }

        if ($this->isCsrfTokenValid('valider' . $recette->getId(), $request->request->get('_token'))) {
            $recette->setValidation(true);
            $em->flush();

            $this->addFlash('success', 'Recette validée.');
        }

        return $this->redirectToRoute('recette_liste_admin');
    }

}
