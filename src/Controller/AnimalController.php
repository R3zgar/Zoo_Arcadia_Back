<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Repository\AnimalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/animal', name: 'app_api_animal_')]
class AnimalController extends AbstractController
{
    // GET : Liste tous les animaux
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(AnimalRepository $animalRepository): JsonResponse
    {
        // Récupérer tous les animaux
        $animals = $animalRepository->findAll();

        return $this->json([
            'data' => $animals
        ]);
    }

    // GET : Affiche un animal spécifique par ID
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(AnimalRepository $animalRepository, int $id): JsonResponse
    {
        // Rechercher l'animal par ID
        $animal = $animalRepository->find($id);

        // Si l'animal n'existe pas, renvoyer une erreur 404
        if (!$animal) {
            return $this->json(['message' => 'Animal non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'data' => $animal
        ]);
    }

    // POST : Créer un nouvel animal
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Décoder les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Créer un nouvel objet Animal
        $animal = new Animal();
        $animal->setPrenomAnimal($data['prenom_animal']);
        $animal->setRaceAnimal($data['race_animal']);
        $animal->setEtatAnimal($data['etat_animal']);
        $animal->setImage($data['image']);

        // Persister l'animal dans la base de données
        $entityManager->persist($animal);
        $entityManager->flush();

        return $this->json([
            'message' => 'Nouvel animal créé avec succès !',
            'data' => $animal
        ], Response::HTTP_CREATED);
    }

    // PUT : Mettre à jour un animal existant
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, EntityManagerInterface $entityManager, AnimalRepository $animalRepository, int $id): JsonResponse
    {
        // Rechercher l'animal par ID
        $animal = $animalRepository->find($id);

        // Si l'animal n'existe pas, renvoyer une erreur 404
        if (!$animal) {
            return $this->json(['message' => 'Animal non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Mettre à jour les informations de l'animal
        $data = json_decode($request->getContent(), true);
        $animal->setPrenomAnimal($data['prenom_animal'] ?? $animal->getPrenomAnimal());
        $animal->setRaceAnimal($data['race_animal'] ?? $animal->getRaceAnimal());
        $animal->setEtatAnimal($data['etat_animal'] ?? $animal->getEtatAnimal());
        $animal->setImage($data['image'] ?? $animal->getImage());

        // Enregistrer les modifications
        $entityManager->flush();

        return $this->json([
            'message' => 'Animal mis à jour avec succès !',
            'data' => $animal
        ]);
    }

    // DELETE : Supprimer un animal
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, AnimalRepository $animalRepository, int $id): JsonResponse
    {
        // Rechercher l'animal par ID
        $animal = $animalRepository->find($id);

        // Si l'animal n'existe pas, renvoyer une erreur 404
        if (!$animal) {
            return $this->json(['message' => 'Animal non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Supprimer l'animal de la base de données
        $entityManager->remove($animal);
        $entityManager->flush();

        return $this->json([
            'message' => 'Animal supprimé avec succès !'
        ]);
    }
}
