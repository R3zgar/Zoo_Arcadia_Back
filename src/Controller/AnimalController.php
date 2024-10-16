<?php


namespace App\Controller;

use App\Entity\Animal;
use App\Repository\AnimalRepository;
use App\Repository\HabitatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



#[Route('api/animal', name: 'app_api_animal')]

class AnimalController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private AnimalRepository $repository)
    {
    }

    // Create
    #[Route(name: 'new', methods: 'POST')]
    public function new(Request $request, HabitatRepository $habitatRepository): Response
    {
        // Décoder les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Rechercher le habitat par ID (assuré que l'habitat_id est envoyé dans les données)
        $habitat = $habitatRepository->find($data['habitat_id']);

        // Si le habitat n'existe pas, retourner une erreur
        if (!$habitat) {
            return $this->json(['message' => 'Habitat non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Créer un nouvel objet Animal
        $animal = new Animal();
        $animal->setPrenomAnimal($data['prenom_animal'] ?? 'Tigre');
        $animal->setRaceAnimal($data['race_animal'] ?? 'Tigeria');
        $animal->setEtatAnimal($data['etat_animal'] ?? 'Sain');
        $animal->setImage($data['image'] ?? 'tiger.jpg');
        $animal->setHabitat($habitat); // Associer l'habitat ici

        // Persister l'animal dans la base de données
        $this->manager->persist($animal);
        $this->manager->flush();

        // Retourner un message de succès
        return $this->json(
            ['message' => "Nouvel animal créé avec succès avec l'id {$animal->getId()}"],
            Response::HTTP_CREATED,
        );
    }


// Lire (afficher un animal spécifique)
    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id, AnimalRepository $animalRepository): Response
    {
        // Trouver l'animal dans la base de données par son ID
        $animal = $animalRepository->find($id);

        // Si l'animal n'est pas trouvé, retourner une exception
        if (!$animal) {
            throw new \Exception("Animal non trouvé pour l'ID {$id}");
        }

        // Retourner les informations de l'animal sous forme de JSON
        return $this->json(
            ['message' => "Un Animal trouvé : {$animal->getPrenomAnimal()} pour l'ID {$animal->getId()}"]
        );
    }


    // Mettre à jour un animal existant
    #[Route('/{id}', name: 'update', methods: 'PUT')]
    public function update(int $id, Request $request, AnimalRepository $animalRepository, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'ID est bien un entier valide
        if (!is_int($id)) {
            return $this->json(['message' => "L'ID doit être un entier valide."], Response::HTTP_BAD_REQUEST);
        }

        // Rechercher l'animal par ID dans la base de données
        $animal = $animalRepository->find($id);

        // Si l'animal n'existe pas, renvoyer une erreur 404
        if (!$animal) {
            return $this->json(['message' => "Animal non trouvé pour l'ID {$id}"], Response::HTTP_NOT_FOUND);
        }

        // Décoder les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Mettre à jour les informations de l'animal
        $animal->setPrenomAnimal($data['prenom_animal'] ?? $animal->getPrenomAnimal());
        $animal->setRaceAnimal($data['race_animal'] ?? $animal->getRaceAnimal());
        $animal->setEtatAnimal($data['etat_animal'] ?? $animal->getEtatAnimal());
        $animal->setImage($data['image'] ?? $animal->getImage());

        // Sauvegarder les modifications dans la base de données
        $entityManager->flush();

        // Retourner un message de succès
        return $this->json(['message' => "Animal mis à jour avec succès !"], Response::HTTP_OK);
    }



// Suppression d'un animal
    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        // Recherche de l'animal dans la base de données par son identifiant
        $animal = $this->repository->find($id);

        // Si l'animal n'est pas trouvé, retournez un message d'erreur avec le code 404
        if (!$animal) {
            return $this->json(['message' => 'Animal non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Supprimer l'animal de la base de données
        $this->manager->remove($animal);
        $this->manager->flush();

        // Retourner un message de confirmation avec le code 200 (succès)
        return $this->json(
            ['message' => 'Animal supprimé avec succès.'],
            Response::HTTP_OK // Statut 200 OK
        );
    }


}


//
//namespace App\Controller;
//
//use App\Entity\Animal;
//use App\Repository\AnimalRepository;
//use Doctrine\ORM\EntityManagerInterface;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\JsonResponse;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\Routing\Annotation\Route;
//
//#[Route('/api/animal', name: 'app_api_animal_')]
//class AnimalController extends AbstractController
//{
//    public function __construct(private EntityManagerInterface $entityManager, private AnimalRepository $animalRepository)
//    {
//    }
//
//    // GET : Liste tous les animaux
//    #[Route('', name: 'index', methods: ['GET'])]
//    public function index(): JsonResponse
//    {
//        // Récupérer tous les animaux dans la base de données
//        $animals = $this->animalRepository->findAll();
//
//        // Retourner les données sous forme de JSON
//        return $this->json([
//            'data' => $animals
//        ]);
//    }
//
//    // GET : Affiche un animal spécifique par ID
//    #[Route('/{id}', name: 'show', methods: ['GET'])]
//    public function show(int $id): JsonResponse
//    {
//        // Rechercher l'animal par ID dans la base de données
//        $animal = $this->animalRepository->find($id);
//
//        // Si l'animal n'existe pas, renvoyer une erreur 404
//        if (!$animal) {
//            return $this->json(['message' => 'Animal non trouvé.'], Response::HTTP_NOT_FOUND);
//        }
//
//        // Retourner les détails de l'animal
//        return $this->json([
//            'data' => $animal
//        ]);
//    }
//
//    // POST : Créer un nouvel animal
//    #[Route('', name: 'create', methods: ['POST'])]
//    public function create(Request $request): JsonResponse
//    {
//        // Décoder les données JSON envoyées dans la requête
//        $data = json_decode($request->getContent(), true);
//
//        // Créer un nouvel objet Animal
//        $animal = new Animal();
//        $animal->setPrenomAnimal($data['prenom_animal']);
//        $animal->setRaceAnimal($data['race_animal']);
//        $animal->setEtatAnimal($data['etat_animal']);
//        $animal->setImage($data['image']);
//
//        // Persister l'animal dans la base de données
//        $this->entityManager->persist($animal);
//        $this->entityManager->flush();
//
//        // Retourner un message de succès
//        return $this->json([
//            'message' => 'Nouvel animal créé avec succès !',
//            'data' => $animal
//        ], Response::HTTP_CREATED);
//    }
//
//    // PUT : Mettre à jour un animal existant
//    #[Route('/{id}', name: 'update', methods: ['PUT'])]
//    public function update(Request $request, int $id): JsonResponse
//    {
//        // Rechercher l'animal par ID dans la base de données
//        $animal = $this->animalRepository->find($id);
//
//        // Si l'animal n'existe pas, renvoyer une erreur 404
//        if (!$animal) {
//            return $this->json(['message' => 'Animal non trouvé.'], Response::HTTP_NOT_FOUND);
//        }
//
//        // Mettre à jour les informations de l'animal avec les données reçues
//        $data = json_decode($request->getContent(), true);
//        $animal->setPrenomAnimal($data['prenom_animal'] ?? $animal->getPrenomAnimal());
//        $animal->setRaceAnimal($data['race_animal'] ?? $animal->getRaceAnimal());
//        $animal->setEtatAnimal($data['etat_animal'] ?? $animal->getEtatAnimal());
//        $animal->setImage($data['image'] ?? $animal->getImage());
//
//        // Sauvegarder les modifications dans la base de données
//        $this->entityManager->flush();
//
//        // Retourner un message de succès
//        return $this->json([
//            'message' => 'Animal mis à jour avec succès !',
//            'data' => $animal
//        ]);
//    }
//
//    // DELETE : Supprimer un animal
//    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
//    public function delete(int $id): JsonResponse
//    {
//        // Rechercher l'animal par ID dans la base de données
//        $animal = $this->animalRepository->find($id);
//
//        // Si l'animal n'existe pas, renvoyer une erreur 404
//        if (!$animal) {
//            return $this->json(['message' => 'Animal non trouvé.'], Response::HTTP_NOT_FOUND);
//        }
//
//        // Supprimer l'animal de la base de données
//        $this->entityManager->remove($animal);
//        $this->entityManager->flush();
//
//        // Retourner un message de confirmation
//        return $this->json([
//            'message' => 'Animal supprimé avec succès !'
//        ], Response::HTTP_NO_CONTENT);
//    }
//}