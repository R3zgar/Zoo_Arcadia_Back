<?php

namespace App\DataFixtures;

use App\Entity\Consultation;
use App\Entity\Animal;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker;

class ConsultationFixtures extends Fixture implements DependentFixtureInterface
{
    public const CONSULTATION_REFERENCE = 'consultation-';
    public const CONSULTATION_NB_TUPLES = 10;

    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR'); // Faker en français

        // Pour chaque consultation, on génère des données aléatoires
        for ($i = 1; $i <= self::CONSULTATION_NB_TUPLES; $i++) {
            // Récupérer une référence d'un animal pour la consultation
            /** @var Animal $animal */
            $animal = $this->getReference(AnimalFixtures::ANIMAL_REFERENCE . random_int(1, AnimalFixtures::ANIMAL_NB_TUPLES));

            // Créer une nouvelle consultation avec des données fictives
            $consultation = (new Consultation())
                ->setCompteur(random_int(1, 100)) // Compteur aléatoire
                ->setIdAnimal($animal->getId()); // Lier l'ID de l'animal à la consultation

            // Persister la consultation dans la base de données
            $manager->persist($consultation);
            $this->addReference(self::CONSULTATION_REFERENCE . $i, $consultation);
        }

        // Sauvegarder toutes les consultations dans la base de données
        $manager->flush();
    }

    // Dépendance avec AnimalFixtures pour garantir que les animaux sont créés avant les consultations
    public function getDependencies(): array
    {
        return [
            AnimalFixtures::class,
        ];
    }
}
