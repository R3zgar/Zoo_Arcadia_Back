<?php

namespace App\DataFixtures;

use App\Entity\Commentaire;
use App\Entity\Animal;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker;

class CommentaireFixtures extends Fixture implements DependentFixtureInterface
{
    public const COMMENTAIRE_REFERENCE = 'commentaire-';
    public const COMMENTAIRE_NB_TUPLES = 10;

    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create();

        // Pour chaque commentaire, on génère des données aléatoires
        for ($i = 1; $i <= self::COMMENTAIRE_NB_TUPLES; $i++) {
            // Récupérer une référence d'un animal pour lier avec le commentaire
            /** @var Animal $animal */
            $animal = $this->getReference(AnimalFixtures::ANIMAL_REFERENCE . random_int(1, AnimalFixtures::ANIMAL_NB_TUPLES));

            // Créer un nouveau commentaire avec des données fictives
            $commentaire = (new Commentaire())
                ->setContenu($faker->text()) // Contenu du commentaire
                ->setAuteur($faker->name()) // Nom de l'auteur
                ->setAnimal($animal) // Lier le commentaire à un animal
                ->setCreatedAt(new DateTimeImmutable()); // Date de création

            // Persister le commentaire dans la base de données
            $manager->persist($commentaire);
            $this->addReference(self::COMMENTAIRE_REFERENCE . $i, $commentaire);
        }

        // Sauvegarder tous les commentaires dans la base de données
        $manager->flush();
    }

    // Dépendance avec AnimalFixtures pour garantir que les animaux sont créés avant les commentaires
    public function getDependencies(): array
    {
        return [
            AnimalFixtures::class,
        ];
    }
}
