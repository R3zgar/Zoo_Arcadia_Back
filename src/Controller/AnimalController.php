<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Repository\AnimalRepository;
use App\Repository\HabitatRepository;
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
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    // POST - Ajouter un nouvel animal
    #[Route(methods: 'POST')]
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

        // Enregistrement de l'animal
        $this->manager->persist($animal);
        $this->manager->flush();

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
    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): JsonResponse
    {
        $animal = $this->repository->findOneBy(['id' => $id]);

        if ($animal) {
            // Créer la réponse manuellement pour éviter les références circulaires
            $responseData = [
                'id' => $animal->getId(),
                'prenom_animal' => $animal->getPrenomAnimal(),
                'race_animal' => $animal->getRaceAnimal(),
                'etat_animal' => $animal->getEtatAnimal(),
                'image' => $animal->getImage(),
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
    #[Route('/{id}', name: 'edit', methods: 'PUT')]
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
            $animal->setPrenomAnimal($data['prenom_animal']);
            $animal->setRaceAnimal($data['race_animal']);
            $animal->setEtatAnimal($data['etat_animal']);
            $animal->setImage($data['image']);
            $animal->setUpdatedAt(new DateTimeImmutable());

            $this->manager->flush();

            // Retourner un message de succès
            return new JsonResponse(['message' => 'Animal mis à jour avec succès!'], Response::HTTP_OK);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    // DELETE - Supprimer un animal
    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
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
    #[Route(name: 'list', methods: 'GET')]
    public function list(): JsonResponse
    {
        $animals = $this->repository->findAll();
        $responseData = [];

        foreach ($animals as $animal) {
            $responseData[] = [
                'id' => $animal->getId(),
                'prenom_animal' => $animal->getPrenomAnimal(),
                'race_animal' => $animal->getRaceAnimal(),
                'etat_animal' => $animal->getEtatAnimal(),
                'image' => $animal->getImage(),
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
}
