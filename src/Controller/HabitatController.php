<?php

namespace App\Controller;

use App\Entity\Habitat;
use App\Repository\HabitatRepository;
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
