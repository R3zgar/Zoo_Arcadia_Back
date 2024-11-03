<?php

namespace App\Service;

use MongoDB\Client;

class MongoDBService
{
    private Client $client;
    private $database;
    private $collection;

    /**
     * Constructeur du service MongoDB.
     *
     * @param string $mongoUri URI de connexion à MongoDB.
     * @param string $databaseName Nom de la base de données à utiliser.
     */
    public function __construct(string $mongoUri = 'mongodb://localhost:27017', string $databaseName = 'zooarcadia')

    {
        // Initialisation du client MongoDB avec l'URI de connexion
        $this->client = new Client($mongoUri);

        // Sélection de la base de données spécifiée
        $this->database = $this->client->selectDatabase($databaseName);

        // Sélection de la collection 'animal_stats' pour stocker les données de vue
        $this->collection = $this->database->selectCollection('animal_stats');
    }

    /**
     * Récupère le nombre de vues pour un animal donné.
     *
     * @param string $animalName Nom de l'animal.
     * @return int Le nombre de vues de l'animal.
     */
    public function getViewCount(string $animalName): int
    {
        error_log("Retrieving view_count for: " . $animalName);
        // Recherche le document dans la collection pour un animal spécifique
        $document = $this->collection->findOne(['animal_name' => $animalName]);

        error_log("Retrieved view_count: " . ($document['view_count'] ?? 0));
        // Retourne le nombre de vues s'il est présent, sinon retourne 0
        return $document['view_count'] ?? 0;
    }

    /**
     * Incrémente le nombre de vues pour un animal donné.
     *
     * @param string $animalName Nom de l'animal.
     */
    public function incrementViewCount(string $animalName): void
    {
        // Met à jour le document pour l'animal en incrémentant le view_count de 1
        $this->collection->updateOne(
            ['animal_name' => $animalName], // Critère de recherche par nom de l'animal
            ['$inc' => ['view_count' => 1]], // Incrémente le champ 'view_count'
            ['upsert' => true] // Crée le document s'il n'existe pas encore (upsert)
        );
    }

    /**
     * Supprime l'entrée de view_count pour un animal donné.
     *
     * @param string $animalName Nom de l'animal.
     */
    public function deleteAnimalViewCount(string $animalName): void
    {
        // Supprime le document pour l'animal spécifié
        $this->collection->deleteOne(['animal_name' => $animalName]);
    }
}
