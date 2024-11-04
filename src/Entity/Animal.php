<?php

namespace App\Entity;

use App\Repository\AnimalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

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

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Habitat::class, inversedBy: 'animaux')]
    #[ORM\JoinColumn(nullable: false, name: 'habitat_id', referencedColumnName: 'id')]
    private ?Habitat $habitat = null;

//    // Ajoutez ce champ pour suivre le nombre de vues
//    #[ORM\Column(type: 'integer', options: ['default' => 0])]
//    private $view_count = 0;

    #[ORM\OneToMany(mappedBy: 'animal', targetEntity: Commentaire::class)]
    private Collection $commentaires;

    #[ORM\OneToMany(mappedBy: 'animal', targetEntity: VeterinaireRapport::class)]
    private Collection $veterinaireRapports;
    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->commentaires = new ArrayCollection();
        $this->veterinaireRapports = new ArrayCollection();
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

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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



    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires[] = $commentaire;
            $commentaire->setAnimal($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): self
    {
        if ($this->commentaires->removeElement($commentaire)) {
            if ($commentaire->getAnimal() === $this) {
                $commentaire->setAnimal(null);
            }
        }

        return $this;
    }

    public function getVeterinaireRapports(): Collection
    {
        return $this->veterinaireRapports;
    }

    public function addVeterinaireRapport(VeterinaireRapport $rapport): self
    {
        if (!$this->veterinaireRapports->contains($rapport)) {
            $this->veterinaireRapports[] = $rapport;
            $rapport->setAnimal($this);
        }

        return $this;
    }

    public function removeVeterinaireRapport(VeterinaireRapport $rapport): self
    {
        if ($this->veterinaireRapports->removeElement($rapport)) {
            if ($rapport->getAnimal() === $this) {
                $rapport->setAnimal(null);
            }
        }

        return $this;
    }



//
//    public function getViewCount(): int
//    {
//        return $this->view_count;
//    }
//
//    public function setViewCount(int $view_count): self
//    {
//        $this->view_count = $view_count;
//        return $this;
//    }
//
//    public function incrementViewCount(): self
//    {
//        $this->view_count++;
//        return $this;
//    }

}
