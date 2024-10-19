<?php

namespace App\Entity;

use App\Repository\HabitatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HabitatRepository::class)]
class Habitat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $nom_habitat = null;

    #[ORM\Column(type: 'text')]
    private ?string $description_habitat = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'habitat', targetEntity: Animal::class)]
    private Collection $animaux;

    public function __construct()
    {
        $this->animaux = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable(); // Otomatik oluşturulma zamanı
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomHabitat(): ?string
    {
        return $this->nom_habitat;
    }

    public function setNomHabitat(string $nom_habitat): static
    {
        $this->nom_habitat = $nom_habitat;

        return $this;
    }

    public function getDescriptionHabitat(): ?string
    {
        return $this->description_habitat;
    }

    public function setDescriptionHabitat(string $description_habitat): static
    {
        $this->description_habitat = $description_habitat;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getAnimaux(): Collection
    {
        return $this->animaux;
    }

    public function addAnimaux(Animal $animaux): static
    {
        if (!$this->animaux->contains($animaux)) {
            $this->animaux->add($animaux);
            $animaux->setHabitat($this);
        }

        return $this;
    }

    public function removeAnimaux(Animal $animaux): static
    {
        if ($this->animaux->removeElement($animaux)) {
            if ($animaux->getHabitat() === $this) {
                $animaux->setHabitat(null);
            }
        }

        return $this;
    }
}
