<?php

namespace App\Entity;

use App\Repository\RecetteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecetteRepository::class)]
class Recette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $instructions = null;

    #[ORM\Column(nullable: true)]
    private ?int $tempsPreparation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $difficulte = null;

    #[ORM\Column]
    private ?bool $validation = null;

    #[ORM\Column(nullable: true)]
    private ?int $portions = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $valeursNutrition = null;

    #[ORM\OneToMany(mappedBy: 'recette', targetEntity: RecetteIngredient::class, cascade: ['persist', 'remove'])]
    private Collection $recetteIngredients;

    #[ORM\ManyToOne(inversedBy: 'recettes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'recette', targetEntity: Commentaire::class, cascade: ['remove'])]
    private Collection $commentaires;

    public function __construct()
    {
        $this->recetteIngredients = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(string $instructions): static
    {
        $this->instructions = $instructions;
        return $this;
    }

    public function getTempsPreparation(): ?int
    {
        return $this->tempsPreparation;
    }

    public function setTempsPreparation(?int $tempsPreparation): static
    {
        $this->tempsPreparation = $tempsPreparation;
        return $this;
    }

    public function getDifficulte(): ?string
    {
        return $this->difficulte;
    }

    public function setDifficulte(?string $difficulte): static
    {
        $this->difficulte = $difficulte;
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

    public function getPortions(): ?int
    {
        return $this->portions;
    }

    public function setPortions(?int $portions): static
    {
        $this->portions = $portions;
        return $this;
    }

    public function getValeursNutrition(): ?string
    {
        return $this->valeursNutrition;
    }

    public function setValeursNutrition(?string $valeursNutrition): static
    {
        $this->valeursNutrition = $valeursNutrition;
        return $this;
    }

    /**
     * @return Collection<int, RecetteIngredient>
     */
    public function getRecetteIngredients(): Collection
    {
        return $this->recetteIngredients;
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

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }
}

