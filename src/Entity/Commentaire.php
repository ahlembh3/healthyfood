<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le contenu ne doit pas être vide.")]
    private ?string $contenu = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 0, max: 5, notInRangeMessage: "La note doit être entre 0 et 5.")]
    #[Assert\Type(type: 'integer', message: "La note doit être un nombre entier.")]
    private ?int $note = null;

    #[ORM\Column]
    private ?bool $signaler = false;

    #[ORM\Column(type: 'smallint')]
    #[Assert\Choice(choices: [1, 2], message: "Le type doit être 1 (recette) ou 2 (article).")]
    private ?int $type = null;


    #[ORM\ManyToOne(targetEntity: Recette::class, inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Recette $recette = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: true)] 
    private ?Article $article = null;


    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Un utilisateur doit être associé.")]
    private ?Utilisateur $utilisateur = null;

    //  utilisateur ayant signalé
    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    private ?Utilisateur $signalePar = null;

    
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $signaleLe = null;

   

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
         $this->date = $date;
         return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): static
    {
        $this->note = $note;
        return $this;
    }

    public function isSignaler(): ?bool
    {
        return $this->signaler;
    }

    public function setSignaler(bool $signaler): static
    {
        $this->signaler = $signaler;
        return $this;
    }

    public function getRecette(): ?Recette
    {
        return $this->recette;
    }

    public function setRecette(?Recette $recette): static
    {
        $this->recette = $recette;
        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

   
    public function getSignalePar(): ?Utilisateur
    {
        return $this->signalePar;
    }

    public function setSignalePar(?Utilisateur $signalePar): static
    {
        $this->signalePar = $signalePar;
        return $this;
    }

   
    public function getSignaleLe(): ?\DateTimeInterface
    {
        return $this->signaleLe;
    }

    public function setSignaleLe(?\DateTimeInterface $signaleLe): static
    {
        $this->signaleLe = $signaleLe;
        return $this;
    }
    public function getType(): ?int
    {
    return $this->type;
    }

    public function setType(int $type): static
    {
    $this->type = $type;
    return $this;
    }
    public function getArticle(): ?Article
    {
    return $this->article;
    }

    public function setArticle(?Article $article): static
    {
    $this->article = $article;
    return $this;
    }


}
