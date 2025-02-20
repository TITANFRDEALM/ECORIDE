<?php

namespace App\Controller;

use App\Entity\Covoiturage;
use App\Repository\CovoiturageRepository;
use App\Repository\VoitureRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/covoiturage', name: 'app_api_covoiturage_')]
final class CovoiturageController extends AbstractController
{
    private EntityManagerInterface $manager;
    private SerializerInterface $serializer;
    private VoitureRepository $voitureRepository;
    private CovoiturageRepository $covoiturageRepository;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        EntityManagerInterface $manager,
        SerializerInterface $serializer,
        VoitureRepository $voitureRepository,
        CovoiturageRepository $covoiturageRepository,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->manager = $manager;
        $this->serializer = $serializer;
        $this->voitureRepository = $voitureRepository;
        $this->covoiturageRepository = $covoiturageRepository;
        $this->urlGenerator = $urlGenerator;
    }

    #[Route(name: 'new', methods: 'POST')]
    public function new(Request $request): JsonResponse
    {
        // Désérialisation et récupération des données
        $data = json_decode($request->getContent(), true);

        // Validation des données nécessaires
        if (!isset(
            $data['voiture_id'], 
            $data['date_depart'], 
            $data['heure_depart'], 
            $data['lieu_depart'], 
            $data['date_arrivee'], 
            $data['heure_arrivee'], 
            $data['lieu_arrivee'], 
            $data['statut'], 
            $data['nb_place'], 
            $data['prix_personne'])) {
            return new JsonResponse(
                ['message' => 'Données manquantes ou invalides'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Recherche de la voiture correspondante
        $voiture = $this->voitureRepository->find($data['voiture_id']);
        if (!$voiture) {
            return new JsonResponse(
                ['message' => 'Voiture non trouvée'],
                Response::HTTP_NOT_FOUND
            );
        }

        // Création de l'entité Covoiturage
        $covoiturage = new Covoiturage();
        $covoiturage->setDateDepart(new \DateTimeImmutable($data['date_depart']))
                    ->setHeureDepart(new \DateTimeImmutable($data['heure_depart']))
                    ->setLieuDepart($data['lieu_depart'])
                    ->setDateArrivee(new \DateTimeImmutable($data['date_arrivee']))
                    ->setHeureArrivee(new \DateTimeImmutable($data['heure_arrivee']))
                    ->setLieuArrivee($data['lieu_arrivee'])
                    ->setStatut($data['statut'])
                    ->setNbPlace($data['nb_place'])
                    ->setPrixPersonne($data['prix_personne'])
                    ->setVoiture($voiture);

        // Sauvegarde dans la base de données
        $this->manager->persist($covoiturage);
        $this->manager->flush();

        // Sérialisation de l'objet Covoiturage
        $responseData = $this->serializer->serialize($covoiturage, 'json', ['groups' => ['covoiturage:read']]);

        // Générer l'URL de la nouvelle ressource
        $location = $this->urlGenerator->generate(
            'app_api_covoiturage_show',
            ['id' => $covoiturage->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): JsonResponse
    {
        $covoiturage = $this->covoiturageRepository->find($id);
        if (!$covoiturage) {
            return new JsonResponse(
                ['message' => "Covoiturage non trouvé pour l'ID {$id}"],
                Response::HTTP_NOT_FOUND
            );
        }

        // Sérialisation de l'objet Covoiturage
        $responseData = $this->serializer->serialize($covoiturage, 'json', ['groups' => ['covoiturage:read']]);

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }
    #[Route('/{id}', name: 'update', methods: 'PUT')]
public function update(Request $request, int $id): JsonResponse
{
    // Récupération des données envoyées dans la requête
    $data = json_decode($request->getContent(), true);

    // Validation des données nécessaires
    if (!isset($data['date_depart'], $data['heure_depart'], $data['lieu_depart'], $data['date_arrivee'], $data['heure_arrivee'], $data['lieu_arrivee'], $data['statut'], $data['nb_place'], $data['prix_personne'])) {
        return new JsonResponse(
            ['message' => 'Données manquantes ou invalides'],
            Response::HTTP_BAD_REQUEST
        );
    }

    // Recherche du covoiturage à mettre à jour
    $covoiturage = $this->covoiturageRepository->find($id);
    if (!$covoiturage) {
        return new JsonResponse(
            ['message' => "Covoiturage non trouvé pour l'ID {$id}"],
            Response::HTTP_NOT_FOUND
        );
    }

    // Mise à jour des propriétés de l'entité Covoiturage
    $covoiturage->setDateDepart(new \DateTimeImmutable($data['date_depart']))
                ->setHeureDepart(new \DateTimeImmutable($data['heure_depart']))
                ->setLieuDepart($data['lieu_depart'])
                ->setDateArrivee(new \DateTimeImmutable($data['date_arrivee']))
                ->setHeureArrivee(new \DateTimeImmutable($data['heure_arrivee']))
                ->setLieuArrivee($data['lieu_arrivee'])
                ->setStatut($data['statut'])
                ->setNbPlace($data['nb_place'])
                ->setPrixPersonne($data['prix_personne']);

    // Sauvegarde des modifications
    $this->manager->flush();

    // Sérialisation de l'objet Covoiturage mis à jour
    $responseData = $this->serializer->serialize($covoiturage, 'json', ['groups' => ['covoiturage:read']]);

    return new JsonResponse($responseData, Response::HTTP_OK, [], true);
}
#[Route('/{id}', name: 'delete', methods: 'DELETE')]
public function delete(int $id): JsonResponse
{
    // Recherche du covoiturage à supprimer
    $covoiturage = $this->covoiturageRepository->find($id);
    if (!$covoiturage) {
        return new JsonResponse(
            ['message' => "Covoiturage non trouvé pour l'ID {$id}"],
            Response::HTTP_NOT_FOUND
        );
    }

    // Suppression de l'entité Covoiturage
    $this->manager->remove($covoiturage);
    $this->manager->flush();

    // Réponse de confirmation
    return new JsonResponse(
        ['message' => "Covoiturage supprimé avec succès"],
        Response::HTTP_NO_CONTENT
    );
}
}
