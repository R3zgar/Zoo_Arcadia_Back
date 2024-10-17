<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Repository\CommentaireRepository;
use App\Repository\AnimalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api/commentaire', name: 'app_api_commentaire')]
class CommentaireController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private CommentaireRepository $repository)
    {
    }

    // Créer un nouveau commentaire
    #[Route(name: 'new', methods: 'POST')]
    public function new(Request $request, AnimalRepository $animalRepository): Response
    {
        // Décoder les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier si 'animal_id' est présent dans les données
        if (!isset($data['animal_id'])) {
            return $this->json(['message' => 'animal_id manquant.'], Response::HTTP_BAD_REQUEST);
        }

        // Rechercher le animal par ID
        $animal = $animalRepository->find($data['animal_id']);

        // Si le animal n'existe pas, retourner une erreur
        if (!$animal) {
            return $this->json(['message' => 'Animal non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Créer un nouvel objet Commentaire
        $commentaire = new Commentaire();
        $commentaire->setTexte($data['texte'] ?? 'Texte par défaut');
        $commentaire->setDateCreation(new \DateTime($data['date_creation'] ?? 'now'));
        $commentaire->setAnimal($animal); // Associer l'animal ici

        // Persister le commentaire dans la base de données
        $this->manager->persist($commentaire);
        $this->manager->flush();

        // Retourner un message de succès
        return $this->json(
            ['message' => "Nouveau commentaire créé avec succès avec l'id {$commentaire->getId()}"],
            Response::HTTP_CREATED,
        );
    }


    // Lire un commentaire spécifique
    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id, CommentaireRepository $commentaireRepository): Response
    {
        // Trouver le commentaire dans la base de données par son ID
        $commentaire = $commentaireRepository->find($id);

        // Si le commentaire n'est pas trouvé, retourner une exception
        if (!$commentaire) {
            throw new \Exception("Commentaire non trouvé pour l'ID {$id}");
        }

        // Retourner les informations du commentaire sous forme de JSON
        return $this->json(
            ['message' => "Un Commentaire trouvé : {$commentaire->getTexte()} pour l'ID {$commentaire->getId()}"]
        );
    }

    // Mettre à jour un commentaire existant
    #[Route('/{id}', name: 'update', methods: 'PUT')]
    public function update(int $id, Request $request, CommentaireRepository $commentaireRepository, EntityManagerInterface $entityManager): Response
    {
        // Rechercher le commentaire par ID dans la base de données
        $commentaire = $commentaireRepository->find($id);

        // Si le commentaire n'existe pas, renvoyer une erreur 404
        if (!$commentaire) {
            return $this->json(['message' => "Commentaire non trouvé pour l'ID {$id}"], Response::HTTP_NOT_FOUND);
        }

        // Décoder les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Mettre à jour les informations du commentaire
        $commentaire->setTexte($data['texte'] ?? $commentaire->getTexte());
        $commentaire->setDateCreation(new \DateTime($data['date_creation'] ?? 'now'));

        // Sauvegarder les modifications dans la base de données
        $entityManager->flush();

        // Retourner un message de succès
        return $this->json(['message' => "Commentaire mis à jour avec succès !"], Response::HTTP_OK);
    }

    // Suppression d'un commentaire
    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        // Recherche du commentaire dans la base de données par son identifiant
        $commentaire = $this->repository->find($id);

        // Si le commentaire n'est pas trouvé, retournez un message d'erreur avec le code 404
        if (!$commentaire) {
            return $this->json(['message' => 'Commentaire non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Supprimer le commentaire de la base de données
        $this->manager->remove($commentaire);
        $this->manager->flush();

        // Retourner un message de confirmation avec le code 200 (succès)
        return $this->json(
            ['message' => 'Commentaire supprimé avec succès.'],
            Response::HTTP_OK
        );
    }

    // Liste tous les commentaires
    #[Route('', name: 'index', methods: 'GET')]
    public function index(CommentaireRepository $commentaireRepository): JsonResponse
    {
        $commentaires = $commentaireRepository->findAll();

        // Seul les informations basiques du commentaire sont retournées
        $commentairesArray = [];
        foreach ($commentaires as $commentaire) {
            $commentairesArray[] = [
                'id' => $commentaire->getId(),
                'texte' => $commentaire->getTexte(),
                'date_creation' => $commentaire->getDateCreation()->format('Y-m-d'),
            ];
        }

        return $this->json(['data' => $commentairesArray]);
    }
}
