<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentaire;
use App\Form\ArticleType;
use App\Form\CommentaireType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/articles')]
class ArticleController extends AbstractController
{
    // Page publique
    #[Route('/liste', name: 'article_index', methods: ['GET'])]
    public function index(
        Request $request,
        ArticleRepository $articleRepository,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('q');
        $categorie = $request->query->get('categorie');

        $queryBuilder = $articleRepository->createQueryBuilder('a')
            ->where('a.validation = true');

        if ($search) {
            $queryBuilder
                ->andWhere('a.titre LIKE :search OR a.contenu LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($categorie) {
            $queryBuilder
                ->andWhere('a.categorie = :categorie')
                ->setParameter('categorie', $categorie);
        }

        $queryBuilder->orderBy('a.date', 'DESC');

        $articles = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            6
        );

        $categoriesDisponibles = ['Bien-être', 'Nutrition', 'Plantes', 'Conseils', 'Autre'];

        return $this->render('article/articles_liste.html.twig', [
            'articles' => $articles,
            'search' => $search,
            'categorie' => $categorie,
            'categoriesDisponibles' => $categoriesDisponibles,
        ]);
    }

    // Page d'administration
    #[Route('/admin/liste', name: 'article_liste_admin', methods: ['GET'])]
    public function listeAdmin(ArticleRepository $articleRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/mes-articles', name: 'article_mes_articles', methods: ['GET'])]
    public function mesArticles(ArticleRepository $articleRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir vos articles.');
        }

        $articles = $articleRepository->findBy(['utilisateur' => $user]);

        return $this->render('article/mes_articles.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/new', name: 'article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setDate(new \DateTimeImmutable());
            $article->setUtilisateur($this->getUser());

            $contenu = $article->getContenu();
            if (strip_tags($contenu) === $contenu) {
                $contenu = nl2br(htmlspecialchars($contenu));
                $article->setContenu($contenu);
            }

            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

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

    #[Route('/article/{id}', name: 'article_show', methods: ['GET', 'POST'])]
    public function show(
        Request $request,
        Article $article,
        EntityManagerInterface $em,
        PaginatorInterface $paginator
    ): Response {
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

        $form = null;

        if ($this->getUser()) {
            $commentaire = new Commentaire();
            $commentaire->setUtilisateur($this->getUser());
            $form = $this->createForm(CommentaireType::class, $commentaire, [
                'is_recette' => false,
                'attr' => ['id' => 'form_commentaire'],
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                $commentaire->setUtilisateur($this->getUser());

                if ($form->isValid()) {
                    $commentaire->setDate(new \DateTimeImmutable());
                    $commentaire->setType(2);
                    $commentaire->setArticle($article);

                    $em->persist($commentaire);
                    $em->flush();

                    $this->addFlash('success', 'Commentaire ajouté !');

                    return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
                }
            }
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
            'commentaires' => $commentaires,
            'formCommentaire' => $form?->createView(),
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
                $contenu = nl2br(htmlspecialchars($contenu));
                $article->setContenu($contenu);
            }

            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

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
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    #[Route('/{id}', name: 'article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $em): Response
    {
        if ($article->isValidation() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Seul un administrateur peut supprimer un article validé.');
        }

        if (!$this->isGranted('ROLE_ADMIN') && $article->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres articles non validés.');
        }

        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
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

        if ($this->isCsrfTokenValid('valider' . $article->getId(), $request->request->get('_token'))) {
            $article->setValidation(true);
            $em->flush();

            $this->addFlash('success', 'L’article a été validé avec succès.');
        }

        return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
    }
}
