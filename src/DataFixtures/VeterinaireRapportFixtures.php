<?php

namespace App\DataFixtures;

use App\Entity\VeterinaireRapport;
use App\Entity\Animal;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker;

class VeterinaireRapportFixtures extends Fixture implements DependentFixtureInterface
{
    public const RAPPORT_REFERENCE = 'rapport-';
    public const RAPPORT_NB_TUPLES = 10;

    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create();

        // Pour chaque rapport vétérinaire, on génère des données aléatoires
        for ($i = 1; $i <= self::RAPPORT_NB_TUPLES; $i++) {
            // Récupérer une référence d'un animal pour lier avec le rapport
            /** @var Animal $animal */
            $animal = $this->getReference(AnimalFixtures::ANIMAL_REFERENCE . random_int(1, AnimalFixtures::ANIMAL_NB_TUPLES));

            // Créer un nouveau rapport avec des données fictives
            $rapport = (new VeterinaireRapport())
                ->setEtatAnimal($faker->randomElement(['En bonne santé', 'Malade', 'Blessé'])) // État de l'animal
                ->setNourriture($faker->word()) // Nourriture donnée
                ->setGrammage(random_int(100, 500)) // Grammage de la nourriture
                ->setDatePassage($faker->dateTimeThisYear()) // Date du passage
                ->setCreatedAt(new DateTimeImmutable()) // Date de création
                ->setAnimal($animal); // Lier le rapport à un animal

            // Persister le rapport dans la base de données
            $manager->persist($rapport);
            $this->addReference(self::RAPPORT_REFERENCE . $i, $rapport);
        }

        // Sauvegarder tous les rapports dans la base de données
        $manager->flush();
    }

    // Dépendance avec AnimalFixtures pour garantir que les animaux sont créés avant les rapports vétérinaires
    public function getDependencies(): array
    {
        return [
            AnimalFixtures::class,
        ];
    }
}
