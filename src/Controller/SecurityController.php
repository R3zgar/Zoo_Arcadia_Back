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
        $user->setFirstName($userData['firstName'] ?? null);
        $user->setLastName($userData['lastName'] ?? null);
        $user->setEmail($userData['email'] ?? null);

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
                        new OA\Property(property: "username", type: "string"),
                        new OA\Property(property: "password", type: "string")
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

        // Si un nouveau mot de passe est fourni, le hacher et le mettre à jour
        if ($request->toArray()['password'] ?? null) {
            $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        }

        // Enregistre les modifications dans la base de données
        $this->manager->flush();

        // Retourne une réponse sans contenu après la mise à jour réussie
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
