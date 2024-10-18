<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private $password;

    #[ORM\Column(type: 'string', length: 100)]
    private $nom;

    // Méthode pour obtenir l'ID de l'utilisateur
    public function getId(): ?int
    {
        return $this->id;
    }

    // Méthode pour obtenir l'email de l'utilisateur
    public function getEmail(): ?string
    {
        return $this->email;
    }

    // Méthode pour définir l'email de l'utilisateur
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    // Méthode pour obtenir les rôles de l'utilisateur
    public function getRoles(): array
    {

        return array_unique($this->roles); // Évite la duplication des rôles
    }

    // Méthode pour définir les rôles de l'utilisateur
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    // Méthode pour obtenir le mot de passe haché de l'utilisateur
    public function getPassword(): string
    {
        return $this->password ?? ''; // Retourne une chaîne vide si le mot de passe est NULL
    }

    // Méthode pour définir le mot de passe de l'utilisateur
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    // Méthode pour obtenir le nom de l'utilisateur
    public function getNom(): ?string
    {
        return $this->nom;
    }

    // Méthode pour définir le nom de l'utilisateur
    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    // Méthode pour identifier l'utilisateur (obligatoire avec UserInterface)
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    // Méthode pour effacer les informations sensibles (ex: mot de passe en clair)
    public function eraseCredentials()
    {
        // Si vous stockez des données sensibles temporaires, nettoyez-les ici
    }

    // Méthode pour obtenir le "salt" (non nécessaire pour bcrypt ou sodium)
    public function getSalt(): ?string
    {
        return null; // bcrypt et sodium gèrent automatiquement le salt
    }
}
