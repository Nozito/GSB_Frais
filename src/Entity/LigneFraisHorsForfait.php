<?php

namespace App\Entity;

use App\Repository\LigneFraisHorsForfaitRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneFraisHorsForfaitRepository::class)]
class LigneFraisHorsForfait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lignesfraishorsforfait')]
    private ?FicheFrais $fichesFrais = null;


    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private $mois;
    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $date = null;

    #[ORM\Column]
    private ?float $montant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMois(): ?DateTimeInterface
    {
        return $this->mois;
    }

    public function setMois(DateTimeInterface $mois): static
    {
        $this->mois = $mois;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function setFichesFrais(?FicheFrais $fichesFrais): static
    {
        $this->fichesFrais = $fichesFrais;

        return $this;
    }

    public function setOldId($id)
    {
    }
}
