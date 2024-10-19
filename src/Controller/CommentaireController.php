<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Repository\AnimalRepository;
use App\Repository\CommentaireRepository;
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
    public function list(): JsonResponse
    {
        $commentaires = $this->repository->findAll();
        $responseData = [];

        foreach ($commentaires as $commentaire) {
            $responseData[] = [
                'id' => $commentaire->getId(),
                'auteur' => $commentaire->getAuteur(),
                'contenu' => $commentaire->getContenu(),
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
}
