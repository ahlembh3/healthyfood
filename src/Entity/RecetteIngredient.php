<?php

namespace App\Entity;

use App\Repository\RecetteIngredientRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecetteIngredientRepository::class)]
#[ORM\Table(name: 'recette_ingredient')]
class RecetteIngredient
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'recetteIngredients')]
    #[ORM\JoinColumn(name: 'id_recette', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Recette $recette = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'recetteIngredients')]
    #[ORM\JoinColumn(name: 'id_ingredient', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Ingredient $ingredient = null;

    #[ORM\Column(type: 'float')]
    private ?float $quantite = null;

    // Getters et setters

    public function getRecette(): ?Recette
    {
        return $this->recette;
    }

    public function setRecette(?Recette $recette): self
    {
        $this->recette = $recette;
        return $this;
    }

    public function getIngredient(): ?Ingredient
    {
        return $this->ingredient;
    }

    public function setIngredient(?Ingredient $ingredient): self
    {
        $this->ingredient = $ingredient;
        return $this;
    }

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(float $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }
}
