<?php

namespace App\Entity;

use App\Repository\PlanteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanteRepository::class)]
class Plante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomCommun = null;

    #[ORM\Column(length: 255)]
    private ?string $nomScientifique = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $partieUtilisee = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $precautions = null;


    #[ORM\ManyToMany(targetEntity: Bienfait::class, inversedBy: 'plantes')]
    #[ORM\JoinTable(name: 'plante_bienfait')]
    private Collection $bienfaits;

    #[ORM\ManyToMany(targetEntity: Tisane::class, mappedBy: 'plantes')]
    private Collection $tisanes;

    public function __construct()
    {
        $this->bienfaits = new ArrayCollection();
        $this->tisanes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomCommun(): ?string
    {
        return $this->nomCommun;
    }

    public function setNomCommun(string $nomCommun): static
    {
        $this->nomCommun = $nomCommun;
        return $this;
    }

    public function getNomScientifique(): ?string
    {
        return $this->nomScientifique;
    }

    public function setNomScientifique(string $nomScientifique): static
    {
        $this->nomScientifique = $nomScientifique;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPartieUtilisee(): ?string
    {
        return $this->partieUtilisee;
    }

    public function setPartieUtilisee(string $partieUtilisee): static
    {
        $this->partieUtilisee = $partieUtilisee;
        return $this;
    }

    public function getPrecautions(): ?string
    {
        return $this->precautions;
    }

    public function setPrecautions(string $precautions): static
    {
        $this->precautions = $precautions;
        return $this;
    }

    /** @return Collection<int, Bienfait> */
    public function getBienfaits(): Collection
    {
        return $this->bienfaits;
    }

    /** @return Collection<int, Tisane> */
    public function getTisanes(): Collection
    {
        return $this->tisanes;
    }
}

