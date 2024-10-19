<?php

namespace App\Controller;

use App\Entity\VeterinaireRapport;
use App\Repository\VeterinaireRapportRepository;
use App\Repository\AnimalRepository;
use OpenApi\Attributes as OA;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/veterinaire_rapport', name: 'app_api_veterinaire_rapport_')]
class VeterinaireRapportController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private VeterinaireRapportRepository $repository,
        private AnimalRepository $animalRepository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    // POST - Ajouter un nouveau rapport vétérinaire
    #[Route(methods: 'POST')]

    #[OA\Post(
        path: '/api/veterinaire_rapport',
        summary: "Ajouter un nouveau rapport vétérinaire",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données du rapport vétérinaire à ajouter",
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'etat_animal', type: 'string', example: 'En bonne santé'),
                    new OA\Property(property: 'nourriture', type: 'string', example: 'Viande'),
                    new OA\Property(property: 'grammage', type: 'integer', example: 500),
                    new OA\Property(property: 'date_passage', type: 'string', example: '2024-10-15'),
                    new OA\Property(property: 'animal_id', type: 'integer', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Rapport vétérinaire créé avec succès",
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Nouveau rapport vétérinaire créé avec succès!'),
                        new OA\Property(property: 'location', type: 'string', example: 'https://127.0.0.1:8000/api/veterinaire_rapport/1')
                    ]
                )
            )
        ]
    )]

    public function new(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérifier si l'animal_id existe dans les données
        if (!isset($data['animal_id'])) {
            return new JsonResponse(['message' => 'ID de l\'animal manquant ou invalide.'], Response::HTTP_BAD_REQUEST);
        }

        // Rechercher l'animal par ID
        $animal = $this->animalRepository->find($data['animal_id']);
        if (!$animal) {
            return new JsonResponse(['message' => 'Animal non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Créer un nouvel objet rapport vétérinaire à partir des données JSON
        $rapport = $this->serializer->deserialize($request->getContent(), VeterinaireRapport::class, 'json');
        $rapport->setAnimal($animal);
        $rapport->setCreatedAt(new DateTimeImmutable());
        $rapport->setUpdatedAt(new DateTimeImmutable());

        // Enregistrement du rapport vétérinaire
        $this->manager->persist($rapport);
        $this->manager->flush();

        // Générer l'URL de l'élément créé
        $location = $this->urlGenerator->generate(
            'app_api_veterinaire_rapport_show',
            ['id' => $rapport->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Retourner un message de succès avec l'URL du rapport créé
        return new JsonResponse([
            'message' => 'Nouveau rapport vétérinaire créé avec succès!',
            'location' => $location
        ], Response::HTTP_CREATED);
    }

    // GET - Afficher les détails d'un rapport vétérinaire
    #[Route('/{id}', name: 'show', methods: 'GET')]

    #[OA\Get(
        path: '/api/veterinaire_rapport/{id}',
        summary: "Afficher un rapport vétérinaire par ID",
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: "ID du rapport vétérinaire", schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Rapport vétérinaire trouvé avec succès",
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'etat_animal', type: 'string', example: 'En bonne santé'),
                        new OA\Property(property: 'nourriture', type: 'string', example: 'Viande'),
                        new OA\Property(property: 'grammage', type: 'integer', example: 500),
                        new OA\Property(property: 'date_passage', type: 'string', example: '2024-10-15'),
                        new OA\Property(property: 'createdAt', type: 'string', example: '2024-10-19 12:34:56'),
                        new OA\Property(property: 'updatedAt', type: 'string', example: '2024-10-20 12:34:56')
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Rapport vétérinaire non trouvé")
        ]
    )]

    public function show(int $id): JsonResponse
    {
        $rapport = $this->repository->findOneBy(['id' => $id]);

        if ($rapport) {
            // Créer la réponse manuellement pour éviter les références circulaires
            $responseData = [
                'id' => $rapport->getId(),
                'etat_animal' => $rapport->getEtatAnimal(),
                'nourriture' => $rapport->getNourriture(),
                'grammage' => $rapport->getGrammage(),
                'date_passage' => $rapport->getDatePassage()->format('Y-m-d'),
                'createdAt' => $rapport->getCreatedAt(),
                'updatedAt' => $rapport->getUpdatedAt()
            ];

            // Retourner un message de succès avec les données du rapport
            return new JsonResponse([
                'message' => 'Rapport vétérinaire trouvé avec succès.',
                'data' => $responseData
            ], Response::HTTP_OK);
        }

        // Retourner un message d'erreur si le rapport n'a pas été trouvé
        return new JsonResponse(['message' => 'Rapport vétérinaire non trouvé.'], Response::HTTP_NOT_FOUND);
    }

    // PUT - Mettre à jour un rapport vétérinaire existant
    #[Route('/{id}', name: 'edit', methods: 'PUT')]

    #[OA\Put(
        path: '/api/veterinaire_rapport/{id}',
        summary: "Mettre à jour un rapport vétérinaire par ID",
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: "ID du rapport vétérinaire", schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Nouvelles données du rapport vétérinaire à mettre à jour",
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'etat_animal', type: 'string', example: 'En meilleure santé'),
                    new OA\Property(property: 'nourriture', type: 'string', example: 'Viande de poulet'),
                    new OA\Property(property: 'grammage', type: 'integer', example: 600),
                    new OA\Property(property: 'date_passage', type: 'string', example: '2024-10-16')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Rapport vétérinaire mis à jour avec succès"),
            new OA\Response(response: 404, description: "Rapport vétérinaire non trouvé")
        ]
    )]

    public function edit(int $id, Request $request): JsonResponse
    {
        $rapport = $this->repository->findOneBy(['id' => $id]);
        if ($rapport) {
            $data = json_decode($request->getContent(), true);

            // Mise à jour des autres données du rapport
            $rapport->setEtatAnimal($data['etat_animal'] ?? $rapport->getEtatAnimal());
            $rapport->setNourriture($data['nourriture'] ?? $rapport->getNourriture());
            $rapport->setGrammage($data['grammage'] ?? $rapport->getGrammage());

            // Mise à jour de la date_passage si elle est présente et valide
            if (isset($data['date_passage']) && is_string($data['date_passage'])) {
                try {
                    $rapport->setDatePassage(new \DateTime($data['date_passage']));
                } catch (\Exception $e) {
                    return new JsonResponse(['message' => 'Format de date invalide.'], Response::HTTP_BAD_REQUEST);
                }
            }

            // Mise à jour du champ updatedAt
            $rapport->setUpdatedAt(new DateTimeImmutable());

            $this->manager->flush();

            // Retourner un message de succès
            return new JsonResponse(['message' => 'Rapport vétérinaire mis à jour avec succès!'], Response::HTTP_OK);
        }

        return new JsonResponse(['message' => 'Rapport vétérinaire non trouvé.'], Response::HTTP_NOT_FOUND);
    }

    // DELETE - Supprimer un rapport vétérinaire
    #[Route('/{id}', name: 'delete', methods: 'DELETE')]

    #[OA\Delete(
        path: '/api/veterinaire_rapport/{id}',
        summary: "Supprimer un rapport vétérinaire par ID",
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: "ID du rapport vétérinaire", schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: "Rapport vétérinaire supprimé avec succès"),
            new OA\Response(response: 404, description: "Rapport vétérinaire non trouvé")
        ]
    )]

    public function delete(int $id): JsonResponse
    {
        $rapport = $this->repository->findOneBy(['id' => $id]);
        if ($rapport) {
            $this->manager->remove($rapport);
            $this->manager->flush();

            // Retourner un message de succès après suppression
            return new JsonResponse(['message' => 'Rapport vétérinaire supprimé avec succès.'], Response::HTTP_OK);
        }

        // Retourner un message d'erreur si le rapport n'a pas été trouvé
        return new JsonResponse(['message' => 'Rapport vétérinaire non trouvé.'], Response::HTTP_NOT_FOUND);
    }

    // GET - Liste tous les rapports vétérinaires
    #[Route(name: 'list', methods: 'GET')]

    #[OA\Get(
        path: '/api/veterinaire_rapport',
        summary: "Liste tous les rapports vétérinaires",
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des rapports vétérinaires récupérée avec succès",
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'etat_animal', type: 'string', example: 'En bonne santé'),
                            new OA\Property(property: 'nourriture', type: 'string', example: 'Viande'),
                            new OA\Property(property: 'grammage', type: 'integer', example: 500),
                            new OA\Property(property: 'date_passage', type: 'string', example: '2024-10-15'),
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
        $rapports = $this->repository->findAll();
        $responseData = [];

        foreach ($rapports as $rapport) {
            $responseData[] = [
                'id' => $rapport->getId(),
                'etat_animal' => $rapport->getEtatAnimal(),
                'nourriture' => $rapport->getNourriture(),
                'grammage' => $rapport->getGrammage(),
                'date_passage' => $rapport->getDatePassage()->format('Y-m-d'),
                'createdAt' => $rapport->getCreatedAt(),
                'updatedAt' => $rapport->getUpdatedAt()
            ];
        }

        // Retourner un message de succès avec la liste des rapports vétérinaires
        return new JsonResponse([
            'message' => 'Liste des rapports vétérinaires récupérée avec succès.',
            'data' => $responseData
        ], Response::HTTP_OK);
    }
}
