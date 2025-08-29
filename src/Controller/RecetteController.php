<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Entity\RecetteIngredient;
use App\Form\RecetteForm;
use App\Form\CommentaireType;
use App\Repository\RecetteRepository;
use App\Repository\IngredientRepository;
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
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/recettes')]
final class RecetteController extends AbstractController
{
    /**
     * Point d’entrée “recettes” :
     * - visiteur (non connecté)  -> liste publique
     * - connecté non-admin       -> mes recettes
     * - admin                    -> gestion admin
     */
    #[Route('/', name: 'recette_index', methods: ['GET'])]
    public function index(): Response
    {
        //$user = $this->getUser();

        //if (!$user) {
            return $this->redirectToRoute('recette_liste');
        //}

        //if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            //return $this->redirectToRoute('recette_mes_recettes');
        //}

        //return $this->redirectToRoute('recette_liste_admin');
    }


    #[Route('/liste', name: 'recette_liste', methods: ['GET'])]
    public function listePublique(
        Request $request,
        RecetteRepository $recetteRepository,
        CommentaireRepository $commentaireRepository,
        IngredientRepository $ingredientRepo,
        EntityManagerInterface $em,
        PaginatorInterface $paginator
    ): Response {
        $q        = trim((string)$request->query->get('q', ''));
        $ingName  = trim((string)$request->query->get('ingredient', ''));
        $type     = trim((string)$request->query->get('type', ''));
        $saison   = trim((string)$request->query->get('saison', ''));
        $bienfait = trim((string)$request->query->get('bienfait', ''));
        $calMax   = $request->query->get('calMax');

        $page    = max(1, (int)$request->query->get('page', 1));
        $perPage = 9; // ajuste si tu veux 6/12/etc.

        $types = $ingredientRepo->findDistinctTypes();

        $qb = $em->createQueryBuilder()
            ->select('DISTINCT r')
            ->from(\App\Entity\Recette::class, 'r')
            ->leftJoin('r.recetteIngredients', 'ri')
            ->leftJoin('ri.ingredient', 'ing')
            ->leftJoin('ing.genes', 'g')
            ->leftJoin('g.bienfaits', 'bf')
            ->where('r.validation = :valide')
            ->setParameter('valide', true);

        if ($q !== '') {
            $qb->andWhere('(LOWER(r.titre) LIKE :q OR LOWER(r.description) LIKE :q)')
                ->setParameter('q', '%'.mb_strtolower($q).'%');
        }
        if ($ingName !== '') {
            $qb->andWhere('LOWER(ing.nom) LIKE :ingName')
                ->setParameter('ingName', '%'.mb_strtolower($ingName).'%');
        }
        if ($type !== '') {
            $qb->andWhere('LOWER(ing.type) = :type')
                ->setParameter('type', mb_strtolower($type));
        }
        if ($saison !== '') {
            $qb->andWhere('LOWER(ing.saisonnalite) LIKE :saison')
                ->setParameter('saison', '%'.mb_strtolower($saison).'%');
        }
        if ($bienfait !== '') {
            $qb->andWhere('LOWER(bf.nom) LIKE :bf')
                ->setParameter('bf', '%'.mb_strtolower($bienfait).'%');
        }

        // Paginer
        if ($calMax !== null && $calMax !== '') {
            // on filtre en PHP puis on pagine le résultat (KNP sait paginer un array)
            $results = $qb->getQuery()->getResult();
            $results = array_values(array_filter($results, function(\App\Entity\Recette $r) use ($calMax) {
                $total = 0.0;
                foreach ($r->getRecetteIngredients() as $ri) {
                    $ing = $ri->getIngredient();
                    if (!$ing) continue;
                    $u = $ing->getUnite();
                    if (in_array($u, ['gramme','millilitre'], true)) {
                        $factor = ($ri->getQuantite() ?? 0) / 100.0;
                        $total += (float)($ing->getCalories() ?? 0) * $factor;
                    }
                }
                return $total <= (float)$calMax;
            }));
            $recettes = $paginator->paginate($results, $page, $perPage);
        } else {
            // pagination SQL efficace
            $recettes = $paginator->paginate($qb->getQuery(), $page, $perPage);
        }

        // moyennes
        $moyennes = $commentaireRepository->getMoyenneNoteParRecette();
        $moyennesParRecette = [];
        foreach ($moyennes as $item) {
            $moyennesParRecette[(int)$item['recette_id']] = round((float)$item['moyenne'], 2);
        }

        // IMPORTANT : rends bien le template que tu édites (index.html.twig)
        return $this->render('recette/recettes_liste.html.twig', [
            'recettes' => $recettes,
            'moyennes' => $moyennesParRecette,
            'filters'  => [
                'q' => $q,
                'ingredient' => $ingName,
                'type' => $type,
                'saison' => $saison,
                'bienfait' => $bienfait,
                'calMax' => $calMax,
            ],
            'types'    => $types,
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

        $from = $request->query->get('from'); // 'liste' | 'mes' | 'admin' | null

// Récupérer les filtres (s'ils ont été propagés depuis la liste publique)
        $filterKeys = ['q','ingredient','type','saison','bienfait','calMax'];
        $filters = [];
        foreach ($filterKeys as $k) {
            $v = $request->query->get($k);
            if ($v !== null && $v !== '') {
                $filters[$k] = $v;
            }
        }

// Règles de retour
        $backRoute  = 'recette_liste'; // défaut
        $backParams = $filters;

        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $user    = $this->getUser();

        if ($isAdmin) {
            if ($from === 'liste') {
                $backRoute = 'recette_liste';
                $backParams = $filters;
            } else {
                $backRoute = 'recette_liste_admin'; // page de gestion admin
                $backParams = [];
            }
        } elseif ($user) {
            if ($from === 'mes') {
                $backRoute = 'recette_mes_recettes';
                $backParams = [];
            } elseif ($from === 'liste') {
                $backRoute = 'recette_liste';
                $backParams = $filters;
            } else {
                // connecté mais venu d'ailleurs → retour vers ses recettes
                $backRoute = 'recette_mes_recettes';
                $backParams = [];
            }
        } else {
            // anonyme → retour liste publique (avec filtres conservés)
            $backRoute = 'recette_liste';
            $backParams = $filters;
        }

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
            'backRoute'        => $backRoute,
            'backParams'       => $backParams,
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
