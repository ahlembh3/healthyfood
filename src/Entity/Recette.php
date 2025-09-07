<?php

namespace App\Entity;

use App\Repository\RecetteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RecetteRepository::class)]
class Recette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(max: 255, maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: "La description ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Les instructions sont obligatoires.")]
    #[Assert\Length(min: 10, minMessage: "Les instructions doivent contenir au moins {{ limit }} caractères.")]
    private ?string $instructions = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "Le temps de préparation doit être un nombre positif.")]
    private ?int $tempsPreparation = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Choice(choices: ['Facile', 'Moyen', 'Difficile'], message: "Choisissez une difficulté valide.")]
    private ?string $difficulte = null;

    #[ORM\Column]
    private ?bool $validation = false;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "Le nombre de portions doit être positif.")]
    private ?int $portions = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "Le temps de cuisson doit être un nombre positif.")]
    private ?int $tempsCuisson = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'recette', targetEntity: RecetteIngredient::class, cascade: ['persist','remove'], orphanRemoval: true)]
    #[Assert\Valid]
    #[Assert\Count(min: 1, minMessage: "Ajoutez au moins un ingrédient à votre recette.")]
    private Collection $recetteIngredients;

    #[ORM\ManyToOne(inversedBy: 'recettes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'recette', targetEntity: Commentaire::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $commentaires;

    public function __construct()
    {
        $this->recetteIngredients = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getInstructions(): ?string { return $this->instructions; }
    public function setInstructions(string $instructions): static { $this->instructions = $instructions; return $this; }

    public function getTempsPreparation(): ?int { return $this->tempsPreparation; }
    public function setTempsPreparation(?int $tempsPreparation): static { $this->tempsPreparation = $tempsPreparation; return $this; }

    public function getDifficulte(): ?string { return $this->difficulte; }
    public function setDifficulte(?string $difficulte): static { $this->difficulte = $difficulte; return $this; }

    public function isValidation(): ?bool { return $this->validation; }
    public function setValidation(bool $validation): static { $this->validation = $validation; return $this; }

    public function getPortions(): ?int { return $this->portions; }
    public function setPortions(?int $portions): static { $this->portions = $portions; return $this; }

    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): static { $this->image = $image; return $this; }

    public function getTempsCuisson(): ?int { return $this->tempsCuisson; }
    public function setTempsCuisson(?int $tempsCuisson): static { $this->tempsCuisson = $tempsCuisson; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    /** @return Collection<int, RecetteIngredient> */
    public function getRecetteIngredients(): Collection { return $this->recetteIngredients; }

    public function addRecetteIngredient(RecetteIngredient $recetteIngredient): static
    {
        if (!$this->recetteIngredients->contains($recetteIngredient)) {
            $this->recetteIngredients->add($recetteIngredient);
            $recetteIngredient->setRecette($this);
        }
        return $this;
    }

    public function removeRecetteIngredient(RecetteIngredient $recetteIngredient): static
    {
        if ($this->recetteIngredients->removeElement($recetteIngredient)) {
            if ($recetteIngredient->getRecette() === $this) {
                $recetteIngredient->setRecette(null);
            }
        }
        return $this;
    }

    public function addIngredient(Ingredient $ingredient, float $quantite = 100.0): static
    {
        foreach ($this->recetteIngredients as $ri) {
            if ($ri->getIngredient() === $ingredient) {
                return $this;
            }
        }
        $recetteIngredient = new RecetteIngredient();
        $recetteIngredient->setRecette($this);
        $recetteIngredient->setIngredient($ingredient);
        $recetteIngredient->setQuantite($quantite);
        $this->recetteIngredients->add($recetteIngredient);
        return $this;
    }

    public function removeIngredient(Ingredient $ingredient): static
    {
        foreach ($this->recetteIngredients as $recetteIngredient) {
            if ($recetteIngredient->getIngredient() === $ingredient) {
                $this->recetteIngredients->removeElement($recetteIngredient);
                break;
            }
        }
        return $this;
    }

    public function getUtilisateur(): ?Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(?Utilisateur $utilisateur): static { $this->utilisateur = $utilisateur; return $this; }

    /** @return Collection<int, Commentaire> */
    public function getCommentaires(): Collection { return $this->commentaires; }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setRecette($this);
        }
        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            if ($commentaire->getRecette() === $this) {
                $commentaire->setRecette(null);
            }
        }
        return $this;
    }
}
