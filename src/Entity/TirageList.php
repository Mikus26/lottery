<?php

namespace App\Entity;

use App\Repository\TirageListRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TirageListRepository::class)]
class TirageList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateTirage = null;

    #[ORM\Column(length: 255)]
    private ?string $numeroUn = null;

    #[ORM\Column(length: 255)]
    private ?string $numeroDeux = null;

    #[ORM\Column(length: 255)]
    private ?string $numeroTrois = null;

    #[ORM\Column(length: 255)]
    private ?string $numeroQuatre = null;

    #[ORM\Column(length: 255)]
    private ?string $numeroCinq = null;

    #[ORM\Column(length: 255)]
    private ?string $etoileUn = null;

    #[ORM\Column(length: 255)]
    private ?string $etoileDeux = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateTirage(): ?\DateTime
    {
        return $this->dateTirage;
    }

    public function setDateTirage(\DateTime $dateTirage): static
    {
        $this->dateTirage = $dateTirage;

        return $this;
    }

    public function getNumeroUn(): ?string
    {
        return $this->numeroUn;
    }

    public function setNumeroUn(string $numeroUn): static
    {
        $this->numeroUn = $numeroUn;

        return $this;
    }

    public function getNumeroDeux(): ?string
    {
        return $this->numeroDeux;
    }

    public function setNumeroDeux(string $numeroDeux): static
    {
        $this->numeroDeux = $numeroDeux;

        return $this;
    }

    public function getNumeroTrois(): ?string
    {
        return $this->numeroTrois;
    }

    public function setNumeroTrois(string $numeroTrois): static
    {
        $this->numeroTrois = $numeroTrois;

        return $this;
    }

    public function getNumeroQuatre(): ?string
    {
        return $this->numeroQuatre;
    }

    public function setNumeroQuatre(string $numeroQuatre): static
    {
        $this->numeroQuatre = $numeroQuatre;

        return $this;
    }

    public function getNumeroCinq(): ?string
    {
        return $this->numeroCinq;
    }

    public function setNumeroCinq(string $numeroCinq): static
    {
        $this->numeroCinq = $numeroCinq;

        return $this;
    }

    public function getEtoileUn(): ?string
    {
        return $this->etoileUn;
    }

    public function setEtoileUn(string $etoileUn): static
    {
        $this->etoileUn = $etoileUn;

        return $this;
    }

    public function getEtoileDeux(): ?string
    {
        return $this->etoileDeux;
    }

    public function setEtoileDeux(string $etoileDeux): static
    {
        $this->etoileDeux = $etoileDeux;

        return $this;
    }
}
