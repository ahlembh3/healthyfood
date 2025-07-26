<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Form\RecetteForm;
use App\Repository\RecetteRepository;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Form\CommentaireType;
use App\Entity\Commentaire;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\IngredientRepository;
use App\Entity\RecetteIngredient;

#[Route('/recettes')]
final class RecetteController extends AbstractController
{
    #[Route(name: 'recette_index', methods: ['GET'])]
    public function index(RecetteRepository $recetteRepository,CommentaireRepository $commentaireRepository): Response
  {
    $recettes = $recetteRepository->findAll();
    $moyennes = $commentaireRepository->getMoyenneNoteParRecette();

    // Transformer les résultats en tableau clé-valeur [recette_id => moyenne]
    $moyennesParRecette = [];
    foreach ($moyennes as $item) {
        $moyennesParRecette[$item['recette_id']] = round($item['moyenne'], 2);
    }

    return $this->render('recette/index.html.twig', [
        'recettes' => $recettes,
        'moyennes' => $moyennesParRecette,
    ]);
}

   #[Route('/new', name: 'recette_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    if (!$this->getUser()) {
        throw $this->createAccessDeniedException('Connectez-vous pour ajouter une recette.');
    }

    $recette = new Recette();
    $recetteIngredient = new RecetteIngredient();
    $recette->addRecetteIngredient($recetteIngredient);
    $form = $this->createForm(RecetteForm::class, $recette);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Traitement de l'image
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
            }

            $recette->setImage($newFilename);
        }

        //  Important : établir le lien bidirectionnel entre Recette et chaque RecetteIngredient
        foreach ($recette->getRecetteIngredients() as $recetteIngredient) {
            $recetteIngredient->setRecette($recette);
            $entityManager->persist($recetteIngredient);
        }

        // Optionnel : associer l'utilisateur connecté (si gestion via login)
        if ($this->getUser()) {
            $recette->setUtilisateur($this->getUser());
        }

        $entityManager->persist($recette);
        $entityManager->flush();

        return $this->redirectToRoute('recette_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('recette/new.html.twig', [
        'recette' => $recette,
        'form' => $form,
    ]);
}


  #[Route('/{id}', name: 'recette_show', methods: ['GET', 'POST'])]
public function show(Request $request, Recette $recette,CommentaireRepository $commentaireRepository, EntityManagerInterface $em): Response
{
    $commentaire = new Commentaire();
    $form = $this->createForm(CommentaireType::class, $commentaire);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException("Vous devez être connecté pour commenter.");
        }

        $commentaire->setDate(new \DateTimeImmutable());
        $commentaire->setUtilisateur($user);
        $commentaire->setRecette($recette);

        $em->persist($commentaire);
        $em->flush();

        $this->addFlash('success', 'Votre commentaire a été ajouté.');
        return $this->redirectToRoute('recette_show', ['id' => $recette->getId()]);
    }
    $moyenne = $commentaireRepository->createQueryBuilder('c')
        ->select('AVG(c.note) as moyenne')
        ->where('c.recette = :recette')
        ->andWhere('c.note IS NOT NULL')
        ->setParameter('recette', $recette)
        ->getQuery()
        ->getSingleScalarResult();

    return $this->render('recette/show.html.twig', [
        'recette' => $recette,
        'form' => $form->createView(),
        'commentaires' => $recette->getCommentaires(), // pour afficher les commentaires
        'moyenne' => $moyenne ? round($moyenne, 2) : null,
    ]);
}


    #[Route('/{id}/edit', name: 'recette_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Recette $recette, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour modifier une recette.');
        }

        if (!$this->isGranted('ROLE_ADMIN') && $recette->getUtilisateur() !== $user) {
            throw $this->createAccessDeniedException("Vous ne pouvez modifier que vos propres recettes.");
        }

        if (!$this->isGranted('ROLE_ADMIN') && $recette->isValidation()) {
            throw $this->createAccessDeniedException("Cette recette a déjà été validée par l’administrateur.");
        }


        $form = $this->createForm(RecetteForm::class, $recette,['is_edit'=>true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
            }

            $recette->setImage($newFilename);
        }
            $entityManager->flush();

            return $this->redirectToRoute('recette_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('recette/edit.html.twig', [
            'recette' => $recette,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'recette_delete', methods: ['POST'])]
    public function delete(Request $request, Recette $recette, EntityManagerInterface $entityManager): Response
    {

        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Accès réservé à l\'administrateur.');
        }

        if ($this->isCsrfTokenValid('delete'.$recette->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($recette);
            $entityManager->flush();
        }

        return $this->redirectToRoute('recette_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/recette/{id}/valider', name: 'recette_valider', methods: ['POST'])]
public function valider(Request $request, Recette $recette, EntityManagerInterface $em): Response
{
    if (!$this->isGranted('ROLE_ADMIN')) {
        throw $this->createAccessDeniedException('Accès réservé à l\'administrateur.');
    }

    if ($this->isCsrfTokenValid('valider' . $recette->getId(), $request->request->get('_token'))) {
        $recette->setValidation(true);
        $em->flush();
    }

    return $this->redirectToRoute('recette_index');
}



#[Route('/ajax/ingredients', name: 'ajax_ingredients_by_type', methods: ['GET'])]
public function getIngredientsByType(Request $request, EntityManagerInterface $em): JsonResponse
{
    // Récupération du type depuis l'URL
    $type = trim($request->query->get('type', ''));

    // Si aucun type n'est fourni
    if (empty($type)) {
        return new JsonResponse(['error' => 'Type manquant'], 400);
    }

    // Requête DQL : insensible à la casse et aux espaces
    $ingredients = $em->createQuery(
        'SELECT i FROM App\Entity\Ingredient i WHERE LOWER(TRIM(i.type)) = LOWER(:type)'
    )
    ->setParameter('type', $type)
    ->getResult();

    // Formatage des données en JSON
    $result = array_map(function ($ingredient) {
        return [
            'id' => $ingredient->getId(),
            'nom' => $ingredient->getNom(),
            'unite' => $ingredient->getUnite(),
        ];
    }, $ingredients);

    return new JsonResponse($result);
}

}
