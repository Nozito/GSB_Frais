<?php

namespace App\Entity;

use App\Repository\FicheFraisRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;

#[ORM\Entity(repositoryClass: FicheFraisRepository::class)]
class FicheFrais
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $mois = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbJustificatifs = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $montantValid = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $dateModif = null;

    #[ORM\ManyToOne(inversedBy: 'fichefrais')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'fichesFrais')]
    private ?Etat $etat = null;

    /**
     * @var Collection<int, LigneFraisForfait>
     */
    #[ORM\OneToMany(targetEntity: LigneFraisForfait::class, mappedBy: 'fichesFrais', fetch: 'EAGER')]
    private Collection $lignefraisforfaits;

    /**
     * @var Collection<int, LigneFraisHorsForfait>
     */
    #[ORM\OneToMany(targetEntity: LigneFraisHorsForfait::class, mappedBy: 'fichesFrais', fetch: 'EAGER')]
    private Collection $lignesfraishorsforfait;

    public function __construct()
    {
        $this->lignefraisforfaits = new ArrayCollection();
        $this->lignesfraishorsforfait = new ArrayCollection();
    }

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

    public function getNbJustificatifs(): ?int
    {
        return $this->nbJustificatifs;
    }

    public function setNbJustificatifs(?int $nbJustificatifs): static
    {
        $this->nbJustificatifs = $nbJustificatifs;

        return $this;
    }

    public function getMontantValid(): ?string
    {
        return $this->montantValid;
    }

    public function setMontantValid(?string $montantValid): static
    {
        $this->montantValid = $montantValid;

        return $this;
    }

    public function getDateModif(): ?DateTimeInterface
    {
        return $this->dateModif;
    }

    public function setDateModif(DateTimeInterface $dateModif): static
    {
        $this->dateModif = $dateModif;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * @return Collection<int, LigneFraisForfait>
     */
    public function getLignefraisforfaits(): Collection
    {
        return $this->lignefraisforfaits;
    }

    public function addLignefraisforfait(LigneFraisForfait $lignefraisforfait): static
    {
        if (!$this->lignefraisforfaits->contains($lignefraisforfait)) {
            $this->lignefraisforfaits->add($lignefraisforfait);
            $lignefraisforfait->setFichesFrais($this);
        }

        return $this;
    }

    public function removeLignefraisforfait(LigneFraisForfait $lignefraisforfait): static
    {
        if ($this->lignefraisforfaits->removeElement($lignefraisforfait)) {
            // set the owning side to null (unless already changed)
            if ($lignefraisforfait->getFichesFrais() === $this) {
                $lignefraisforfait->setFichesFrais(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LigneFraisHorsForfait>
     */
    public function getLignesfraishorsforfait(): Collection
    {
        return $this->lignesfraishorsforfait;
    }

    public function addLignesfraishorsforfait(LigneFraisHorsForfait $lignesfraishorsforfait): static
    {
        if (!$this->lignesfraishorsforfait->contains($lignesfraishorsforfait)) {
            $this->lignesfraishorsforfait->add($lignesfraishorsforfait);
            $lignesfraishorsforfait->setFichesFrais($this);
        }

        return $this;
    }

    public function removeLignesfraishorsforfait(LigneFraisHorsForfait $lignesfraishorsforfait): static
    {
        if ($this->lignesfraishorsforfait->removeElement($lignesfraishorsforfait)) {
            // set the owning side to null (unless already changed)
            if ($lignesfraishorsforfait->getFicheFrais() === $this) {
                $lignesfraishorsforfait->setFichesFrais(null);
            }
        }

        return $this;
    }

    public function setOldId($id)
    {
    }
}
