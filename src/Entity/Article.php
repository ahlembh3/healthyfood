<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;



#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(
    max: 255,
    maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le contenu est obligatoire.")]
    #[Assert\Length(
    min: 20,
    minMessage: "Le contenu doit faire au moins {{ limit }} caractères.")]
    private ?string $contenu = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

   #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(options: ["default" => false])]
    private ?bool $validation = false;

    #[Assert\NotBlank(message: "La catégorie est obligatoire.")]
    #[Assert\Choice(
    choices: ['Bien-être', 'Nutrition', 'Plantes', 'Conseils', 'Autre'],
    message: "Catégorie invalide. Choisissez une valeur valide.")]
    #[ORM\Column(length: 255, nullable:false)]
    private ?string $categorie = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: '255 caractères maximum.')]
    private ?string $source = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Commentaire::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $commentaires;

    public function __construct()
    {
    $this->commentaires = new ArrayCollection();
    }

    /**
 * @return Collection<int, Commentaire>
 */
   public function getCommentaires(): Collection
   {
    return $this->commentaires;
   }

   public function addCommentaire(Commentaire $commentaire): static
   {
    if (!$this->commentaires->contains($commentaire)) {
        $this->commentaires->add($commentaire);
        $commentaire->setArticle($this);
    }

    return $this;
   }

   public function removeCommentaire(Commentaire $commentaire): static
   {
    if ($this->commentaires->removeElement($commentaire)) {
        // set the owning side to null (unless already changed)
        if ($commentaire->getArticle() === $this) {
            $commentaire->setArticle(null);
        }
    }

    return $this;
   }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function isValidation(): ?bool
    {
        return $this->validation;
    }

    public function setValidation(bool $validation): static
    {
        $this->validation = $validation;
        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): static
    {
        $this->categorie = $categorie;
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
    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;
        return $this;
    }
}

