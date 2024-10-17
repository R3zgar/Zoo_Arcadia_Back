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

#[Route('api/utilisateur', name: 'app_api_utilisateur')]
class UtilisateurController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private UtilisateurRepository $repository)
    {
    }

    // Créer un nouvel utilisateur
    #[Route(name: 'new', methods: 'POST')]
    public function new(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $utilisateur = new Utilisateur();
        $utilisateur->setNom($data['nom'] ?? 'Inconnu');
        $utilisateur->setEmail($data['email'] ?? 'email@exemple.com');
        $utilisateur->setPassword(password_hash($data['mot_de_passe'], PASSWORD_BCRYPT)); // Şifreyi hash'le

        $utilisateur->setRoles([$data['role'] ?? 'ROLE_EMPLOYE']);

        $this->manager->persist($utilisateur);
        $this->manager->flush();

        return $this->json(
            ['message' => "Nouvel utilisateur créé avec succès avec l'id {$utilisateur->getId()}"],
            Response::HTTP_CREATED,
        );
    }

    // Lire (afficher un utilisateur spécifique)
    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id, UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateur = $utilisateurRepository->find($id);

        if (!$utilisateur) {
            throw new \Exception("Utilisateur non trouvé pour l'ID {$id}");
        }

        return $this->json(
            ['message' => "Utilisateur trouvé : {$utilisateur->getNom()} pour l'ID {$utilisateur->getId()}"]
        );
    }

    // Mettre à jour un utilisateur existant
    #[Route('/{id}', name: 'update', methods: 'PUT')]
    public function update(int $id, Request $request, UtilisateurRepository $utilisateurRepository, EntityManagerInterface $entityManager): Response
    {
        $utilisateur = $utilisateurRepository->find($id);

        if (!$utilisateur) {
            return $this->json(['message' => "Utilisateur non trouvé pour l'ID {$id}"], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $utilisateur->setNom($data['nom'] ?? $utilisateur->getNom());
        $utilisateur->setEmail($data['email'] ?? $utilisateur->getEmail());

        if (isset($data['mot_de_passe'])) {
            $utilisateur->setPassword(password_hash($data['mot_de_passe'], PASSWORD_BCRYPT));
        }

        $entityManager->flush();

        return $this->json(['message' => "Utilisateur mis à jour avec succès !"], Response::HTTP_OK);
    }

    // Suppression d'un utilisateur
    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        $utilisateur = $this->repository->find($id);

        if (!$utilisateur) {
            return $this->json(['message' => 'Utilisateur non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $this->manager->remove($utilisateur);
        $this->manager->flush();

        return $this->json(
            ['message' => 'Utilisateur supprimé avec succès.'],
            Response::HTTP_OK
        );
    }

    // Liste tous les utilisateurs
    #[Route('', name: 'index', methods: 'GET')]
    public function index(UtilisateurRepository $utilisateurRepository): JsonResponse
    {
        $utilisateurs = $utilisateurRepository->findAll();
        $utilisateursArray = [];

        foreach ($utilisateurs as $utilisateur) {
            $utilisateursArray[] = [
                'id' => $utilisateur->getId(),
                'nom' => $utilisateur->getNom(),
                'email' => $utilisateur->getEmail(),
                'roles' => $utilisateur->getRoles(),
            ];
        }

        return $this->json(['data' => $utilisateursArray]);
    }
}
