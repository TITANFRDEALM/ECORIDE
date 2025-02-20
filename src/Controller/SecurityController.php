<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;


#[Route('/api', name: 'app_api_')]
final class SecurityController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private SerializerInterface $serializer)
    {
    }
#[Route('/registration', name: 'registration', methods: 'POST')]
#[OA\Post(
    path: "/api/registration",
    summary: "Inscription d'un nouvel utilisateur",
    requestBody: new OA\RequestBody(
        required: true,
        description: "Données de l'utilisateur à inscrire",
        content: new OA\MediaType(
            mediaType: "application/json",
            schema: new OA\Schema(
                type: "object",
                properties: [
                    new OA\Property(property: "email", type: "string", example: "adresse@email.com"),
                    new OA\Property(property: "password", type: "string", example: "Mot de passe")
                ],
                required: ["email", "password"]
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: "Utilisateur inscrit avec succès",
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    type: "object",
                    properties: [
                        new OA\Property(property: "user", type: "string", example: "Nom d'utilisateur"),
                        new OA\Property(property: "apiToken", type: "string", example: "31a023e212f116124a36af14ea0c1c3806eb9378"),
                        new OA\Property(
                            property: "roles",
                            type: "array",
                            items: new OA\Items(type: "string", example: "ROLE_USER") // Correct usage of OA\Items
                        )
                    ]
                )
            )
        )
    ]
)]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        // Désérialiser les données JSON de la requête dans un objet Utilisateur
        $utilisateur = $this->serializer->deserialize($request->getContent(), Utilisateur::class, 'json');

        // Hacher le mot de passe de l'utilisateur
        $utilisateur->setPassword($passwordHasher->hashPassword($utilisateur, $utilisateur->getPassword()));

        // Générer un apiToken unique pour l'utilisateur
        $apiToken = bin2hex(random_bytes(32)); // Génère un token aléatoire de 64 caractères
        $utilisateur->setApiToken($apiToken);

        // Définir la date de création de l'utilisateur
        $utilisateur->setCreatedAt(new \DateTimeImmutable());

        // Sauvegarder l'utilisateur dans la base de données
        $this->manager->persist($utilisateur);
        $this->manager->flush();

        // Retourner une réponse JSON avec l'utilisateur créé, son apiToken, et ses rôles
        return new JsonResponse(
            [
                'utilisateur' => $utilisateur->getUserIdentifier(),
                'apiToken' => $apiToken, // Retourner l'apiToken généré
                'roles' => $utilisateur->getRoles()
            ], 
            Response::HTTP_CREATED
        );
    }

    #[Route('/login', name: 'login', methods: 'POST')]
    #[OA\Post(
        path: "/api/login",
        summary: "Connecter un nouvel utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de l'utilisateur pour se connecter",
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    type: "object",
                    properties: [
                        new OA\Property(property: "email", type: "string", example: "adresse@email.com"),
                        new OA\Property(property: "password", type: "string", example: "Mot de passe")
                    ],
                    required: ["email", "password"]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Connexion réussie",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(property: "user", type: "string", example: "Nom d'utilisateur"),
                            new OA\Property(property: "apiToken", type: "string", example: "31a023e212f116124a36af14ea0c1c3806eb9378"),
                            new OA\Property(
                                property: "roles",
                                type: "array",
                                items: new OA\Items(type: "string", example: "ROLE_USER") // Correct usage of OA\Items
                            )
                        ]
                    )
                )
            )
        ]
    )]
    public function login(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        // Récupérer les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);
        
        // Vérifier si l'email et le mot de passe sont présents dans la requête
        if (!isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse(['message' => 'Missing credentials'], Response::HTTP_BAD_REQUEST);
        }
    
        // Récupérer l'utilisateur par son email
        $utilisateur = $this->manager->getRepository(Utilisateur::class)->findOneBy(['email' => $data['email']]);
    
        // Vérifier si l'utilisateur existe
        if (!$utilisateur) {
            return new JsonResponse(['message' => 'Invalid credentials.'], Response::HTTP_UNAUTHORIZED);
        }
    
        // Vérifier si le mot de passe est correct
        if (!$passwordHasher->isPasswordValid($utilisateur, $data['password'])) {
            return new JsonResponse(['message' => 'Invalid credentials.'], Response::HTTP_UNAUTHORIZED);
        }
    
        // Si l'authentification réussie, retourner un token et les infos de l'utilisateur
        return new JsonResponse([
            'user' => $utilisateur->getUserIdentifier(),
            'apiToken' => $utilisateur->getApiToken(),
            'roles' => $utilisateur->getRoles(),
        ]);
    }

    // Route pour récupérer les informations de l'utilisateur connecté
    #[Route('/me', name: 'get_user_profile', methods: 'GET')]
    public function me(#[CurrentUser] Utilisateur $utilisateur): JsonResponse
    {
        // Vérifier si l'utilisateur est connecté
        if (!$utilisateur) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
    
        // Utilisation de la méthode serialize pour convertir l'utilisateur en JSON
        $jsonData = $this->serializer->serialize($utilisateur, 'json', ['groups' => 'user:read']);
    
        // Retourner la réponse JSON avec les données de l'utilisateur
        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    // Route pour éditer les informations de l'utilisateur connecté
    #[Route('/edit', name: 'edit_user', methods: 'PUT')]
    public function edit(Request $request, #[CurrentUser] Utilisateur $utilisateur, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        // Désérialiser les données JSON de la requête
        $updatedUser = $this->serializer->deserialize($request->getContent(), Utilisateur::class, 'json');
    
        // Vérifier si l'email existe et correspond à celui de l'utilisateur connecté
        if ($updatedUser->getEmail() !== $utilisateur->getEmail()) {
            return new JsonResponse(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
    
        // Mettre à jour les informations de l'utilisateur
        $utilisateur->setNom($updatedUser->getNom())
            ->setPrenom($updatedUser->getPrenom())
            ->setTelephone($updatedUser->getTelephone())
            ->setAdresse($updatedUser->getAdresse())
            ->setDateNaissance($updatedUser->getDateNaissance())
            ->setPseudo($updatedUser->getPseudo())
            ->setPhoto($updatedUser->getPhoto())
            ->setUpdateAt(new \DateTimeImmutable());  // Met à jour la date de mise à jour
    
        // Si un nouveau mot de passe est envoyé, on le hache
        if ($updatedUser->getPassword()) {
            $utilisateur->setPassword($passwordHasher->hashPassword($utilisateur, $updatedUser->getPassword()));
        }
    
        // Sauvegarder les modifications en base de données
        $this->manager->flush();
    
        return new JsonResponse([
            'message' => 'User updated successfully',
            'user' => $utilisateur->getUserIdentifier(),
            'roles' => $utilisateur->getRoles()
        ]);
    }
}