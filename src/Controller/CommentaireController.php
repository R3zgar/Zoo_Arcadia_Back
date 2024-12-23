<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Repository\AnimalRepository;
use App\Repository\CommentaireRepository;
use OpenApi\Attributes as OA;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/commentaire', name: 'app_api_commentaire_')]
class CommentaireController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private CommentaireRepository $repository,
        private SerializerInterface $serializer
    ) {
    }

    // POST - Ajouter un nouveau commentaire
    #[Route(methods: 'POST')]

    #[OA\Post(
        path: '/api/commentaire',
        summary: "Ajouter un nouveau commentaire",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données du commentaire à ajouter",
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'auteur', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'contenu', type: 'string', example: 'Très bel endroit !'),
                    new OA\Property(property: 'animal_id', type: 'integer', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Commentaire créé avec succès",
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Nouveau commentaire créé avec succès!'),
                        new OA\Property(property: 'id', type: 'integer', example: 1)
                    ]
                )
            )
        ]
    )]

    public function new(Request $request, AnimalRepository $animalRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérifier si l'animal_id est présent dans les données
        if (!isset($data['animal_id'])) {
            return new JsonResponse(['message' => 'ID de l\'animal manquant ou invalide.'], Response::HTTP_BAD_REQUEST);
        }

        // Contrôle pour vérifier que l'animal_id est valide (seulement 1, 2 ou 3)
        if (!in_array($data['animal_id'], [1, 2, 3])) {
            return new JsonResponse(['message' => 'ID de l\'animal non valide. Seulement les animaux dans les habitats Savane (1), Jungle (2) et Marais (3) sont permis.'], Response::HTTP_BAD_REQUEST);
        }

        // Rechercher l'animal par ID
        $animal = $animalRepository->find($data['animal_id']);
        if (!$animal) {
            return new JsonResponse(['message' => 'Animal non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Créer un nouvel objet Commentaire à partir des données JSON
        $commentaire = $this->serializer->deserialize($request->getContent(), Commentaire::class, 'json');
        $commentaire->setAnimal($animal);
        $commentaire->setCreatedAt(new DateTimeImmutable());

        // Définir le statut par défaut à "En attente"
        $commentaire->setStatus('En attente');

        // Enregistrer le commentaire
        $this->manager->persist($commentaire);
        $this->manager->flush();

        // Retourner un message de succès avec l'ID du commentaire créé
        return new JsonResponse([
            'message' => 'Nouveau commentaire créé avec succès!',
            'id' => $commentaire->getId()
        ], Response::HTTP_CREATED);
    }

    // GET - Afficher les détails d'un commentaire spécifique
    #[Route('/{id}', name: 'show', methods: 'GET')]

    #[OA\Get(
        path: '/api/commentaire/{id}',
        summary: "Afficher un commentaire par ID",
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: "ID du commentaire", schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Commentaire trouvé avec succès",
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'auteur', type: 'string', example: 'John Doe'),
                        new OA\Property(property: 'contenu', type: 'string', example: 'Très bel endroit !'),
                        new OA\Property(property: 'animal', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'prenom_animal', type: 'string', example: 'Simba')
                        ])
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Commentaire non trouvé")
        ]
    )]

    public function show(int $id): JsonResponse
    {
        $commentaire = $this->repository->findOneBy(['id' => $id]);

        if ($commentaire) {
            // Créer la réponse pour éviter les références circulaires
            $responseData = [
                'id' => $commentaire->getId(),
                'auteur' => $commentaire->getAuteur(),
                'contenu' => $commentaire->getContenu(),
                'created_at' => $commentaire->getCreatedAt()->format('Y-m-d H:i:s'),
                'animal' => [
                    'id' => $commentaire->getAnimal()->getId(),
                    'prenom_animal' => $commentaire->getAnimal()->getPrenomAnimal()
                ]
            ];

            return new JsonResponse([
                'message' => 'Commentaire trouvé avec succès.',
                'data' => $responseData
            ], Response::HTTP_OK);
        }

        return new JsonResponse(['message' => 'Commentaire non trouvé.'], Response::HTTP_NOT_FOUND);
    }

    // DELETE - Supprimer un commentaire
    #[Route('/{id}', name: 'delete', methods: 'DELETE')]

    #[OA\Delete(
        path: '/api/commentaire/{id}',
        summary: "Supprimer un commentaire par ID",
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: "ID du commentaire", schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: "Commentaire supprimé avec succès"),
            new OA\Response(response: 404, description: "Commentaire non trouvé")
        ]
    )]

    public function delete(int $id): JsonResponse
    {
        $commentaire = $this->repository->findOneBy(['id' => $id]);
        if ($commentaire) {
            $this->manager->remove($commentaire);
            $this->manager->flush();

            // Retourner un message de succès après suppression
            return new JsonResponse(['message' => 'Commentaire supprimé avec succès.'], Response::HTTP_OK);
        }

        return new JsonResponse(['message' => 'Commentaire non trouvé.'], Response::HTTP_NOT_FOUND);
    }



    // GET - Liste tous les commentaires
    #[Route(name: 'list', methods: 'GET')]

    #[OA\Get(
        path: '/api/commentaire',
        summary: "Liste tous les commentaires",
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des commentaires récupérée avec succès",
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'auteur', type: 'string', example: 'John Doe'),
                            new OA\Property(property: 'contenu', type: 'string', example: 'Très bel endroit !'),
                            new OA\Property(property: 'created_at', type: 'string', example: '2024-10-19 12:34:56'),
                            new OA\Property(property: 'animal', type: 'object', properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'prenom_animal', type: 'string', example: 'Simba')
                            ])
                        ]
                    )
                )
            )
        ]
    )]

    public function list(): JsonResponse
    {
        $commentaires = $this->repository->findAll();
        $responseData = [];

        foreach ($commentaires as $commentaire) {
            $responseData[] = [
                'id' => $commentaire->getId(),
                'auteur' => $commentaire->getAuteur(),
                'contenu' => $commentaire->getContenu(),
                'status' => $commentaire->getStatus(),
                'created_at' => $commentaire->getCreatedAt()->format('Y-m-d H:i:s'),
                'animal' => [
                    'id' => $commentaire->getAnimal()->getId(),
                    'prenom_animal' => $commentaire->getAnimal()->getPrenomAnimal()
                ]
            ];
        }

        // Retourner un message de succès avec la liste des commentaires
        return new JsonResponse([
            'message' => 'Liste des commentaires récupérée avec succès.',
            'data' => $responseData
        ], Response::HTTP_OK);
    }

// PATCH - Approuver un commentaire
    #[Route('/{id}/approve', name: 'approve', methods: ['PATCH'])]
    public function approve(int $id): JsonResponse
    {
        $commentaire = $this->repository->find($id);

        // Vérification si le commentaire existe
        if (!$commentaire) {
            return new JsonResponse(['message' => 'Commentaire non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Mettre à jour le statut du commentaire en français
        $commentaire->setStatus('Approuvé');
        $this->manager->flush();

        return new JsonResponse([
            'message' => 'Commentaire approuvé avec succès!',
            'id' => $commentaire->getId()
        ], Response::HTTP_OK);
    }


}
