<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(type: "integer")]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    #[Groups(['user:read'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 64)]
    #[Groups(['user:read'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $apiToken = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private array $roles = [];

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    /** @var string|null Le mot de passe hashé */
    #[ORM\Column]
    private ?string $password = null;

    public function __construct()
    {
        // Génère un token API unique pour l'utilisateur
        $this->apiToken = bin2hex(random_bytes(20));
    }

    // Obtient l'ID de l'utilisateur
    public function getId(): ?int
    {
        return $this->id;
    }

    // Obtient le prénom de l'utilisateur
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    // Définit le prénom de l'utilisateur
    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    // Obtient le nom de famille de l'utilisateur
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    // Définit le nom de famille de l'utilisateur
    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    // Obtient l'adresse email de l'utilisateur
    public function getEmail(): ?string
    {
        return $this->email;
    }

    // Définit l'adresse email de l'utilisateur
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    // Obtient le token API de l'utilisateur
    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    // Définit le token API de l'utilisateur
    public function setApiToken(string $apiToken): static
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    /**
     * Obtient un identifiant visuel qui représente cet utilisateur.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Obtient les rôles de l'utilisateur.
     *
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return array_unique($this->roles);
    }

    // Définit les rôles de l'utilisateur
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Obtient le mot de passe hashé.
     *
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    // Définit le mot de passe hashé
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    // Efface les informations sensibles de l'utilisateur (si nécessaire)
    public function eraseCredentials(): void
    {
        // Si vous stockez des données temporaires sensibles sur l'utilisateur, nettoyez-les ici
        // $this->plainPassword = null;
    }

    // Obtient la date de création du compte
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    // Définit la date de création du compte
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    // Obtient la date de mise à jour du compte
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Définit la date de mise à jour du compte
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
