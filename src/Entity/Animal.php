<?php

namespace App\Entity;

use App\Repository\AnimalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnimalRepository::class)]
class Animal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $prenom_animal = null;

    #[ORM\Column(length: 180)]
    private ?string $race_animal = null;

    #[ORM\Column(length: 180)]
    private ?string $etat_animal = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'animaux')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Habitat $habitat = null;

    /**
     * @var Collection<int, VeterinaireRapport>
     */
    #[ORM\OneToMany(targetEntity: VeterinaireRapport::class, mappedBy: 'animal')]
    private Collection $veterinaireRapports;

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'animal', orphanRemoval: true)]
    private Collection $commentaires;

    public function __construct()
    {
        $this->veterinaireRapports = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenomAnimal(): ?string
    {
        return $this->prenom_animal;
    }

    public function setPrenomAnimal(string $prenom_animal): static
    {
        $this->prenom_animal = $prenom_animal;

        return $this;
    }

    public function getRaceAnimal(): ?string
    {
        return $this->race_animal;
    }

    public function setRaceAnimal(string $race_animal): static
    {
        $this->race_animal = $race_animal;

        return $this;
    }

    public function getEtatAnimal(): ?string
    {
        return $this->etat_animal;
    }

    public function setEtatAnimal(string $etat_animal): static
    {
        $this->etat_animal = $etat_animal;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getHabitat(): ?Habitat
    {
        return $this->habitat;
    }

    public function setHabitat(?Habitat $habitat): static
    {
        $this->habitat = $habitat;

        return $this;
    }

    /**
     * @return Collection<int, VeterinaireRapport>
     */
    public function getVeterinaireRapports(): Collection
    {
        return $this->veterinaireRapports;
    }

    public function addVeterinaireRapport(VeterinaireRapport $veterinaireRapport): static
    {
        if (!$this->veterinaireRapports->contains($veterinaireRapport)) {
            $this->veterinaireRapports->add($veterinaireRapport);
            $veterinaireRapport->setAnimal($this);
        }

        return $this;
    }

    public function removeVeterinaireRapport(VeterinaireRapport $veterinaireRapport): static
    {
        if ($this->veterinaireRapports->removeElement($veterinaireRapport)) {
            // set the owning side to null (unless already changed)
            if ($veterinaireRapport->getAnimal() === $this) {
                $veterinaireRapport->setAnimal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setAnimal($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getAnimal() === $this) {
                $commentaire->setAnimal(null);
            }
        }

        return $this;
    }
}
