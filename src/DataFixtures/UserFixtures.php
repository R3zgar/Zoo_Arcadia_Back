<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\{Fixture, FixtureGroupInterface};
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public const USER_REFERENCE = 'user-';
    public const USER_NB_TUPLES = 20;

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create();

        // Pour chaque utilisateur, on génère des données aléatoires
        for ($i = 1; $i <= self::USER_NB_TUPLES; $i++) {
            // Créer un nouvel utilisateur avec des données fictives
            $user = (new User())
                ->setFirstName($faker->firstName()) // Prénom de l'utilisateur
                ->setLastName($faker->lastName()) // Nom de l'utilisateur
                ->setEmail("email.$i@bonjour.fr") // Email fictif
                ->setCreatedAt(new DateTimeImmutable()); // Date de création

            // Hachage du mot de passe
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password' . $i));

            // Persister l'utilisateur dans la base de données
            $manager->persist($user);
            $this->addReference(self::USER_REFERENCE . $i, $user);
        }

        // Sauvegarder tous les utilisateurs dans la base de données
        $manager->flush();
    }

    // Méthode pour définir les groupes de fixtures
    public static function getGroups(): array
    {
        return ['independent']; // Groupe spécifique pour les fixtures d'utilisateurs
    }
}
