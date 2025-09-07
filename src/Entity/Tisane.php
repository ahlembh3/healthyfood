<?php

namespace App\Entity;

use App\Repository\TisaneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TisaneRepository::class)]
class Tisane
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de la tisane est obligatoire.")]
    #[Assert\Length(max: 255, maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le mode de préparation est obligatoire.")]
    #[Assert\Length(min: 10, minMessage: "Le mode de préparation doit contenir au moins {{ limit }} caractères.")]
    private ?string $modePreparation = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $dosage = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $precautions = null;

    #[ORM\ManyToMany(targetEntity: Bienfait::class, inversedBy: 'tisanes')]
    #[ORM\JoinTable(name: 'tisane_bienfait')]
    private Collection $bienfaits;

    #[ORM\ManyToMany(targetEntity: Plante::class, inversedBy: 'tisanes')]
    #[ORM\JoinTable(name: 'tisane_plante')]
    private Collection $plantes;

    public function __construct()
    {
        $this->bienfaits = new ArrayCollection();
        $this->plantes   = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) ($this->nom ?? 'Tisane');
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getModePreparation(): ?string { return $this->modePreparation; }
    public function setModePreparation(string $modePreparation): self { $this->modePreparation = $modePreparation; return $this; }

    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): self { $this->image = $image; return $this; }

    public function getDosage(): ?string { return $this->dosage; }
    public function setDosage(?string $dosage): self { $this->dosage = $dosage; return $this; }

    public function getPrecautions(): ?string { return $this->precautions; }
    public function setPrecautions(?string $precautions): self { $this->precautions = $precautions; return $this; }

    /** @return Collection<int, Bienfait> */
    public function getBienfaits(): Collection { return $this->bienfaits; }

    public function addBienfait(Bienfait $bienfait): self
    {
        if (!$this->bienfaits->contains($bienfait)) {
            $this->bienfaits->add($bienfait);
            if (method_exists($bienfait, 'getTisanes') && !$bienfait->getTisanes()->contains($this)) {
                $bienfait->getTisanes()->add($this);
            }
        }
        return $this;
    }

    public function removeBienfait(Bienfait $bienfait): self
    {
        if ($this->bienfaits->removeElement($bienfait)) {
            if (method_exists($bienfait, 'getTisanes') && $bienfait->getTisanes()->contains($this)) {
                $bienfait->getTisanes()->removeElement($this);
            }
        }
        return $this;
    }

    /** @return Collection<int, Plante> */
    public function getPlantes(): Collection { return $this->plantes; }

    public function addPlante(Plante $plante): self
    {
        if (!$this->plantes->contains($plante)) {
            $this->plantes->add($plante);
            if (method_exists($plante, 'getTisanes') && !$plante->getTisanes()->contains($this)) {
                $plante->getTisanes()->add($this);
            }
        }
        return $this;
    }

    public function removePlante(Plante $plante): self
    {
        if ($this->plantes->removeElement($plante)) {
            if (method_exists($plante, 'getTisanes') && $plante->getTisanes()->contains($this)) {
                $plante->getTisanes()->removeElement($this);
            }
        }
        return $this;
    }

    /**
     * Construit des précautions «effectives» si non renseignées au niveau Tisane,
     * en agrégant celles des plantes liées (sans doublons).
     */
    public function getPrecautionsEffectives(): ?string
    {
        if ($this->precautions) {
            return $this->precautions;
        }

        $chunks = [];
        foreach ($this->getPlantes() as $p) {
            $txt = trim((string) (method_exists($p, 'getPrecautions') ? $p->getPrecautions() : ''));
            if ($txt !== '') {
                $chunks[] = $txt;
            }
        }
        if (!$chunks) {
            return null;
        }

        $all   = implode("\n", $chunks);
        $lines = preg_split('/[\r\n]+|(?<=[\.\!\?])\s+/', $all, -1, PREG_SPLIT_NO_EMPTY);
        $lines = array_map(static fn($l) => trim($l), $lines);
        $lines = array_values(array_unique(array_filter($lines, static fn($l) => $l !== '')));

        return implode("\n", $lines);
    }
}
