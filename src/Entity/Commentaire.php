<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private ?string $texte = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\ManyToOne(targetEntity: Animal::class, inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Animal $animal = null;

    // Getter pour texte
    public function getTexte(): ?string
    {
        return $this->texte;
    }

    // Setter pour texte
    public function setTexte(string $texte): self
    {
        $this->texte = $texte;

        return $this;
    }

    // Getter pour date_creation
    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    // Setter pour date_creation
    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    // Getter pour animal
    public function getAnimal(): ?Animal
    {
        return $this->animal;
    }

    // Setter pour animal
    public function setAnimal(?Animal $animal): self
    {
        $this->animal = $animal;

        return $this;
    }

    // Getter pour id
    public function getId(): ?int
    {
        return $this->id;
    }
}
