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
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[Route('/articles')]
class ArticleController extends AbstractController
{
    #[Route('', name: 'article_index', methods: ['GET'])]
    public function home(Request $request): Response
    {
        // Redirige sur la liste en préservant les paramètres
        return $this->redirectToRoute('article_liste', $request->query->all());
    }

    // Liste publique (recherche + filtre + pagination)
    #[Route('/liste', name: 'article_liste', methods: ['GET'])]
    public function index(
        Request $request,
        ArticleRepository $articleRepository,
        PaginatorInterface $paginator
    ): Response {
        $search    = trim((string) $request->query->get('q', ''));
        $categorie = trim((string) $request->query->get('categorie', ''));
        $page      = max(1, (int) $request->query->get('page', 1));

        if ($search !== '') {
            $articlesArr = $articleRepository->fuzzySearchValidated(
                $search,
                $categorie,
                limitCandidates: 250,
                minScore: 20
            );
            $articles = $paginator->paginate($articlesArr, $page, 6);
        } else {
            $articles = $paginator->paginate(
                $articleRepository->queryValidated($categorie),
                $page,
                6
            );
        }

        $categoriesDisponibles = $articleRepository->getAvailableCategories();

        // Mémoriser l’URL de retour
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

    #[Route('/admin/liste', name: 'article_liste_admin', methods: ['GET'])]
    public function listeAdmin(ArticleRepository $articleRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $request->getSession()->set('article_return_url', $this->generateUrl('article_liste_admin'));

        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/mes-articles', name: 'article_mes_articles', methods: ['GET'])]
    public function mesArticles(ArticleRepository $articleRepository, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir vos articles.');
        }

        $articles = $articleRepository->findBy(['utilisateur' => $user], ['date' => 'DESC']);
        $request->getSession()->set('article_return_url', $this->generateUrl('article_mes_articles'));

        return $this->render('article/mes_articles.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/new', name: 'article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $article = new Article();
        $form    = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // date & auteur : aussi sécurisés par PrePersist/NotNull
            $article->setDate(new \DateTimeImmutable());
            $article->setUtilisateur($this->getUser());

            // Si contenu brut, on met un HTML minimal
            $contenu = $article->getContenu();
            if (strip_tags($contenu) === $contenu) {
                $article->setContenu(nl2br($contenu));
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

        //  IMPORTANT : 422 si soumis mais invalide (Turbo)
        $status = ($form->isSubmitted() && !$form->isValid()) ? 422 : 200;

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ], new Response('', $status));
    }

    #[Route('/{id}', name: 'article_show', methods: ['GET', 'POST'])]
    public function show(
        Request $request,
        Article $article,
        #[Autowire(service: 'html_sanitizer.sanitizer.article.content')] HtmlSanitizerInterface $sanitizer,
        EntityManagerInterface $em,
        PaginatorInterface $paginator
    ): Response {
        // Nettoyage du contenu
        $decoded = html_entity_decode($article->getContenu() ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean   = $sanitizer->sanitize($decoded);
        $contenu = new \Twig\Markup($clean, 'UTF-8');

        // Back URL (selon contexte)
        $from    = (string) $request->query->get('from', '');
        $session = $request->getSession();
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

        // Commentaires (type = 2 pour Article)
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

        // Formulaire commentaire (si connecté)
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

        // Droits d’édition
        $canEdit = $this->isGranted('ROLE_ADMIN')
            || ($this->getUser() && $article->getUtilisateur() === $this->getUser() && !$article->isValidation());

        return $this->render('article/show.html.twig', [
            'article'         => $article,
            'commentaires'    => $commentaires,
            'formCommentaire' => $formView,
            'backUrl'         => $backUrl,
            'canEdit'         => $canEdit,
            'contenu'         => $contenu,
        ]);
    }

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
                $article->setContenu(nl2br($contenu));
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
        // ✅ IMPORTANT : 422 si soumis mais invalide (Turbo)
        $status = ($form->isSubmitted() && !$form->isValid()) ? 422 : 200;

        return $this->render('article/edit.html.twig', [
            'form'    => $form->createView(),
            'article' => $article,
        ], new Response('', $status));
    }

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
