<?php

namespace App\DataFixtures;

use App\Entity\Service;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker;

class ServiceFixtures extends Fixture
{
    public const SERVICE_REFERENCE = 'service-';
    public const SERVICE_NB_TUPLES = 10;

    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR'); // Faker en français

        // Pour chaque service, on génère des données aléatoires
        for ($i = 1; $i <= self::SERVICE_NB_TUPLES; $i++) {
            // Créer un nouveau service avec des données fictives
            $service = (new Service())
                ->setNomService("Service n°$i") // Nom du service
                ->setDescriptionService($faker->text()); // Description fictive du service

            // Persister le service dans la base de données
            $manager->persist($service);
            $this->addReference(self::SERVICE_REFERENCE . $i, $service);
        }

        // Sauvegarder tous les services dans la base de données
        $manager->flush();
    }
}
