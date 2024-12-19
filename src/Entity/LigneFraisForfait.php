<?php

namespace App\Entity;

use App\Repository\LigneFraisForfaitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneFraisForfaitRepository::class)]
class LigneFraisForfait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

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