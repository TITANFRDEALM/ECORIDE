<?php

namespace App\Controller;

use App\Entity\Voiture;
use App\Repository\VoitureRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;

#[Route('api/voiture', name: 'app_api_voiture_')]
final class VoitureController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager, 
        private VoitureRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $UrlGenerator,
        )
    {
    }

    #[Route(name: 'new', methods: 'POST')]
    #[OA\Post(
        path: "/api/voiture",
        summary: "Nouvelle Voiture",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de voiture à créer",
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    type: "object",
                    properties: [
                        new OA\Property(property: "modele", type: "string", example: "serie 1"),
                        new OA\Property(property: "immatriculation", type: "string", example: "aa111zz")
                    ],
                    required: ["modele", "immatriculation"]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Voiture créée",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(property: "modele", type: "string", example: "serie1"),
                            new OA\Property(property: "immatriculation", type: "string", example: "aa789zz")
                        ]
                    )
                )
            )
        ]
    )]
    public function new(Request $request): JsonResponse
    {
        $voiture = $this->serializer->deserialize($request->getContent(), Voiture::class, 'json');
        $voiture->setDatePremiereImmatriculation(new DateTimeImmutable());
    
        $this->manager->persist($voiture);
        $this->manager->flush();
    
        // Sérialisation de l'objet voiture pour la réponse
        $responseData = $this->serializer->serialize($voiture, 'json');
    
        // Générer l'URL de l'élément créé
        $location = $this->UrlGenerator->generate(
            'app_api_voiture_show', // La route pour afficher une voiture
            ['id' => $voiture->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    
        // Retourner la réponse avec les données de l'objet et l'en-tête Location
        return new JsonResponse($responseData, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[OA\Get(
        path: "/api/voiture/{id}",
        summary: "Afficher une voiture par son ID",
        parameters: [ // Correct: parameters (plural)
            new OA\Parameter( // Correct: new OA\Parameter
                name: "id",
                in: "path",
                required: true,
                description: "ID de la voiture à afficher", // Correct description
                schema: new OA\Schema(type: "integer") // Correct: new OA\Schema
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Voiture trouvée avec succès",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(property: "modele", type: "string", example: "serie1"),
                            new OA\Property(property: "immatriculation", type: "string", example: "aa789zz")
                        ]
                    )
                )
            )
        ]
    )]
    public function show(int $id): Response
    {
        $voiture = $this->repository->findOneBy(['id' => $id]);
        if (!$voiture) {
            throw $this->createNotFoundException("No voiture found for {$id} id");
        }
    
        // Sérialisation de l'objet voiture en JSON pour la réponse
        $responseData = $this->serializer->serialize($voiture, 'json');
    
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }
    
    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id, Request $request): Response
    {
        $voiture = $this->repository->findOneBy(['id' => $id]);
        if (!$voiture) {
            throw $this->createNotFoundException("No voiture found for {$id} id");
        }
    
        // Récupérer les données JSON envoyées via la requête PUT
        $data = json_decode($request->getContent(), true);
        $modele = $data['modele'] ?? 'Default Model'; // Valeur par défaut si le modèle n'est pas défini
    
        $voiture->setModele($modele); // Mise à jour du modèle de la voiture
        $this->manager->flush(); // Enregistrer les changements dans la base de données
    
        return $this->json(
            ['message' => "Voiture updated: {$voiture->getModele()}"],
            Response::HTTP_OK
        );
    }
    

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        $voiture = $this->repository->findOneBy(['id' => $id]);
        if (!$voiture) {
            throw $this->createNotFoundException("No voiture found for {$id} id");
        }
    
        $this->manager->remove($voiture);
        $this->manager->flush();
    
        return $this->json(['message' => "Voiture resource deleted"], Response::HTTP_NO_CONTENT);
    }
}
