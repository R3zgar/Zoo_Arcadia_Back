<?php

namespace App\Controller;

use App\Entity\Habitat;
use App\Repository\HabitatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api/habitat', name: 'app_api_habitat')]
class HabitatController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private HabitatRepository $repository)
    {
    }

    // Créer un nouvel habitat
    #[Route(name: 'new', methods: 'POST')]
    public function new(Request $request): Response
    {
        // Décoder les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Créer un nouvel objet Habitat
        $habitat = new Habitat();
        $habitat->setNomHabitat($data['nom_habitat'] ?? 'Désert');
        $habitat->setDescriptionHabitat($data['description_habitat'] ?? 'Région désertique avec peu de végétation');
        $habitat->setImage($data['image'] ?? 'desert.png');

        // Persister l'habitat dans la base de données
        $this->manager->persist($habitat);
        $this->manager->flush();

        // Retourner un message de succès
        return $this->json(
            ['message' => "Nouvel habitat créé avec succès avec l'id {$habitat->getId()}"],
            Response::HTTP_CREATED,
        );
    }

    // Lire (afficher un habitat spécifique)
    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id, HabitatRepository $habitatRepository): Response
    {
        // Trouver l'habitat dans la base de données par son ID
        $habitat = $habitatRepository->find($id);

        // Si l'habitat n'est pas trouvé, retourner une exception
        if (!$habitat) {
            throw new \Exception("Habitat non trouvé pour l'ID {$id}");
        }

        // Retourner les informations de l'habitat sous forme de JSON
        return $this->json(
            ['message' => "Un Habitat trouvé : {$habitat->getNomHabitat()} pour l'ID {$habitat->getId()}"]
        );
    }

    // Mettre à jour un habitat existant
    #[Route('/{id}', name: 'update', methods: 'PUT')]
    public function update(int $id, Request $request, HabitatRepository $habitatRepository, EntityManagerInterface $entityManager): Response
    {
        // Rechercher l'habitat par ID dans la base de données
        $habitat = $habitatRepository->find($id);

        // Si l'habitat n'existe pas, renvoyer une erreur 404
        if (!$habitat) {
            return $this->json(['message' => "Habitat non trouvé pour l'ID {$id}"], Response::HTTP_NOT_FOUND);
        }

        // Décoder les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Mettre à jour les informations de l'habitat
        $habitat->setNomHabitat($data['nom_habitat'] ?? $habitat->getNomHabitat());
        $habitat->setDescriptionHabitat($data['description_habitat'] ?? $habitat->getDescriptionHabitat());
        $habitat->setImage($data['image'] ?? $habitat->getImage());

        // Sauvegarder les modifications dans la base de données
        $entityManager->flush();

        // Retourner un message de succès
        return $this->json(['message' => "Habitat mis à jour avec succès !"], Response::HTTP_OK);
    }

    // Suppression d'un habitat
    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        // Recherche de l'habitat dans la base de données par son identifiant
        $habitat = $this->repository->find($id);

        // Si l'habitat n'est pas trouvé, retournez un message d'erreur avec le code 404
        if (!$habitat) {
            return $this->json(['message' => 'Habitat non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Supprimer l'habitat de la base de données
        $this->manager->remove($habitat);
        $this->manager->flush();

        // Retourner un message de confirmation avec le code 200 (succès)
        return $this->json(
            ['message' => 'Habitat supprimé avec succès.'],
            Response::HTTP_OK // Statut 200 OK
        );
    }

// Liste tous les habitats
    #[Route('', name: 'index', methods: 'GET')]
    public function index(HabitatRepository $habitatRepository): JsonResponse
    {
        $habitats = $habitatRepository->findAll();

        // Seul les informations basiques du habitat sont retournées pour éviter la référence circulaire
        $habitatsArray = [];
        foreach ($habitats as $habitat) {
            $habitatsArray[] = [
                'id' => $habitat->getId(),
                'nom_habitat' => $habitat->getNomHabitat(),
                'description_habitat' => $habitat->getDescriptionHabitat(),
                'image' => $habitat->getImage(),
            ];
        }

        return $this->json(['data' => $habitatsArray]);
    }


}
