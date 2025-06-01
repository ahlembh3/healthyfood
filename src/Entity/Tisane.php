<?php

namespace App\Entity;

use App\Repository\TisaneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Bienfait;
use App\Entity\Plante;

#[ORM\Entity(repositoryClass: TisaneRepository::class)]
class Tisane
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $modePreparation = null;

     #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToMany(targetEntity: Bienfait::class, inversedBy: 'tisanes')]
    #[ORM\JoinTable(name: 'tisane_bienfait')]
    private Collection $bienfaits;

   #[ORM\ManyToMany(targetEntity: Plante::class, inversedBy: 'tisanes')]
   #[ORM\JoinTable(name: 'tisane_plante')]
    private Collection $plantes;

    public function __construct()
    {
       $this->bienfaits = new ArrayCollection();
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

    public function getModePreparation(): ?string
    {
        return $this->modePreparation;
    }

    public function setModePreparation(string $modePreparation): static
    {
        $this->modePreparation = $modePreparation;
        return $this;
    }

    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): static {
    $this->image = $image;
    return $this;
    }

    /** @return Collection<int, Bienfait> */
    public function getBienfaits(): Collection
    {
        return $this->bienfaits;
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
        if (!$plante->getTisanes()->contains($this)) {
            $plante->getTisanes()->add($this); // synchronisation
        }
    }

    return $this;
}

public function removePlante(Plante $plante): static
{
    $this->plantes->removeElement($plante);
    return $this;
}

public function addBienfait(Bienfait $bienfait): static
{
    if (!$this->bienfaits->contains($bienfait)) {
        $this->bienfaits->add($bienfait);
        if (!$bienfait->getTisanes()->contains($this)) {
            $bienfait->getTisanes()->add($this); // synchronisation
        }
    }

    return $this;
}

public function removeBienfait(Bienfait $bienfait): static
{
    $this->bienfaits->removeElement($bienfait);
    return $this;
}

}

