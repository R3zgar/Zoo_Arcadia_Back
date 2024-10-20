<?php

namespace App\DataFixtures;

use App\Entity\Animal;
use App\Entity\Habitat;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Exception;
use Faker;

class AnimalFixtures extends Fixture implements DependentFixtureInterface
{
    public const ANIMAL_REFERENCE = 'animal-';
    public const ANIMAL_NB_TUPLES = 10;

    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create();

        // Pour chaque animal, on génère des données aléatoires et on attribue un habitat
        for ($i = 1; $i <= self::ANIMAL_NB_TUPLES; $i++) {
            // Récupérer une référence de l'habitat
            /** @var Habitat $habitat */
            $habitat = $this->getReference(HabitatFixtures::HABITAT_REFERENCE . random_int(1, HabitatFixtures::HABITAT_NB_TUPLES));

            // Créer un nouvel animal avec des données fictives
            $animal = (new Animal())
                ->setPrenomAnimal("Animal n°$i") // Prénom de l'animal
                ->setRaceAnimal($faker->word())  // Race de l'animal
                ->setEtatAnimal($faker->sentence()) // État de l'animal
                ->setImage("animal_$i.jpg") // Image fictive de l'animal
                ->setCreatedAt(new DateTimeImmutable()) // Date de création
                ->setHabitat($habitat); // Lier l'animal à un habitat

            // Persister l'animal dans la base de données
            $manager->persist($animal);
            $this->addReference(self::ANIMAL_REFERENCE . $i, $animal);
        }

        // Sauvegarder tous les animaux dans la base de données
        $manager->flush();
    }

    // Dépendance avec HabitatFixtures pour garantir que les habitats sont créés avant les animaux
    public function getDependencies(): array
    {
        return [
            HabitatFixtures::class,
        ];
    }
}
