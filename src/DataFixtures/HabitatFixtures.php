<?php

namespace App\DataFixtures;

use App\Entity\Habitat;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker;

class HabitatFixtures extends Fixture
{
    public const HABITAT_REFERENCE = 'habitat-';
    public const HABITAT_NB_TUPLES = 5;

    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR'); // Faker en français

        // Pour chaque habitat, on génère des données aléatoires
        for ($i = 1; $i <= self::HABITAT_NB_TUPLES; $i++) {
            // Créer un nouvel habitat avec des données fictives
            $habitat = (new Habitat())
                ->setNomHabitat("Habitat n°$i") // Nom de l'habitat
                ->setDescriptionHabitat($faker->text()) // Description de l'habitat
                ->setImage("habitat_$i.jpg") // Image fictive de l'habitat
                ->setCreatedAt(new DateTimeImmutable()); // Date de création

            // Persister l'habitat dans la base de données
            $manager->persist($habitat);

            // Ajouter une référence pour lier cet habitat avec d'autres entités (ex: AnimalFixtures)
            $this->addReference(self::HABITAT_REFERENCE . $i, $habitat);
        }

        // Sauvegarder tous les habitats dans la base de données
        $manager->flush();
    }
}
