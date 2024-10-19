<?php

namespace App\Controller;

use App\Entity\Habitat;
use App\Repository\HabitatRepository;
use OpenApi\Attributes as OA;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/habitat', name: 'app_api_habitat_')]
class HabitatController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private HabitatRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    // POST - Ajouter un nouvel habitat
    #[Route(methods: 'POST')]

    #[OA\Post(
        path: '/api/habitat',
        summary: "Ajouter un nouvel habitat",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de l'habitat à ajouter",
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'nom_habitat', type: 'string', example: 'Savane'),
                    new OA\Property(property: 'description_habitat', type: 'string', example: 'Vaste étendue de savane avec une faune diversifiée.'),
                    new OA\Property(property: 'image', type: 'string', example: 'savane.jpg')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Habitat créé avec succès",
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Nouvel habitat créé avec succès!'),
                        new OA\Property(property: 'location', type: 'string', example: 'https://127.0.0.1:8000/api/habitat/1')
                    ]
                )
            )
        ]
    )]

    public function new(Request $request): JsonResponse
    {
        // Désérialiser les données JSON envoyées dans la requête
        $habitat = $this->serializer->deserialize($request->getContent(), Habitat::class, 'json');
        $habitat->setCreatedAt(new DateTimeImmutable()); // Ajout de createdAt

        // Enregistrement de l'habitat
        $this->manager->persist($habitat);
        $this->manager->flush();

        // Générer l'URL de l'élément créé
        $location = $this->urlGenerator->generate(
            'app_api_habitat_show',
            ['id' => $habitat->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Retourner un message de succès avec l'URL de l'habitat créé
        return new JsonResponse([
            'message' => 'Nouvel habitat créé avec succès!',
            'location' => $location
        ], Response::HTTP_CREATED);
    }

    // GET - Afficher les détails d'un habitat
    #[Route('/{id}', name: 'show', methods: 'GET')]

    #[OA\Get(
        path: '/api/habitat/{id}',
        summary: "Afficher un habitat par ID",
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: "ID de l'habitat", schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Habitat trouvé avec succès",
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'nom_habitat', type: 'string', example: 'Savane'),
                        new OA\Property(property: 'description_habitat', type: 'string', example: 'Vaste étendue de savane avec une faune diversifiée.'),
                        new OA\Property(property: 'image', type: 'string', example: 'savane.jpg'),
                        new OA\Property(property: 'createdAt', type: 'string', example: '2024-10-19 12:34:56'),
                        new OA\Property(property: 'updatedAt', type: 'string', example: '2024-10-20 12:34:56')
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Habitat non trouvé")
        ]
    )]

    public function show(int $id): JsonResponse
    {
        $habitat = $this->repository->findOneBy(['id' => $id]);

        if ($habitat) {
            // Créer la réponse manuellement pour éviter les références circulaires
            $responseData = [
                'id' => $habitat->getId(),
                'nom_habitat' => $habitat->getNomHabitat(),
                'description_habitat' => $habitat->getDescriptionHabitat(),
                'image' => $habitat->getImage(),
                'createdAt' => $habitat->getCreatedAt(), // Ajout de createdAt
                'updatedAt' => $habitat->getUpdatedAt()  // Ajout de updatedAt
            ];

            // Retourner un message de succès avec les données de l'habitat
            return new JsonResponse([
                'message' => 'Habitat trouvé avec succès.',
                'data' => $responseData
            ], Response::HTTP_OK);
        }

        // Retourner un message d'erreur si l'habitat n'a pas été trouvé
        return new JsonResponse(['message' => 'Habitat non trouvé.'], Response::HTTP_NOT_FOUND);
    }

    // PUT - Mettre à jour un habitat existant
    #[Route('/{id}', name: 'edit', methods: 'PUT')]


    #[OA\Put(
        path: '/api/habitat/{id}',
        summary: "Mettre à jour un habitat par ID",
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: "ID de l'habitat", schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Nouvelles données de l'habitat à mettre à jour",
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'nom_habitat', type: 'string', example: 'Savane'),
                    new OA\Property(property: 'description_habitat', type: 'string', example: 'Nouvelle description'),
                    new OA\Property(property: 'image', type: 'string', example: 'nouvelle_image.jpg')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Habitat mis à jour avec succès"),
            new OA\Response(response: 404, description: "Habitat non trouvé")
        ]
    )]

    public function edit(int $id, Request $request): JsonResponse
    {
        $habitat = $this->repository->findOneBy(['id' => $id]);
        if ($habitat) {
            // Désérialiser les données JSON envoyées dans la requête
            $data = json_decode($request->getContent(), true);

            // Mise à jour des données de l'habitat
            $habitat->setNomHabitat($data['nom_habitat']);
            $habitat->setDescriptionHabitat($data['description_habitat']);
            $habitat->setImage($data['image']);
            $habitat->setUpdatedAt(new DateTimeImmutable()); // Ajout de updatedAt

            $this->manager->flush();

            // Retourner un message de succès
            return new JsonResponse(['message' => 'Habitat mis à jour avec succès!'], Response::HTTP_OK);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    // DELETE - Supprimer un habitat
    #[Route('/{id}', name: 'delete', methods: 'DELETE')]

    #[OA\Delete(
        path: '/api/habitat/{id}',
        summary: "Supprimer un habitat par ID",
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: "ID de l'habitat", schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: "Habitat supprimé avec succès"),
            new OA\Response(response: 404, description: "Habitat non trouvé")
        ]
    )]

    public function delete(int $id): JsonResponse
    {
        $habitat = $this->repository->findOneBy(['id' => $id]);
        if ($habitat) {
            $this->manager->remove($habitat);
            $this->manager->flush();

            // Retourner un message de succès après suppression
            return new JsonResponse(['message' => 'Habitat supprimé avec succès.'], Response::HTTP_OK);
        }

        // Retourner un message d'erreur si l'habitat n'a pas été trouvé
        return new JsonResponse(['message' => 'Habitat non trouvé.'], Response::HTTP_NOT_FOUND);
    }

    // GET - Liste tous les habitats
    #[Route(name: 'list', methods: 'GET')]

    #[OA\Get(
        path: '/api/habitat',
        summary: "Liste tous les habitats",
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des habitats récupérée avec succès",
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'nom_habitat', type: 'string', example: 'Savane'),
                            new OA\Property(property: 'description_habitat', type: 'string', example: 'Vaste étendue de savane avec une faune diversifiée.'),
                            new OA\Property(property: 'image', type: 'string', example: 'savane.jpg'),
                            new OA\Property(property: 'createdAt', type: 'string', example: '2024-10-19 12:34:56'),
                            new OA\Property(property: 'updatedAt', type: 'string', example: '2024-10-20 12:34:56')
                        ]
                    )
                )
            )
        ]
    )]

    public function list(): JsonResponse
    {
        $habitats = $this->repository->findAll();
        $responseData = [];

        foreach ($habitats as $habitat) {
            $responseData[] = [
                'id' => $habitat->getId(),
                'nom_habitat' => $habitat->getNomHabitat(),
                'description_habitat' => $habitat->getDescriptionHabitat(),
                'image' => $habitat->getImage(),
                'createdAt' => $habitat->getCreatedAt(), // Ajout de createdAt
                'updatedAt' => $habitat->getUpdatedAt()  // Ajout de updatedAt
            ];
        }

        // Retourner un message de succès avec la liste des habitats
        return new JsonResponse([
            'message' => 'Liste des habitats récupérée avec succès.',
            'data' => $responseData
        ], Response::HTTP_OK);
    }
}
