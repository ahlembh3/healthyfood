<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentaire;
use App\Form\ArticleType;
use App\Form\CommentaireType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/articles')]
class ArticleController extends AbstractController
{
    /**
     * Point d’entrée “articles” :
     * - visiteur (non connecté)  -> liste publique
     * - connecté non-admin       -> mes articles
     * - admin                    -> gestion admin
     */
    #[Route('', name: 'article_index', methods: ['GET'])]
    public function home(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            // préserve ?q=...&categorie=...
            return $this->redirectToRoute('article_liste', $request->query->all());
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('article_liste_admin');
        }

        return $this->redirectToRoute('article_mes_articles');
    }

    // --- Liste publique (recherche + filtre + pagination) ---
    #[Route('/liste', name: 'article_liste', methods: ['GET'])]
    public function index(
        Request $request,
        ArticleRepository $articleRepository,
        PaginatorInterface $paginator
    ): Response {
        $search    = trim((string) $request->query->get('q', ''));
        $categorie = trim((string) $request->query->get('categorie', ''));
        $page      = max(1, (int) $request->query->get('page', 1));

        $qb = $articleRepository->createQueryBuilder('a')
            ->where('a.validation = true');

        if ($search !== '') {
            $qb->andWhere('LOWER(a.titre) LIKE :search OR LOWER(a.contenu) LIKE :search')
                ->setParameter('search', '%'.mb_strtolower($search).'%');
        }

        if ($categorie !== '') {
            $qb->andWhere('a.categorie = :cat')
                ->setParameter('cat', $categorie);
        }

        $qb->orderBy('a.date', 'DESC');

        $articles = $paginator->paginate(
            $qb->getQuery(),
            $page,
            6
        );

        // Catégories disponibles (dynamiques)
        $categoriesDisponibles = array_column(
            $articleRepository->createQueryBuilder('a2')
                ->select('DISTINCT a2.categorie AS categorie')
                ->where('a2.validation = true')
                ->andWhere('a2.categorie IS NOT NULL AND a2.categorie <> \'\'')
                ->orderBy('a2.categorie', 'ASC')
                ->getQuery()
                ->getScalarResult(),
            'categorie'
        );

        // mémoriser l’URL de retour (avec filtres & page)
        $returnUrl = $this->generateUrl('article_liste', [
            'q'         => $search,
            'categorie' => $categorie,
            'page'      => $page,
        ]);
        $request->getSession()->set('article_return_url', $returnUrl);

        return $this->render('article/articles_liste.html.twig', [
            'articles'              => $articles,
            'search'                => $search,
            'categorie'             => $categorie,
            'categoriesDisponibles' => $categoriesDisponibles,
        ]);
    }

    // --- Liste admin (gestion) ---
    #[Route('/admin/liste', name: 'article_liste_admin', methods: ['GET'])]
    public function listeAdmin(ArticleRepository $articleRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // mémoriser l’URL de retour pour l’admin
        $request->getSession()->set('article_return_url', $this->generateUrl('article_liste_admin'));

        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    // --- Mes articles (utilisateur connecté) ---
    #[Route('/mes-articles', name: 'article_mes_articles', methods: ['GET'])]
    public function mesArticles(ArticleRepository $articleRepository, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir vos articles.');
        }

        $articles = $articleRepository->findBy(['utilisateur' => $user], ['date' => 'DESC']);

        // mémoriser l’URL de retour perso
        $request->getSession()->set('article_return_url', $this->generateUrl('article_mes_articles'));

        return $this->render('article/mes_articles.html.twig', [
            'articles' => $articles,
        ]);
    }

    // --- Création ---
    #[Route('/new', name: 'article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $article = new Article();
        $form    = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setDate(new \DateTimeImmutable());
            $article->setUtilisateur($this->getUser());

            // Si contenu brut -> transforme en HTML simple (baseline)
            $contenu = $article->getContenu();
            if (strip_tags($contenu) === $contenu) {
                $article->setContenu(nl2br(htmlspecialchars($contenu)));
            }

            // Upload image
            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safe             = $slugger->slug($originalFilename);
                $newFilename      = $safe.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('articles_directory'), $newFilename);
                    $article->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $em->persist($article);
            $em->flush();

            $route = $this->isGranted('ROLE_ADMIN') ? 'article_liste_admin' : 'article_mes_articles';
            return $this->redirectToRoute($route);
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // --- Affichage d’un article ---
    #[Route('/{id}', name: 'article_show', methods: ['GET', 'POST'])]
    public function show(
        Request $request,
        Article $article,
        EntityManagerInterface $em,
        PaginatorInterface $paginator
    ): Response {
        // déterminer l’URL de retour
        $from     = (string) $request->query->get('from', '');
        $session  = $request->getSession();
        $backUrl  = null;

        if ($from === 'liste') {
            $backUrl = $session->get('article_return_url') ?: $this->generateUrl('article_liste');
        } elseif ($from === 'mes') {
            $backUrl = $this->generateUrl('article_mes_articles');
        } elseif ($from === 'admin') {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
            $backUrl = $this->generateUrl('article_liste_admin');
        } else {
            $backUrl = $session->get('article_return_url') ?? $this->generateUrl('article_liste');
        }

        // Pagination commentaires (type = 2 pour Article)
        $query = $em->getRepository(Commentaire::class)->createQueryBuilder('c')
            ->where('c.article = :article')
            ->andWhere('c.type = 2')
            ->setParameter('article', $article)
            ->orderBy('c.date', 'DESC')
            ->getQuery();

        $commentaires = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        // Formulaire commentaire si connecté
        $formView = null;
        if ($this->getUser()) {
            $commentaire = new Commentaire();
            $commentaire->setUtilisateur($this->getUser());

            $form = $this->createForm(CommentaireType::class, $commentaire, [
                'is_recette' => false,
                'attr'       => ['id' => 'form_commentaire'],
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $commentaire->setDate(new \DateTimeImmutable());
                $commentaire->setType(2);
                $commentaire->setArticle($article);

                $em->persist($commentaire);
                $em->flush();

                $this->addFlash('success', 'Commentaire ajouté !');
                return $this->redirectToRoute('article_show', ['id' => $article->getId(), 'from' => $from]);
            }

            $formView = $form->createView();
        }

        // droits d’édition/suppression
        $canEdit = $this->isGranted('ROLE_ADMIN')
            || ($this->getUser() && $article->getUtilisateur() === $this->getUser() && !$article->isValidation());

        return $this->render('article/show.html.twig', [
            'article'         => $article,
            'commentaires'    => $commentaires,
            'formCommentaire' => $formView,
            'backUrl'         => $backUrl,
            'canEdit'         => $canEdit,
        ]);
    }

    // --- Édition ---
    #[Route('/{id}/edit', name: 'article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();

        if (!$user || (!$this->isGranted('ROLE_ADMIN') && $article->getUtilisateur() !== $user)) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit de modifier cet article.");
        }

        if (!$this->isGranted('ROLE_ADMIN') && $article->isValidation()) {
            throw $this->createAccessDeniedException("L’article a déjà été validé par l’administrateur.");
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contenu = $article->getContenu();
            if (strip_tags($contenu) === $contenu) {
                $article->setContenu(nl2br(htmlspecialchars($contenu)));
            }

            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safe             = $slugger->slug($originalFilename);
                $newFilename      = $safe.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('articles_directory'), $newFilename);
                    $article->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $em->flush();

            $route = $this->isGranted('ROLE_ADMIN') ? 'article_liste_admin' : 'article_mes_articles';
            return $this->redirectToRoute($route);
        }

        return $this->render('article/edit.html.twig', [
            'form'    => $form->createView(),
            'article' => $article,
        ]);
    }

    // --- Suppression ---
    #[Route('/{id}/delete', name: 'article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $em): Response
    {
        if ($article->isValidation() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Seul un administrateur peut supprimer un article validé.');
        }

        if (!$this->isGranted('ROLE_ADMIN') && $article->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres articles non validés.');
        }

        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $em->remove($article);
            $em->flush();
            $this->addFlash('success', 'Article supprimé avec succès.');
        }

        $route = $this->isGranted('ROLE_ADMIN') ? 'article_liste_admin' : 'article_mes_articles';
        return $this->redirectToRoute($route);
    }

    // --- Validation (admin) ---
    #[Route('/{id}/valider', name: 'article_valider', methods: ['POST'])]
    public function valider(Request $request, Article $article, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('valider'.$article->getId(), $request->request->get('_token'))) {
            $article->setValidation(true);
            $em->flush();
            $this->addFlash('success', 'L’article a été validé avec succès.');
        }

        return $this->redirectToRoute('article_show', ['id' => $article->getId(), 'from' => 'admin']);
    }
}
