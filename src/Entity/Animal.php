<?php

namespace App\Entity;

use App\Repository\AnimalRepository;
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

    #[ORM\Column(length: 180)]
    private ?string $id_habitat = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

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

    public function getIdHabitat(): ?string
    {
        return $this->id_habitat;
    }

    public function setIdHabitat(string $id_habitat): static
    {
        $this->id_habitat = $id_habitat;

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
}
