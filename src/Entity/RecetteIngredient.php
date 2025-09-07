<?php

namespace App\Entity;

use App\Repository\RecetteIngredientRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RecetteIngredientRepository::class)]
#[ORM\Table(name: 'recette_ingredient')]
#[ORM\UniqueConstraint(name: 'uniq_recette_ingredient', columns: ['id_recette', 'id_ingredient'])]
#[ORM\HasLifecycleCallbacks]
class RecetteIngredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'recetteIngredients')]
    #[ORM\JoinColumn(name: 'id_recette', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'La recette est requise.')]
    private ?Recette $recette = null;

    #[ORM\ManyToOne(inversedBy: 'recetteIngredients')]
    #[ORM\JoinColumn(name: 'id_ingredient', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Choisissez un ingrédient.')]
    private ?Ingredient $ingredient = null;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull(message: 'La quantité est obligatoire.')]
    #[Assert\Positive(message: 'La quantité doit être strictement supérieure à 0.')]
    private ?float $quantite = 100.0; // défaut côté objet

    public function getId(): ?int { return $this->id; }

    public function getRecette(): ?Recette { return $this->recette; }
    public function setRecette(?Recette $recette): self { $this->recette = $recette; return $this; }

    public function getIngredient(): ?Ingredient { return $this->ingredient; }
    public function setIngredient(?Ingredient $ingredient): self { $this->ingredient = $ingredient; return $this; }

    public function getQuantite(): ?float { return $this->quantite; }
    public function setQuantite(?float $quantite): self { $this->quantite = $quantite; return $this; }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function ensureQuantite(): void
    {
        if ($this->quantite === null) {
            $this->quantite = 100.0;
        }
    }
}
