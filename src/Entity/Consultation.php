<?php

namespace App\Entity;

use App\Repository\ConsultationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsultationRepository::class)]
class Consultation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $compteur = null;

    #[ORM\Column(length: 180)]
    private ?string $id_animal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompteur(): ?int
    {
        return $this->compteur;
    }

    public function setCompteur(int $compteur): static
    {
        $this->compteur = $compteur;

        return $this;
    }

    public function getIdAnimal(): ?string
    {
        return $this->id_animal;
    }

    public function setIdAnimal(string $id_animal): static
    {
        $this->id_animal = $id_animal;

        return $this;
    }
}
