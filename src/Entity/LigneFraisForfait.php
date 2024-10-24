<?php

namespace App\Entity;

use App\Repository\LigneFraisForfaitRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneFraisForfaitRepository::class)]
class LigneFraisForfait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    //#[ORM\Column(length: 255)]
    //private ?string $libelle = null;

    //#[ORM\Column(type: Types::DATE_MUTABLE)]
    //private ?DateTimeInterface $date = null;

   // #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    //private ?string $montant = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\ManyToOne(inversedBy: 'lignefraisforfaits')]
    private ?FicheFrais $fichesFrais = null;

    #[ORM\ManyToOne(inversedBy: 'lignesFraisForfaits')]
    private ?FraisForfait $FraisForfaits = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    //public function getLibelle(): ?string
    //{
    //    return $this->libelle;
    //}

    //public function setLibelle(string $libelle): static
    //{
    //    $this->libelle = $libelle;

    //    return $this;
    //}

    //public function getDate(): ?DateTimeInterface
    //{
    //    return $this->date;
    //}

    //public function setDate(DateTimeInterface $date): static
    //{
    //    $this->date = $date;

    //    return $this;
    ///}

    //public function getMontant(): ?string
    //{
    //    return $this->montant;
    //}

    //public function setMontant(string $montant): static
    //{
    //    $this->montant = $montant;

    //    return $this;
    //}

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getFichesFrais(): ?FicheFrais
    {
        return $this->fichesFrais;
    }

    public function setFichesFrais(?FicheFrais $fichesFrais): static
    {
        $this->fichesFrais = $fichesFrais;

        return $this;
    }

    public function getFraisForfaits(): ?FraisForfait
    {
        return $this->FraisForfaits;
    }

    public function setFraisForfaits(?FraisForfait $FraisForfaits): static
    {
        $this->FraisForfaits = $FraisForfaits;

        return $this;
    }
}
