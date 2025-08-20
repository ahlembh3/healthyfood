<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class Gene
{
#[ORM\Id, ORM\GeneratedValue, ORM\Column] private ?int $id = null;

#[ORM\Column(length:100, unique:true)]
private string $nom;

#[ORM\Column(type:"text", nullable:true)]
private ?string $description = null;

// Inverses
#[ORM\ManyToMany(targetEntity: Ingredient::class, mappedBy: "genes")]
private Collection $ingredients;

#[ORM\ManyToMany(targetEntity: Bienfait::class, inversedBy: "genes")]
#[ORM\JoinTable(name: "gene_bienfait")]
private Collection $bienfaits;

public function __construct() {
$this->ingredients = new ArrayCollection();
$this->bienfaits = new ArrayCollection();
}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection<int, Ingredient>
     */
    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    public function addIngredient(Ingredient $ingredient): self
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients->add($ingredient);
            // Synchronisation inverse si disponible
            if (method_exists($ingredient, 'addGene')) {
                $ingredient->addGene($this);
            }
        }
        return $this;
    }

    public function removeIngredient(Ingredient $ingredient): self
    {
        if ($this->ingredients->removeElement($ingredient)) {
            // Synchronisation inverse si disponible
            if (method_exists($ingredient, 'removeGene')) {
                $ingredient->removeGene($this);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Bienfait>
     */
    public function getBienfaits(): Collection
    {
        return $this->bienfaits;
    }

    public function addBienfait(Bienfait $bienfait): self
    {
        if (!$this->bienfaits->contains($bienfait)) {
            $this->bienfaits->add($bienfait);
            // Synchronisation inverse si disponible
            if (method_exists($bienfait, 'addGene')) {
                $bienfait->addGene($this);
            }
        }
        return $this;
    }

    public function removeBienfait(Bienfait $bienfait): self
    {
        if ($this->bienfaits->removeElement($bienfait)) {
            // Synchronisation inverse si disponible
            if (method_exists($bienfait, 'removeGene')) {
                $bienfait->removeGene($this);
            }
        }
        return $this;
    }
}
