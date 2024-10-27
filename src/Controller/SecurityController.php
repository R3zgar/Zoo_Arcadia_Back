<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api', name: 'app_api_')]
class SecurityController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private SerializerInterface $serializer)
    {
    }


    #[Route('/registration', name: 'registration', methods: 'POST')]
    #[OA\Post(
        path: '/api/registration',
        summary: "Inscription d'un nouvel utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'firstName', type: 'string', example: 'John'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Doe'),
                    new OA\Property(property: 'email', type: 'string', example: 'adresse@email.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'Mot de passe'),
                    new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string', example: 'ROLE_ADMIN'))
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Utilisateur inscrit avec succès",
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'user', type: 'string', example: "Nom d'utilisateur"),
                        new OA\Property(property: 'apiToken', type: 'string', example: '31a023e212f116124a36af14ea0c1c3806eb9378'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string', example: 'ROLE_USER'))
                    ]
                )
            )
        ]
    )]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        // Désérialise le contenu JSON en un objet User
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $userData = $request->toArray();

        // Hash du mot de passe avant de l'enregistrer
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));


        // Vérification des valeurs nulles
        if (!isset($userData['firstName'])) {
            throw new \InvalidArgumentException('Le prénom est requis.');
        }
        if (!isset($userData['lastName'])) {
            throw new \InvalidArgumentException('Le nom de famille est requis.');
        }
        if (!isset($userData['email'])) {
            throw new \InvalidArgumentException('L\'adresse e-mail est requise.');
        }
        if (!isset($userData['roles']) || !is_array($userData['roles'])) {
            throw new \InvalidArgumentException('Le rôle est requis et doit être un tableau.');
        }

        // Définit les informations utilisateur
        $user->setFirstName($userData['firstName']);
        $user->setLastName($userData['lastName']);
        $user->setEmail($userData['email']);
        $user->setRoles($userData['roles']); // Définit les rôles envoyés par le client sans ajouter ROLE_USER par défaut

        // Définit la date de création de l'utilisateur
        $user->setCreatedAt(new \DateTimeImmutable());

        // Sauvegarde l'utilisateur dans la base de données
        $this->manager->persist($user);
        $this->manager->flush();

        // Retourne les informations de l'utilisateur et son token API
        return new JsonResponse(
            ['user' => $user->getUserIdentifier(), 'apiToken' => $user->getApiToken(), 'roles' => $user->getRoles()],
            Response::HTTP_CREATED
        );
    }

    // Endpoint pour récupérer la liste des utilisateurs
    #[Route('/users', name: 'get_users', methods: ['GET'])]
    #[OA\Get(
        path: "/api/users",
        summary: "Récupérer la liste des utilisateurs",
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des utilisateurs récupérée avec succès",
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'firstName', type: 'string', example: 'John'),
                            new OA\Property(property: 'lastName', type: 'string', example: 'Doe'),
                            new OA\Property(property: 'email', type: 'string', example: 'john.doe@example.com'),
                            new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string', example: 'ROLE_USER'))
                        ]
                    )
                )
            )
        ]
    )]

    public function getUsers(UserRepository $userRepository): JsonResponse
    {
        // Récupère tous les utilisateurs de la base de données
        $users = $userRepository->findAll();
        $responseData = $this->serializer->serialize($users, 'json', ['groups' => 'user:read']);

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }



    #[Route('/login', name: 'login', methods: 'POST')]
    #[OA\Post(
        path: '/api/login',
        summary: "Connecter un utilisateur",
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    type: "object",
                    required: ["email", "password"],
                    properties: [
                        new OA\Property(property: "email", type: "adresse@email.com"),
                        new OA\Property(property: "password", type: "mot de passe")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Connexion réussie",
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'X-AUTH-TOKEN', type: 'string')
                    ]
                )
            )
        ]
    )]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        // Vérifie si l'utilisateur est connecté
        if (null === $user) {
            return new JsonResponse(['message' => 'Identifiants manquants'], Response::HTTP_UNAUTHORIZED);
        }

        // Retourne les informations de l'utilisateur
        return new JsonResponse([
            'user'  => $user->getUserIdentifier(),
            'apiToken' => $user->getApiToken(),
            'roles' => $user->getRoles(),
        ]);
    }

    // Méthode pour supprimer un utilisateur par son ID
    #[Route('/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    #[OA\Delete(
        path: "/api/users/{id}",
        summary: "Supprimer un utilisateur par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'utilisateur à supprimer",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Utilisateur supprimé avec succès",
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: "L'utilisateur a été supprimé avec succès.")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Utilisateur non trouvé"
            )
        ]
    )]
    public function deleteUser(int $id, UserRepository $userRepository): JsonResponse
    {
        // Récupère l'utilisateur par son ID
        $user = $userRepository->find($id);

        // Vérifie si l'utilisateur existe
        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Supprime l'utilisateur de la base de données
        $this->manager->remove($user);
        $this->manager->flush();

        // Retourne une réponse avec un message de succès
        return new JsonResponse(['message' => "L'utilisateur a été supprimé avec succès."], Response::HTTP_OK);
    }


    // Méthode pour modifier les informations d'un utilisateur par son ID
    #[Route('/users/{id}', name: 'edit_user', methods: ['PUT'])]
    #[OA\Put(
        path: "/api/users/{id}",
        summary: "Modifier un utilisateur par son ID",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'firstName', type: 'string', example: 'Nouveau prénom'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Nouveau nom de famille'),
                    new OA\Property(property: 'email', type: 'string', example: 'nouvel.email@example.com'),
                    new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string', example: 'ROLE_USER'))
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Utilisateur modifié avec succès"
            ),
            new OA\Response(
                response: 404,
                description: "Utilisateur non trouvé"
            )
        ]
    )]
    public function editUser(int $id, Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $user->setFirstName($data['firstName'] ?? $user->getFirstName());
        $user->setLastName($data['lastName'] ?? $user->getLastName());
        $user->setEmail($data['email'] ?? $user->getEmail());
        $user->setRoles($data['roles'] ?? $user->getRoles());

        // Met à jour le mot de passe si fourni
        if (!empty($data['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }

        $user->setUpdatedAt(new \DateTimeImmutable());
        $this->manager->flush();

        return new JsonResponse(['message' => "L'utilisateur a été modifié avec succès."], Response::HTTP_OK);
    }



    #[Route('/account/me', name: 'me', methods: 'GET')]
    #[OA\Get(
        path: "/api/account/me",
        summary: "Récupérer toutes les informations de l'utilisateur",
        responses: [
            new OA\Response(
                response: 200,
                description: "Informations utilisateur retournées avec succès"
            )
        ]
    )]
    public function me(): JsonResponse
    {
        // Récupère l'utilisateur actuel connecté
        $user = $this->getUser();

        // Sérialise les données de l'utilisateur
        $responseData = $this->serializer->serialize($user, 'json');

        // Retourne les informations sérialisées de l'utilisateur
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/account/edit', name: 'edit', methods: 'PUT')]
    #[OA\Put(
        path: "/api/account/edit",
        summary: "Modifier les informations de l'utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'firstName', type: 'string', example: 'Nouveau prénom'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Nouveau nom de famille'),
                    new OA\Property(property: 'password', type: 'string', example: 'Nouveau mot de passe')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Informations utilisateur modifiées avec succès"
            )
        ]
    )]


    #[Route('/account/change-password', name: 'change_password', methods: 'PATCH')]
    #[OA\Put(
        path: "/api/account/change-password",
        summary: "Changer le mot de passe de l'utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'oldPassword', type: 'string', example: 'Ancien mot de passe'),
                    new OA\Property(property: 'newPassword', type: 'string', example: 'Nouveau mot de passe')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Mot de passe modifié avec succès"
            )
        ]
    )]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        #[CurrentUser] User $user
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $oldPassword = $data['oldPassword'] ?? null;
        $newPassword = $data['newPassword'] ?? null;

        if (!$oldPassword || !$newPassword) {
            return new JsonResponse(['message' => 'Ancien mot de passe et nouveau mot de passe sont requis.'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifie si l'ancien mot de passe est correct
        if (!$passwordHasher->isPasswordValid($user, $oldPassword)) {
            return new JsonResponse(['message' => 'L\'ancien mot de passe est incorrect.'], Response::HTTP_BAD_REQUEST);
        }

        // Hash le nouveau mot de passe et l'enregistre
        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        $user->setUpdatedAt(new \DateTimeImmutable());
        $this->manager->flush();

        // Retourne une réponse avec un message après la mise à jour réussie
        return new JsonResponse(['message' => 'Le mot de passe a été changé avec succès.'], Response::HTTP_OK);
    }




    public function edit(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        // Désérialise les nouvelles informations utilisateur à partir du JSON et les applique à l'utilisateur actuel
        $user = $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $this->getUser()]
        );
    
        // Met à jour la date de modification
        $user->setUpdatedAt(new DateTimeImmutable());
    
        // Vérification des valeurs nulles
        if ($request->toArray()['password'] ?? null) {
            $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        }
    
        // Si le prénom ou le nom de famille est null, gérer cela
        if ($user->getFirstName() === null || $user->getLastName() === null) {
            throw new \InvalidArgumentException('Le prénom et le nom de famille ne peuvent pas être null.');
        }
    
        // Enregistre les modifications dans la base de données
        $this->manager->flush();
    
        // Retourne une réponse sans contenu après la mise à jour réussie
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    #[Route('/api/admin', name: 'api_doc_admin', methods: 'GET')]
#[OA\Get(
    path: "/api/admin",
    summary: "Documentation pour les Administrateurs",
    responses: [
        new OA\Response(
            response: 200,
            description: "Documentation spécifique pour le rôle Administrateur"
        )
    ]
)]
public function adminDoc(): JsonResponse
{
    // Documentation pour les actions que les administrateurs peuvent faire
    $documentation = [
        'description' => "Les administrateurs peuvent gérer les utilisateurs, les rôles, et accéder aux zones d'administration.",
        'actions' => [
            'Créer un utilisateur' => '/api/admin/create-user',
            'Modifier un utilisateur' => '/api/admin/edit-user',
            'Supprimer un utilisateur' => '/api/admin/delete-user',
            'Voir tous les utilisateurs' => '/api/admin/list-users'
        ]
    ];

    return new JsonResponse($documentation);
}

#[Route('/api/employee', name: 'api_doc_employee', methods: 'GET')]
#[OA\Get(
    path: "/api/employee",
    summary: "Documentation pour les Employés",
    responses: [
        new OA\Response(
            response: 200,
            description: "Documentation spécifique pour le rôle Employé"
        )
    ]
)]
public function employeeDoc(): JsonResponse
{
    // Documentation pour les actions que les employés peuvent faire
    $documentation = [
        'description' => "Les employés peuvent consulter et gérer les commentaires des visiteurs, et interagir avec les informations des animaux.",
        'actions' => [
            'Consulter les commentaires' => '/api/employee/list-comments',
            'Valider un commentaire' => '/api/employee/validate-comment',
            'Supprimer un commentaire' => '/api/employee/delete-comment',
            'Voir les animaux' => '/api/employee/list-animals'
        ]
    ];

    return new JsonResponse($documentation);
}

#[Route('/api/veterinaire', name: 'api_doc_veterinaire', methods: 'GET')]
#[OA\Get(
    path: "/apic/veterinaire",
    summary: "Documentation pour les Vétérinaires",
    responses: [
        new OA\Response(
            response: 200,
            description: "Documentation spécifique pour le rôle Vétérinaire"
        )
    ]
)]
public function veterinaireDoc(): JsonResponse
{
    // Documentation pour les actions que les vétérinaires peuvent faire
    $documentation = [
        'description' => "Les vétérinaires peuvent consulter et mettre à jour les rapports des animaux, et gérer les informations de santé des animaux.",
        'actions' => [
            'Consulter les rapports vétérinaires' => '/api/veterinaire/list-reports',
            'Ajouter un rapport vétérinaire' => '/api/veterinaire/add-report',
            'Modifier un rapport vétérinaire' => '/api/veterinaire/edit-report',
            'Supprimer un rapport vétérinaire' => '/api/veterinaire/delete-report'
        ]
    ];

    return new JsonResponse($documentation);
}


    
}

