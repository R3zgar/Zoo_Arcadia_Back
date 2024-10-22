<?php

namespace App\Controller;

use App\Entity\User;
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
                    new OA\Property(property: 'password', type: 'string', example: 'Mot de passe')
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
    
        // Hash du mot de passe avant de l'enregistrer
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
    
        // Définit les valeurs obligatoires (firstName, lastName, email)
        $userData = $request->toArray();
    
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
    
        $user->setFirstName($userData['firstName']);
        $user->setLastName($userData['lastName']);
        $user->setEmail($userData['email']);
    
        // Définit la date de création de l'utilisateur
        $user->setCreatedAt(new DateTimeImmutable());
    
        // Sauvegarde l'utilisateur dans la base de données
        $this->manager->persist($user);
        $this->manager->flush();
    
        // Retourne les informations de l'utilisateur et son token API
        return new JsonResponse(
            ['user'  => $user->getUserIdentifier(), 'apiToken' => $user->getApiToken(), 'roles' => $user->getRoles()],
            Response::HTTP_CREATED
        );
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
                    required: ["username", "password"],
                    properties: [
                        new OA\Property(property: "username", type: "adresse@email.com"),
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
                    new OA\Property(property: 'password', type: 'string', example: 'Nouveau mot de passe'),
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

public function getUsers(UserRepository $userRepository): JsonResponse
{
    $users = $userRepository->findAll();
    return $this->json($users, 200, [], ['groups' => 'user:read']);
}


    
}

