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
    #[Route('/', name: 'article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        $user = $this->getUser();

        // Si l'utilisateur est connecté et n'est pas admin → redirige vers la liste publique
        if ($user && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('article_liste_utilisateur');
        }

        // Sinon, admin → vue de gestion
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

            //  Mise en forme automatique si aucun HTML n’est détecté
            $contenu = $article->getContenu();
            if (strip_tags($contenu) === $contenu) {
                // Pas de balises HTML → mise en forme simple
                $contenu = nl2br(htmlspecialchars($contenu));
                $article->setContenu($contenu);
            }

            //  Gestion image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
                    $article->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $em->persist($article);
            $em->flush();

            //  Redirection conditionnelle selon le rôle
            $roles = $this->getUser()->getRoles();
            if (in_array('ROLE_ADMIN', $roles)) {
                return $this->redirectToRoute('article_index');
            } else {
                return $this->redirectToRoute('article_mes_articles');
            }
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
        // 1. Requête pour récupérer les commentaires liés à cet article (type = 2)
        $query = $em->getRepository(Commentaire::class)->createQueryBuilder('c')
            ->where('c.article = :article')
            ->andWhere('c.type = 2')
            ->setParameter('article', $article)
            ->orderBy('c.date', 'DESC')
            ->getQuery();

        // 2. Pagination : 5 commentaires par page
        $commentaires = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        // 3. Création du formulaire de commentaire (si connecté)
        $form = null;

        if ($this->getUser()) {
            $commentaire = new Commentaire();
            $commentaire->setUtilisateur($this->getUser());
            $form = $this->createForm(CommentaireType::class, $commentaire, [
                'is_recette' => false,
                'attr' => ['id' => 'form_commentaire'], //  donne une ID unique
            ]);


            $form->handleRequest($request);

            // ⚠Associer l'utilisateur AVANT la validation
            if ($form->isSubmitted()) {
                $commentaire->setUtilisateur($this->getUser());

                if ($form->isValid()) {
                    $commentaire->setDate(new \DateTimeImmutable());
                    $commentaire->setType(2); // 2 = article
                    $commentaire->setArticle($article);

                    $em->persist($commentaire);
                    $em->flush();

                    $this->addFlash('success', 'Commentaire ajouté !');

                    return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
                }
            }
        }

        // 4. Rendu de la vue
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
            //  Mise en forme automatique si aucun HTML
            $contenu = $article->getContenu();
            if (strip_tags($contenu) === $contenu) {
                $contenu = nl2br(htmlspecialchars($contenu));
                $article->setContenu($contenu);
            }

            //  Image
            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
                    $article->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $em->flush();

            // Redirection selon le rôle
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('article_index');
            } else {
                return $this->redirectToRoute('article_mes_articles');
            }
        }

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }


    #[Route('/{id}', name: 'article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $em): Response
    {
        // Vérifie que seul un administrateur peut supprimer un article validé
        if ($article->isValidation() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Seul un administrateur peut supprimer un article validé.');
        }

        // Vérifie que l'utilisateur est bien le propriétaire OU admin pour les articles non validés
        if (!$this->isGranted('ROLE_ADMIN') && $article->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres articles non validés.');
        }

        // Protection CSRF
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $em->remove($article);
            $em->flush();
            $this->addFlash('success', 'Article supprimé avec succès.');
        }

        // Redirection selon le rôle
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('article_index');
        } else {
            return $this->redirectToRoute('article_mes_articles');
        }
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
