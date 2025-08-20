<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'accord_aromatique')]
#[ORM\UniqueConstraint(name: 'u_pair_ing', columns: ['plante_id','ingredient_id'])]
#[ORM\UniqueConstraint(name: 'u_pair_type', columns: ['plante_id','ingredient_type'])]
class AccordAromatique
{
#[ORM\Id, ORM\GeneratedValue, ORM\Column] private ?int $id = null;

#[ORM\ManyToOne(targetEntity: Plante::class)]
#[ORM\JoinColumn(nullable:false, onDelete:"CASCADE")]
private ?Plante $plante = null;

// accord direct sur un ingrédient précis (optionnel)
#[ORM\ManyToOne(targetEntity: Ingredient::class)]
#[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
private ?Ingredient $ingredient = null;

// ou accord par type d’ingrédient (ex: "Poisson", "Volaille")
#[ORM\Column(length:50, nullable:true)]
private ?string $ingredientType = null;

#[ORM\Column(type: "float", options: ["default" => 1.0])]
private float $score = 1.0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlante(): ?Plante
    {
        return $this->plante;
    }

    public function setPlante(?Plante $plante): self
    {
        $this->plante = $plante;
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

    public function getIngredientType(): ?string
    {
        return $this->ingredientType;
    }

    public function setIngredientType(?string $ingredientType): self
    {
        $this->ingredientType = $ingredientType;
        return $this;
    }

    public function getScore(): float
    {
        return $this->score;
    }

    public function setScore(float $score): self
    {
        $this->score = $score;
        return $this;
    }
}
