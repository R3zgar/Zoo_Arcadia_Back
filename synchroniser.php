<?php

// Importation des classes nécessaires
use MongoDB\Client;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use App\Entity\Animal;

// Chargement de l'autoloader de Composer
require_once __DIR__ . '/vendor/autoload.php';

// Configuration de la connexion MongoDB
$clientMongo = new Client('mongodb://localhost:27017');
$baseDeDonnees = $clientMongo->selectDatabase('zooarcadia'); // Sélection de la base de données ZooArcadia
$collection = $baseDeDonnees->selectCollection('animal_stats'); // Sélection de la collection animal_stats

// Configuration de Doctrine pour la connexion MySQL
$paths = [__DIR__ . '/src/Entity']; // Chemin vers les entités Doctrine
$isDevMode = true; // Mode développement activé

// Création de la configuration Doctrine
$config = ORMSetup::createAttributeMetadataConfiguration([__DIR__ . '/src/Entity'], true);

// Paramètres de connexion à la base de données MySQL
$dbParams = [
    'driver' => 'pdo_mysql',
    'user' => 'root', // Nom d'utilisateur MySQL
    'password' => 'UvuZ54hBFAgAgV', // Mot de passe MySQL
    'dbname' => 'zoo_arcadia', // Nom de la base de données MySQL
    'host' => '127.0.0.1', // Adresse du serveur MySQL
    'port' => 3306, // Port MySQL
];

$connection = \Doctrine\DBAL\DriverManager::getConnection($dbParams, $config);

// Création de l'EntityManager avec la configuration et la connexion
$entityManager = new EntityManager($connection, $config);

// Récupération de tous les animaux de la base de données MySQL
$query = $entityManager->createQuery('SELECT a FROM App\Entity\Animal a');
$animaux = $query->getResult(); // Récupère tous les animaux

// Synchronisation des données dans MongoDB pour chaque animal
foreach ($animaux as $animal) {
    // Récupération des données de l'animal depuis MySQL
    $animalId = $animal->getId();
    $animalName = $animal->getPrenomAnimal();
    $species = $animal->getSpecies(); // Remplacez avec le nom de la méthode correcte pour obtenir l'espèce de l'animal

    // Vérifiez si l'animal existe déjà dans MongoDB et mettez à jour le view_count
    $collection->updateOne(
        ['animal_id' => $animalId], // Cherche l'animal par ID pour éviter les doublons
        [
            '$set' => [
                'animal_name' => $animalName,
                'species' => $species,
                'view_count' => 0 // Définit le view_count initial à 0
            ]
        ],
        ['upsert' => true] // Ajoute un nouveau document si aucun n'est trouvé (upsert)
    );

    // Affiche le message de synchronisation pour chaque animal
    echo "Synchronisation du view_count pour l'animal : " . $animalName . "\n";
}

// Message final indiquant que la synchronisation est terminée
echo "Tous les animaux ont été synchronisés avec MongoDB.";


$animal = new Animal();
var_dump($animal);