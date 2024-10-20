<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Repository\ConsultationRepository;
use App\Repository\AnimalRepository;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/consultation', name: 'app_api_consultation_')]
class ConsultationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private ConsultationRepository $repository,
        private SerializerInterface $serializer
    ) {
    }

    // POST - Ajouter une nouvelle consultation
    #[Route(methods: ['POST'])]

    #[OA\Post(
        path: '/api/consultation',
        summary: "Ajouter une nouvelle consultation",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de la consultation à ajouter",
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'compteur', type: 'integer', example: 5),
                    new OA\Property(property: 'id_animal', type: 'string', example: '1')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Consultation créée avec succès",
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Nouvelle consultation créée avec succès!'),
                        new OA\Property(property: 'id', type: 'integer', example: 1)
                    ]
                )
            )
        ]
    )]

    public function new(Request $request, AnimalRepository $animalRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérifier si l'id_animal est présent dans les données
        if (!isset($data['id_animal'])) {
            return new JsonResponse(['message' => 'ID de l\'animal manquant ou invalide.'], Response::HTTP_BAD_REQUEST);
        }

        // Rechercher l'animal par ID
        $animal = $animalRepository->find($data['id_animal']);
        if (!$animal) {
            return new JsonResponse(['message' => 'Animal non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Créer un nouvel objet Consultation à partir des données JSON
        $consultation = $this->serializer->deserialize($request->getContent(), Consultation::class, 'json');

        // Enregistrer la consultation
        $this->manager->persist($consultation);
        $this->manager->flush();

        // Retourner un message de succès avec l'ID de la consultation créée
        return new JsonResponse([
            'message' => 'Nouvelle consultation créée avec succès!',
            'id' => $consultation->getId()
        ], Response::HTTP_CREATED);
    }

    // GET - Afficher les détails d'une consultation spécifique
    #[Route('/{id}', name: 'show', methods: ['GET'])]

    #[OA\Get(
        path: '/api/consultation/{id}',
        summary: "Afficher une consultation par ID",
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: "ID de la consultation", schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Consultation trouvée avec succès",
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'compteur', type: 'integer', example: 5),
                        new OA\Property(property: 'id_animal', type: 'string', example: '1')
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Consultation non trouvée")
        ]
    )]

    public function show(int $id): JsonResponse
    {
        $consultation = $this->repository->findOneBy(['id' => $id]);

        if ($consultation) {
            // Créer la réponse manuellement
            $responseData = [
                'id' => $consultation->getId(),
                'compteur' => $consultation->getCompteur(),
                'id_animal' => $consultation->getIdAnimal()
            ];

            return new JsonResponse([
                'message' => 'Consultation trouvée avec succès.',
                'data' => $responseData
            ], Response::HTTP_OK);
        }

        return new JsonResponse(['message' => 'Consultation non trouvée.'], Response::HTTP_NOT_FOUND);
    }

    // GET - Liste toutes les consultations
    #[Route(name: 'list', methods: ['GET'])]

    #[OA\Get(
        path: '/api/consultation',
        summary: "Liste toutes les consultations",
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des consultations récupérée avec succès",
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'compteur', type: 'integer', example: 5),
                            new OA\Property(property: 'id_animal', type: 'string', example: '1')
                        ]
                    )
                )
            )
        ]
    )]

    public function list(): JsonResponse
    {
        $consultations = $this->repository->findAll();
        $responseData = [];

        foreach ($consultations as $consultation) {
            $responseData[] = [
                'id' => $consultation->getId(),
                'compteur' => $consultation->getCompteur(),
                'id_animal' => $consultation->getIdAnimal()
            ];
        }

        return new JsonResponse([
            'message' => 'Liste des consultations récupérée avec succès.',
            'data' => $responseData
        ], Response::HTTP_OK);
    }
}
