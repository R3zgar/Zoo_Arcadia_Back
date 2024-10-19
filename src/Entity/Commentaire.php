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
    private ?string $contenu = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $auteur = null;

    #[ORM\ManyToOne(targetEntity: Animal::class, inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Animal $animal = null;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $created_at = null;

    // Getter pour contenu
    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    // Setter pour contenu
    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    // Getter pour auteur
    public function getAuteur(): ?string
    {
        return $this->auteur;
    }

    // Setter pour auteur
    public function setAuteur(string $auteur): self
    {
        $this->auteur = $auteur;
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

    // Getter pour created_at
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    // Setter pour created_at
    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    // Getter pour id
    public function getId(): ?int
    {
        return $this->id;
    }
}
