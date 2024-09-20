<?php

namespace App\Entity;

use App\Repository\FraisForfaitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FraisForfaitRepository::class)]
class FraisForfait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $montant = null;

    /**
     * @var Collection<int, LigneFraisForfait>
     */
    #[ORM\OneToMany(targetEntity: LigneFraisForfait::class, mappedBy: 'FraisForfaits')]
    private Collection $lignesFraisForfaits;

    public function __construct()
    {
        $this->lignesFraisForfaits = new ArrayCollection();
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

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * @return Collection<int, LigneFraisForfait>
     */
    public function getLignesFraisForfaits(): Collection
    {
        return $this->lignesFraisForfaits;
    }

    public function addLignesFraisForfait(LigneFraisForfait $lignesFraisForfait): static
    {
        if (!$this->lignesFraisForfaits->contains($lignesFraisForfait)) {
            $this->lignesFraisForfaits->add($lignesFraisForfait);
            $lignesFraisForfait->setFraisForfaits($this);
        }

        return $this;
    }

    public function removeLignesFraisForfait(LigneFraisForfait $lignesFraisForfait): static
    {
        if ($this->lignesFraisForfaits->removeElement($lignesFraisForfait)) {
            // set the owning side to null (unless already changed)
            if ($lignesFraisForfait->getFraisForfaits() === $this) {
                $lignesFraisForfait->setFraisForfaits(null);
            }
        }

        return $this;
    }
}
