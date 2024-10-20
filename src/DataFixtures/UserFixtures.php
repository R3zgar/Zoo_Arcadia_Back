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
    public const USER_REFERENCE = 'utilisateur-';
    public const USER_NB_TUPLES = 20;

    private UserPasswordHasherInterface $hashMotDePasse;

    public function __construct(UserPasswordHasherInterface $hashMotDePasse)
    {
        $this->hashMotDePasse = $hashMotDePasse;
    }

    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR'); // Faker en français

        // Pour chaque utilisateur, on génère des données aléatoires
        for ($i = 1; $i <= self::USER_NB_TUPLES; $i++) {
            // Créer un nouvel utilisateur avec des données fictives
            $utilisateur = (new User())
                ->setFirstName($faker->firstName()) // Prénom de l'utilisateur
                ->setLastName($faker->lastName()) // Nom de l'utilisateur
                ->setEmail("email.$i@bonjour.fr") // Email fictif en français
                ->setCreatedAt(new DateTimeImmutable()); // Date de création

            // Hachage du mot de passe
            $utilisateur->setPassword($this->hashMotDePasse->hashPassword($utilisateur, 'motdepasse' . $i));

            // Persister l'utilisateur dans la base de données
            $manager->persist($utilisateur);
            $this->addReference(self::USER_REFERENCE . $i, $utilisateur);
        }

        // Sauvegarder tous les utilisateurs dans la base de données
        $manager->flush();
    }

    // Méthode pour définir les groupes de fixtures
    public static function getGroups(): array
    {
        return ['indépendant']; // Groupe spécifique pour les fixtures d'utilisateurs
    }
}
