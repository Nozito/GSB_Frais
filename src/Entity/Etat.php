<?php

namespace App\Entity;

use App\Repository\EtatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EtatRepository::class)]
class Etat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    /**
     * @var Collection<int, FicheFrais>
     */
    #[ORM\OneToMany(targetEntity: FicheFrais::class, mappedBy: 'etat')]
    private Collection $fichesFrais;

    public function __construct()
    {
        $this->fichesFrais = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, FicheFrais>
     */
    public function getFichesFrais(): Collection
    {
        return $this->fichesFrais;
    }

    public function addFichesFrai(FicheFrais $fichesFrai): static
    {
        if (!$this->fichesFrais->contains($fichesFrai)) {
            $this->fichesFrais->add($fichesFrai);
            $fichesFrai->setEtat($this);
        }

        return $this;
    }

    public function removeFichesFrai(FicheFrais $fichesFrai): static
    {
        if ($this->fichesFrais->removeElement($fichesFrai)) {
            // set the owning side to null (unless already changed)
            if ($fichesFrai->getEtat() === $this) {
                $fichesFrai->setEtat(null);
            }
        }

        return $this;
    }
}
