<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/utilisateur', name: 'app_api_utilisateur')]
class UtilisateurController extends AbstractController
{
    // Constructeur avec injection de dépendances (EntityManager, Repository, et Serializer)
    public function __construct(
        private EntityManagerInterface $manager,
        private UtilisateurRepository $repository,
        private SerializerInterface $serializer
    ) {
    }

    // Créer un nouvel utilisateur
    #[Route(name: 'new', methods: 'POST')]
    public function new(Request $request): Response
    {
        // Désérialiser les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier si un utilisateur avec le même email existe déjà
        $existingUser = $this->repository->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            // Si l'email est déjà utilisé, retourner un message d'erreur avec code 409 (Conflit)
            return $this->json(['message' => 'Cet email est déjà utilisé.'], Response::HTTP_CONFLICT);
        }

        // Créer un nouvel objet Utilisateur
        $utilisateur = new Utilisateur();

        // Vérifier si le mot de passe est présent dans les données
        if (empty($data['mot_de_passe'])) {
            // Si le mot de passe est manquant, retourner un message d'erreur avec code 400 (Mauvaise requête)
            return $this->json(['message' => 'Le mot de passe est obligatoire.'], Response::HTTP_BAD_REQUEST);
        }

        // Définir les valeurs de l'utilisateur
        $utilisateur->setNom($data['nom'] ?? 'Inconnu');
        $utilisateur->setEmail($data['email'] ?? 'email@example.com');
        $utilisateur->setPassword(password_hash($data['mot_de_passe'], PASSWORD_BCRYPT)); // Hachage du mot de passe

        // Vérifier le rôle et l'attribuer correctement
        $role = $data['roles'][0] ?? 'ROLE_EMPLOYE';
        if (!in_array($role, ['ROLE_ADMIN', 'ROLE_EMPLOYE', 'ROLE_VETERINAIRE'])) {
            $role = 'ROLE_EMPLOYE'; // Si le rôle est invalide, attribuer le rôle d'employé par défaut
        }
        $utilisateur->setRoles([$role]);

        // Persister l'utilisateur dans la base de données
        $this->manager->persist($utilisateur);
        $this->manager->flush();

        // Retourner un message de succès avec le code 201 (Créé)
        return $this->json(
            ['message' => "Nouvel utilisateur créé avec succès avec l'id {$utilisateur->getId()}"],
            Response::HTTP_CREATED,
        );
    }

    // Lire (afficher un utilisateur spécifique)
    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id, UtilisateurRepository $utilisateurRepository): Response
    {
        // Trouver l'utilisateur dans la base de données par son ID
        $utilisateur = $utilisateurRepository->find($id);

        // Si l'utilisateur n'est pas trouvé, retourner une exception
        if (!$utilisateur) {
            throw new \Exception("Utilisateur non trouvé pour l'ID {$id}");
        }

        // Retourner les informations de l'utilisateur sous forme de JSON
        return $this->json(
            ['message' => "Utilisateur trouvé : {$utilisateur->getNom()} pour l'ID {$utilisateur->getId()}"]
        );
    }

    // Mettre à jour un utilisateur existant
    #[Route('/{id}', name: 'update', methods: 'PUT')]
    public function update(int $id, Request $request, UtilisateurRepository $utilisateurRepository, EntityManagerInterface $entityManager): Response
    {
        // Rechercher l'utilisateur par ID dans la base de données
        $utilisateur = $utilisateurRepository->find($id);

        // Si l'utilisateur n'existe pas, renvoyer une erreur 404
        if (!$utilisateur) {
            return $this->json(['message' => "Utilisateur non trouvé pour l'ID {$id}"], Response::HTTP_NOT_FOUND);
        }

        // Désérialiser les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Mise à jour du nom, email et rôle
        $utilisateur->setNom($data['nom'] ?? $utilisateur->getNom());
        $utilisateur->setEmail($data['email'] ?? $utilisateur->getEmail());
        if (in_array($data['roles'][0], ['ROLE_ADMIN', 'ROLE_EMPLOYE', 'ROLE_VETERINAIRE'])) {
            $utilisateur->setRoles($data['roles']);
        }

        // Mise à jour du mot de passe si présent
        if (!empty($data['mot_de_passe'])) {
            $utilisateur->setPassword(password_hash($data['mot_de_passe'], PASSWORD_BCRYPT));
        }

        // Sauvegarder les modifications dans la base de données
        $entityManager->flush();

        // Retourner un message de succès avec le code 200 (OK)
        return $this->json(['message' => "Utilisateur mis à jour avec succès !"], Response::HTTP_OK);
    }

    // Suppression d'un utilisateur
    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        // Recherche de l'utilisateur dans la base de données par son identifiant
        $utilisateur = $this->repository->find($id);

        // Si l'utilisateur n'est pas trouvé, retournez un message d'erreur avec le code 404
        if (!$utilisateur) {
            return $this->json(['message' => 'Utilisateur non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Supprimer l'utilisateur de la base de données
        $this->manager->remove($utilisateur);
        $this->manager->flush();

        // Retourner un message de confirmation avec le code 200 (succès)
        return $this->json(
            ['message' => 'Utilisateur supprimé avec succès.'],
            Response::HTTP_OK
        );
    }

    // Liste tous les utilisateurs
    #[Route('', name: 'index', methods: 'GET')]
    public function index(UtilisateurRepository $utilisateurRepository): JsonResponse
    {
        // Récupérer tous les utilisateurs de la base de données
        $utilisateurs = $utilisateurRepository->findAll();

        // Construire un tableau d'utilisateurs pour éviter les références circulaires
        $utilisateursArray = [];
        foreach ($utilisateurs as $utilisateur) {
            $utilisateursArray[] = [
                'id' => $utilisateur->getId(),
                'nom' => $utilisateur->getNom(),
                'email' => $utilisateur->getEmail(),
                'roles' => $utilisateur->getRoles(),
            ];
        }

        // Retourner la liste des utilisateurs sous forme de JSON
        return $this->json(['data' => $utilisateursArray]);
    }
}
