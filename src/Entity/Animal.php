<?php

namespace App\Entity;

use App\Repository\AnimalRepository;
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

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable(); // Otomatik oluşturulma zamanı
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
}
