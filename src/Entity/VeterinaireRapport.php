<?php

namespace App\Entity;

use App\Repository\VeterinaireRapportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VeterinaireRapportRepository::class)]
class VeterinaireRapport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $etat_animal = null;

    #[ORM\Column(length: 180)]
    private ?string $nourriture = null;

    #[ORM\Column(length: 180)]
    private ?string $grammage = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_passage = null;

    #[ORM\Column(length: 180)]
    private ?string $id_animal = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNourriture(): ?string
    {
        return $this->nourriture;
    }

    public function setNourriture(string $nourriture): static
    {
        $this->nourriture = $nourriture;

        return $this;
    }

    public function getGrammage(): ?string
    {
        return $this->grammage;
    }

    public function setGrammage(string $grammage): static
    {
        $this->grammage = $grammage;

        return $this;
    }

    public function getDatePassage(): ?\DateTimeInterface
    {
        return $this->date_passage;
    }

    public function setDatePassage(\DateTimeInterface $date_passage): static
    {
        $this->date_passage = $date_passage;

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
