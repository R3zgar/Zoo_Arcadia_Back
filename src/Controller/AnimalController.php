<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Repository\AnimalRepository;
use App\Repository\HabitatRepository;
use App\Service\MongoDBService;
use OpenApi\Attributes as OA;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/animal', name: 'app_api_animal_')]
class AnimalController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private AnimalRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        private MongoDBService $mongoDBService

    ) {
    }

    // POST - Ajouter un nouvel animal
    #[Route(methods: ['POST'])]

    #[OA\Post(
        path: '/api/animal',
        summary: "Ajouter un nouvel animal",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de l'animal à ajouter",
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'prenom_animal', type: 'string', example: 'Simba'),
                    new OA\Property(property: 'race_animal', type: 'string', example: 'Lion'),
                    new OA\Property(property: 'etat_animal', type: 'string', example: 'En bonne santé'),
                    new OA\Property(property: 'image', type: 'string', example: 'simba.jpg'),
                    new OA\Property(property: 'habitat_id', type: 'integer', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Animal créé avec succès",
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Nouvel animal créé avec succès!'),
                        new OA\Property(property: 'location', type: 'string', example: 'https://127.0.0.1:8000/api/animal/1')
                    ]
                )
            )
        ]
    )]

    public function new(Request $request, HabitatRepository $habitatRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérifier si le habitat_id existe dans les données
        if (!isset($data['habitat_id'])) {
            return new JsonResponse(['message' => 'ID de l\'habitat manquant ou invalide.'], Response::HTTP_BAD_REQUEST);
        }

        // Rechercher le habitat par ID
        $habitat = $habitatRepository->find($data['habitat_id']);
        if (!$habitat) {
            return new JsonResponse(['message' => 'Habitat non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Vérification pour s'assurer que le habitat_id correspond à l'habitat réel (validation pour habitat_id fixe)
        $validHabitats = [
            1 => 'Savane',
            2 => 'Jungle',
            3 => 'Marais'
        ];

        if (isset($validHabitats[$data['habitat_id']]) && $habitat->getNomHabitat() !== $validHabitats[$data['habitat_id']]) {
            return new JsonResponse(['message' => 'ID de l\'habitat ne correspond pas à l\'habitat réel.'], Response::HTTP_BAD_REQUEST);
        }

        // Créer un nouvel objet Animal à partir des données JSON
        $animal = $this->serializer->deserialize($request->getContent(), Animal::class, 'json');
        $animal->setHabitat($habitat);
        $animal->setCreatedAt(new DateTimeImmutable());

        // Enregistrement de l'animal dans MySQL
        $this->manager->persist($animal);
        $this->manager->flush();

        // Ajouter l'animal à MongoDB avec un view_count initial à 0
        $this->mongoDBService->incrementViewCount($animal->getPrenomAnimal());

        // Générer l'URL de l'élément créé
        $location = $this->urlGenerator->generate(
            'app_api_animal_show',
            ['id' => $animal->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Retourner un message de succès avec l'URL de l'animal créé
        return new JsonResponse([
            'message' => 'Nouvel animal créé avec succès!',
            'location' => $location
        ], Response::HTTP_CREATED);
    }


// GET - Afficher les détails d'un animal
    #[Route('/{id<\d+>}', name: 'show', methods: ['GET'])]

    #[OA\Get(
        path: '/api/animal/{id}',
        summary: "Afficher un animal par ID",
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: "ID de l'animal", schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Animal trouvé avec succès",
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'prenom_animal', type: 'string', example: 'Simba'),
                        new OA\Property(property: 'race_animal', type: 'string', example: 'Lion'),
                        new OA\Property(property: 'etat_animal', type: 'string', example: 'En bonne santé'),
                        new OA\Property(property: 'image', type: 'string', example: 'simba.jpg'),
                        new OA\Property(property: 'view_count', type: 'integer', example: 5),
                        new OA\Property(property: 'habitat', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'nom_habitat', type: 'string', example: 'Savane')
                        ])
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Animal non trouvé")
        ]
    )]

    public function show(int $id): JsonResponse
    {
        $animal = $this->repository->findOneBy(['id' => $id]);

        if ($animal) {
            // Incrémenter le compteur de vues dans MongoDB
            $this->mongoDBService->incrementViewCount($animal->getPrenomAnimal());
            $viewCount = $this->mongoDBService->getViewCount($animal->getPrenomAnimal());

            // Créer la réponse manuellement pour éviter les références circulaires
            $responseData = [
                'id' => $animal->getId(),
                'prenom_animal' => $animal->getPrenomAnimal(),
                'race_animal' => $animal->getRaceAnimal(),
                'etat_animal' => $animal->getEtatAnimal(),
                'image' => $animal->getImage(),
                'view_count' => $viewCount,
                'habitat' => [
                    'id' => $animal->getHabitat()->getId(),
                    'nom_habitat' => $animal->getHabitat()->getNomHabitat()
                ]
            ];

            // Retourner un message de succès avec les données de l'animal
            return new JsonResponse([
                'message' => 'Animal trouvé avec succès.',
                'data' => $responseData
            ], Response::HTTP_OK);
        }

        // Retourner un message d'erreur si l'animal n'a pas été trouvé
        return new JsonResponse(['message' => 'Animal non trouvé.'], Response::HTTP_NOT_FOUND);
    }

    // PUT - Mettre à jour un animal existant
    #[Route('/{id}', name: 'edit', methods: ['PUT'])]

    #[OA\Put(
        path: '/api/animal/{id}',
        summary: "Mettre à jour un animal par ID",
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: "ID de l'animal", schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Nouvelles données de l'animal à mettre à jour",
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'prenom_animal', type: 'string', example: 'Nouveau prénom'),
                    new OA\Property(property: 'race_animal', type: 'string', example: 'Nouvelle race'),
                    new OA\Property(property: 'etat_animal', type: 'string', example: 'Nouvel état'),
                    new OA\Property(property: 'image', type: 'string', example: 'nouvelle_image.jpg'),
                    new OA\Property(property: 'habitat_id', type: 'integer', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 204, description: "Animal mis à jour avec succès"),
            new OA\Response(response: 404, description: "Animal non trouvé")
        ]
    )]

    public function edit(int $id, Request $request, HabitatRepository $habitatRepository): JsonResponse
    {
        $animal = $this->repository->findOneBy(['id' => $id]);
        if ($animal) {
            $data = json_decode($request->getContent(), true);

            // Vérifier si le habitat_id existe dans les données pour la mise à jour
            if (isset($data['habitat_id'])) {
                $habitat = $habitatRepository->find($data['habitat_id']);
                if (!$habitat) {
                    return new JsonResponse(['message' => 'Habitat non trouvé.'], Response::HTTP_NOT_FOUND);
                }

                // Validation du habitat_id
                $validHabitats = [
                    1 => 'Savane',
                    2 => 'Jungle',
                    3 => 'Marais'
                ];

                if (isset($validHabitats[$data['habitat_id']]) && $habitat->getNomHabitat() !== $validHabitats[$data['habitat_id']]) {
                    return new JsonResponse(['message' => 'ID de l\'habitat ne correspond pas à l\'habitat réel.'], Response::HTTP_BAD_REQUEST);
                }

                $animal->setHabitat($habitat);
            }

            // Mise à jour des autres données de l'animal
            $oldAnimalName = $animal->getPrenomAnimal(); // Sauvegarder l'ancien nom pour mise à jour dans MongoDB
            $animal->setPrenomAnimal($data['prenom_animal']);
            $animal->setRaceAnimal($data['race_animal']);
            $animal->setEtatAnimal($data['etat_animal']);
            $animal->setImage($data['image']);
            $animal->setUpdatedAt(new DateTimeImmutable());

            $this->manager->flush();

            // Mettre à jour l'entrée dans MongoDB si le nom de l'animal a changé
            if ($oldAnimalName !== $data['prenom_animal']) {
                // Supprimer l'ancien enregistrement dans MongoDB
                $this->mongoDBService->deleteAnimalViewCount($oldAnimalName);
                // Ajouter le nouveau avec un view_count à 0
                $this->mongoDBService->incrementViewCount($animal->getPrenomAnimal());
            }

            // Retourner un message de succès
            return new JsonResponse(['message' => 'Animal mis à jour avec succès!'], Response::HTTP_OK);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }


    // DELETE - Supprimer un animal
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]

    #[OA\Delete(
        path: '/api/animal/{id}',
        summary: "Supprimer un animal par ID",
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: "ID de l'animal", schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: "Animal supprimé avec succès"),
            new OA\Response(response: 404, description: "Animal non trouvé")
        ]
    )]

    public function delete(int $id): JsonResponse
    {
        $animal = $this->repository->findOneBy(['id' => $id]);
        if ($animal) {
            $this->manager->remove($animal);
            $this->manager->flush();

            // Retourner un message de succès après suppression
            return new JsonResponse(['message' => 'Animal supprimé avec succès.'], Response::HTTP_OK);
        }

        // Retourner un message d'erreur si l'animal n'a pas été trouvé
        return new JsonResponse(['message' => 'Animal non trouvé.'], Response::HTTP_NOT_FOUND);
    }

    // GET - Liste tous les animaux
    #[Route(name: 'list', methods: ['GET'])]

    #[OA\Get(
        path: '/api/animal',
        summary: "Liste tous les animaux",
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des animaux récupérée avec succès",
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'prenom_animal', type: 'string', example: 'Simba'),
                            new OA\Property(property: 'race_animal', type: 'string', example: 'Lion'),
                            new OA\Property(property: 'etat_animal', type: 'string', example: 'En bonne santé'),
                            new OA\Property(property: 'image', type: 'string', example: 'simba.jpg'),
                            new OA\Property(property: 'habitat', type: 'object', properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'nom_habitat', type: 'string', example: 'Savane')
                            ])
                        ]
                    )
                )
            )
        ]
    )]

    public function list(): JsonResponse
    {
        $animals = $this->repository->findAll();
        $responseData = [];

        foreach ($animals as $animal) {

            $viewCount = $this->mongoDBService->getViewCount($animal->getPrenomAnimal());

            $responseData[] = [
                'id' => $animal->getId(),
                'prenom_animal' => $animal->getPrenomAnimal(),
                'race_animal' => $animal->getRaceAnimal(),
                'etat_animal' => $animal->getEtatAnimal(),
                'image' => $animal->getImage(),
                'view_count' => $viewCount,
                'habitat' => [
                    'id' => $animal->getHabitat()->getId(),
                    'nom_habitat' => $animal->getHabitat()->getNomHabitat()
                ]
            ];
        }

        // Retourner un message de succès avec la liste des animaux
        return new JsonResponse([
            'message' => 'Liste des animaux récupérée avec succès.',
            'data' => $responseData
        ], Response::HTTP_OK);
    }

    // Ajoute une nouvelle route pour synchroniser toutes les vues des animaux
    #[Route('/sync-all', name: 'sync_all_view_counts', methods: ['GET'])]
    public function syncAllViewCounts(): JsonResponse
    {
        // Récupère tous les animaux depuis la base de données MySQL
        $animals = $this->repository->findAll();

        // Parcourt chaque animal pour incrémenter le compteur de vues dans MongoDB
        foreach ($animals as $animal) {
            $this->mongoDBService->incrementViewCount($animal->getPrenomAnimal());
        }

        // Retourne un message de succès après la synchronisation
        return new JsonResponse(['message' => 'Tous les animaux ont été synchronisés avec MongoDB.'], Response::HTTP_OK);
    }
}
