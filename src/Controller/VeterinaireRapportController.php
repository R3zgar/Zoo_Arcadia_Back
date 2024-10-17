<?php

namespace App\Controller;

use App\Entity\VeterinaireRapport;
use App\Repository\VeterinaireRapportRepository;
use App\Repository\AnimalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface; // Serializer ajouté

#[Route('api/veterinaire_rapport', name: 'app_api_veterinaire_rapport')]
class VeterinaireRapportController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private VeterinaireRapportRepository $repository,
        private AnimalRepository $animalRepository,
        private SerializerInterface $serializer // Serializer ajouté
    ) {
    }

    // Créer un nouveau rapport
    #[Route(name: 'new', methods: 'POST')]
    public function new(Request $request): Response
    {
        // Désérialiser les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier si l'animal_id est envoyé
        if (!isset($data['animal_id'])) {
            return $this->json(['message' => 'L\'animal_id est manquant.'], Response::HTTP_BAD_REQUEST);
        }

        // Rechercher l'animal par ID
        $animal = $this->animalRepository->find($data['animal_id']);

        // Si l'animal n'existe pas, retourner une erreur
        if (!$animal) {
            return $this->json(['message' => 'Animal non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Créer un nouvel objet rapport
        $rapport = $this->serializer->deserialize($request->getContent(), VeterinaireRapport::class, 'json');

        // Associer l'animal au rapport
        $rapport->setAnimal($animal);

        // Persister le rapport dans la base de données
        $this->manager->persist($rapport);
        $this->manager->flush();

        // Retourner un message de succès
        return $this->json(
            ['message' => "Nouveau rapport vétérinaire créé avec succès avec l'id {$rapport->getId()}"],
            Response::HTTP_CREATED,
        );
    }

    // Lire un rapport vétérinaire spécifique
    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id, VeterinaireRapportRepository $veterinaireRapportRepository): Response
    {
        // Trouver le rapport dans la base de données par son ID
        $rapport = $veterinaireRapportRepository->find($id);

        // Si le rapport n'est pas trouvé, retourner une exception
        if (!$rapport) {
            throw new \Exception("Rapport non trouvé pour l'ID {$id}");
        }

        // Retourner les informations du rapport sous forme de JSON
        return $this->json(
            ['message' => "Un rapport trouvé : {$rapport->getEtatAnimal()} pour l'ID {$rapport->getId()}"]
        );
    }

// Mettre à jour un rapport existant
    #[Route('/{id}', name: 'update', methods: 'PUT')]
    public function update(int $id, Request $request, VeterinaireRapportRepository $veterinaireRapportRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        // Rechercher le rapport par ID dans la base de données
        $rapport = $veterinaireRapportRepository->find($id);

        // Si le rapport n'existe pas, renvoyer une erreur 404
        if (!$rapport) {
            return $this->json(['message' => "Rapport non trouvé pour l'ID {$id}"], Response::HTTP_NOT_FOUND);
        }

        // Désérialiser les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Mise à jour des informations du rapport à partir des données reçues
        $rapport->setEtatAnimal($data['etat_animal'] ?? $rapport->getEtatAnimal());
        $rapport->setNourriture($data['nourriture'] ?? $rapport->getNourriture());
        $rapport->setGrammage($data['grammage'] ?? $rapport->getGrammage());

        // Vérifier si la date_passage est présente et si c'est un string, sinon ne pas changer
        if (isset($data['date_passage']) && is_string($data['date_passage'])) {
            try {
                $rapport->setDatePassage(new \DateTime($data['date_passage']));
            } catch (\Exception $e) {
                return $this->json(['message' => 'Format de date invalide.'], Response::HTTP_BAD_REQUEST);
            }
        }

        // Sauvegarder les modifications dans la base de données
        $entityManager->flush();

        // Retourner un message de succès
        return $this->json(['message' => "Rapport mis à jour avec succès !"], Response::HTTP_OK);
    }

    // Suppression d'un rapport
    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        // Recherche du rapport dans la base de données par son identifiant
        $rapport = $this->repository->find($id);

        // Si le rapport n'est pas trouvé, retournez un message d'erreur avec le code 404
        if (!$rapport) {
            return $this->json(['message' => 'Rapport non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Supprimer le rapport de la base de données
        $this->manager->remove($rapport);
        $this->manager->flush();

        // Retourner un message de confirmation avec le code 200 (succès)
        return $this->json(
            ['message' => 'Rapport supprimé avec succès.'],
            Response::HTTP_OK
        );
    }

    // Liste tous les rapports vétérinaires
    #[Route('', name: 'index', methods: 'GET')]
    public function index(VeterinaireRapportRepository $veterinaireRapportRepository): JsonResponse
    {
        $rapports = $veterinaireRapportRepository->findAll();

        // Seules les informations basiques du rapport sont retournées
        $rapportsArray = [];
        foreach ($rapports as $rapport) {
            $rapportsArray[] = [
                'id' => $rapport->getId(),
                'etat_animal' => $rapport->getEtatAnimal(),
                'nourriture' => $rapport->getNourriture(),
                'grammage' => $rapport->getGrammage(),
                'date_passage' => $rapport->getDatePassage()->format('Y-m-d'),
            ];
        }

        return $this->json(['data' => $rapportsArray]);
    }
}
