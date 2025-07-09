<?php

namespace App\Entity;

use App\Repository\BienfaitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: BienfaitRepository::class)]
class Bienfait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du bienfait est obligatoire.")]
    #[Assert\Length(
    max: 255,
    maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $nom = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: Tisane::class, mappedBy: 'bienfaits')]
    private Collection $tisanes;

    #[ORM\ManyToMany(targetEntity: Plante::class, mappedBy: 'bienfaits')]
    private Collection $plantes;

    public function __construct()
    {
       $this->tisanes = new ArrayCollection();
       $this->plantes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
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

    /** @return Collection<int, Tisane> */
    public function getTisanes(): Collection
    {
        return $this->tisanes;
    }

    public function addTisane(Tisane $tisane): static
    {
        if (!$this->tisanes->contains($tisane)) {
            $this->tisanes->add($tisane);
        }

        return $this;
    }

    public function removeTisane(Tisane $tisane): static
    {
        $this->tisanes->removeElement($tisane);

        return $this;
    }

    /** @return Collection<int, Plante> */
    public function getPlantes(): Collection
    {
        return $this->plantes;
    }

    public function addPlante(Plante $plante): static
    {
        if (!$this->plantes->contains($plante)) {
            $this->plantes->add($plante);
        }

        return $this;
    }

    public function removePlante(Plante $plante): static
    {
        $this->plantes->removeElement($plante);

        return $this;
    }
}

