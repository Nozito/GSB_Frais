<?php

namespace App\Entity;

use App\Repository\LigneFraisHorsForfaitRepository;
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

    public function getId(): ?int
    {
        return $this->id;
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
}
