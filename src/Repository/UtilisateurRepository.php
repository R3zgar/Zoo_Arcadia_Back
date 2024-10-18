<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    /**
     * Fonction pour trouver un utilisateur par son email
     * Cette fonction retourne un utilisateur basé sur son adresse email.
     */
    public function findByEmail(string $email): ?Utilisateur
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Fonction pour trouver des utilisateurs par leur rôle dans un champ JSON
     * Cette fonction va récupérer tous les utilisateurs et filtrer les rôles en PHP.
     */
    public function findByRole(string $role): array
    {
        // Récupérer tous les utilisateurs
        $users = $this->createQueryBuilder('u')
            ->getQuery()
            ->getResult();

        // Filtrer les utilisateurs en fonction de leur rôle
        return array_filter($users, function($user) use ($role) {
            return in_array($role, $user->getRoles());
        });
    }
}
