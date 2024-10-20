<?php

namespace App\Controller;

use App\Entity\Service;
use App\Repository\ServiceRepository;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/service', name: 'app_api_service_')]
class ServiceController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private ServiceRepository $repository,
        private SerializerInterface $serializer
    ) {
    }

    // POST - Ajouter un nouveau service
    #[Route(methods: ['POST'])]

    #[OA\Post(
        path: '/api/service',
        summary: "Ajouter un nouveau service",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données du service à ajouter",
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'nom_service', type: 'string', example: 'Visite Guidée'),
                    new OA\Property(property: 'description_service', type: 'string', example: 'Une visite guidée du zoo')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Service créé avec succès",
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Nouveau service créé avec succès!'),
                        new OA\Property(property: 'id', type: 'integer', example: 1)
                    ]
                )
            )
        ]
    )]

    public function new(Request $request): JsonResponse
    {
        // Désérialiser les données envoyées dans la requête en format JSON
        $service = $this->serializer->deserialize($request->getContent(), Service::class, 'json');

        // Enregistrer le service dans la base de données
        $this->manager->persist($service);
        $this->manager->flush();

        // Retourner un message de succès avec l'ID du service créé
        return new JsonResponse([
            'message' => 'Nouveau service créé avec succès!',
            'id' => $service->getId()
        ], Response::HTTP_CREATED);
    }

    // GET - Afficher les détails d'un service spécifique
    #[Route('/{id}', name: 'show', methods: ['GET'])]

    #[OA\Get(
        path: '/api/service/{id}',
        summary: "Afficher un service par ID",
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: "ID du service", schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Service trouvé avec succès",
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'nom_service', type: 'string', example: 'Visite Guidée'),
                        new OA\Property(property: 'description_service', type: 'string', example: 'Une visite guidée du zoo')
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Service non trouvé")
        ]
    )]

    public function show(int $id): JsonResponse
    {
        // Rechercher le service par son ID
        $service = $this->repository->findOneBy(['id' => $id]);

        if ($service) {
            // Préparer les données à retourner
            $responseData = [
                'id' => $service->getId(),
                'nom_service' => $service->getNomService(),
                'description_service' => $service->getDescriptionService()
            ];

            // Retourner un message de succès avec les données du service
            return new JsonResponse([
                'message' => 'Service trouvé avec succès.',
                'data' => $responseData
            ], Response::HTTP_OK);
        }

        // Si le service n'est pas trouvé, retourner un message d'erreur
        return new JsonResponse(['message' => 'Service non trouvé.'], Response::HTTP_NOT_FOUND);
    }

    // PUT - Mettre à jour un service existant
    #[Route('/{id}', name: 'edit', methods: ['PUT'])]

    #[OA\Put(
        path: '/api/service/{id}',
        summary: "Mettre à jour un service par ID",
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: "ID du service", schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Nouvelles données du service à mettre à jour",
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'nom_service', type: 'string', example: 'Visite Guidée Modifiée'),
                    new OA\Property(property: 'description_service', type: 'string', example: 'Visite guidée mise à jour')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Service mis à jour avec succès"),
            new OA\Response(response: 404, description: "Service non trouvé")
        ]
    )]

    public function edit(int $id, Request $request): JsonResponse
    {
        // Rechercher le service par son ID
        $service = $this->repository->findOneBy(['id' => $id]);

        if ($service) {
            // Récupérer les nouvelles données envoyées
            $data = json_decode($request->getContent(), true);

            // Mettre à jour les données du service
            $service->setNomService($data['nom_service'] ?? $service->getNomService());
            $service->setDescriptionService($data['description_service'] ?? $service->getDescriptionService());

            // Sauvegarder les modifications
            $this->manager->flush();

            // Retourner un message de succès
            return new JsonResponse(['message' => 'Service mis à jour avec succès!'], Response::HTTP_OK);
        }

        // Si le service n'est pas trouvé, retourner un message d'erreur
        return new JsonResponse(['message' => 'Service non trouvé.'], Response::HTTP_NOT_FOUND);
    }

    // DELETE - Supprimer un service
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]

    #[OA\Delete(
        path: '/api/service/{id}',
        summary: "Supprimer un service par ID",
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: "ID du service", schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: "Service supprimé avec succès"),
            new OA\Response(response: 404, description: "Service non trouvé")
        ]
    )]

    public function delete(int $id): JsonResponse
    {
        // Rechercher le service par son ID
        $service = $this->repository->findOneBy(['id' => $id]);
        if ($service) {
            // Supprimer le service de la base de données
            $this->manager->remove($service);
            $this->manager->flush();

            // Retourner un message de succès
            return new JsonResponse(['message' => 'Service supprimé avec succès.'], Response::HTTP_OK);
        }

        // Si le service n'est pas trouvé, retourner un message d'erreur
        return new JsonResponse(['message' => 'Service non trouvé.'], Response::HTTP_NOT_FOUND);
    }

    // GET - Liste tous les services
    #[Route(name: 'list', methods: ['GET'])]

    #[OA\Get(
        path: '/api/service',
        summary: "Liste tous les services",
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des services récupérée avec succès",
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'nom_service', type: 'string', example: 'Visite Guidée'),
                            new OA\Property(property: 'description_service', type: 'string', example: 'Une visite guidée du zoo')
                        ]
                    )
                )
            )
        ]
    )]

    public function list(): JsonResponse
    {
        // Récupérer tous les services de la base de données
        $services = $this->repository->findAll();
        $responseData = [];

        foreach ($services as $service) {
            // Ajouter chaque service à la réponse
            $responseData[] = [
                'id' => $service->getId(),
                'nom_service' => $service->getNomService(),
                'description_service' => $service->getDescriptionService()
            ];
        }

        // Retourner la liste des services
        return new JsonResponse([
            'message' => 'Liste des services récupérée avec succès.',
            'data' => $responseData
        ], Response::HTTP_OK);
    }
}
