<?php

namespace App\Entity;

use App\Repository\CovoiturageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CovoiturageRepository::class)]
class Covoiturage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['covoiturage:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['covoiturage:read', 'covoiturage:write'])]
    private ?\DateTimeInterface $date_depart = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['covoiturage:read', 'covoiturage:write'])]
    private ?\DateTimeInterface $heure_depart = null;

    #[ORM\Column(length: 50)]
    #[Groups(['covoiturage:read', 'covoiturage:write'])]
    private ?string $lieu_depart = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['covoiturage:read', 'covoiturage:write'])]
    private ?\DateTimeInterface $date_arrivee = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['covoiturage:read', 'covoiturage:write'])]
    private ?\DateTimeInterface $heure_arrivee = null;

    #[ORM\Column(length: 50)]
    #[Groups(['covoiturage:read', 'covoiturage:write'])]
    private ?string $lieu_arrivee = null;

    #[ORM\Column(length: 50)]
    #[Groups(['covoiturage:read', 'covoiturage:write'])]
    private ?string $statut = null;

    #[ORM\Column]
    #[Groups(['covoiturage:read', 'covoiturage:write'])]
    private ?int $nb_place = null;

    #[ORM\Column]
    #[Groups(['covoiturage:read', 'covoiturage:write'])]
    private ?float $prix_personne = null;

    #[ORM\ManyToOne(inversedBy: 'covoiturages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['covoiturage:read', 'covoiturage:write'])]
    private ?Voiture $voiture = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->date_depart;
    }

    public function setDateDepart(\DateTimeInterface $date_depart): static
    {
        $this->date_depart = $date_depart;

        return $this;
    }

    public function getHeureDepart(): ?\DateTimeInterface
    {
        return $this->heure_depart;
    }

    public function setHeureDepart(\DateTimeInterface $heure_depart): static
    {
        $this->heure_depart = $heure_depart;

        return $this;
    }

    public function getLieuDepart(): ?string
    {
        return $this->lieu_depart;
    }

    public function setLieuDepart(string $lieu_depart): static
    {
        $this->lieu_depart = $lieu_depart;

        return $this;
    }

    public function getDateArrivee(): ?\DateTimeInterface
    {
        return $this->date_arrivee;
    }

    public function setDateArrivee(\DateTimeInterface $date_arrivee): static
    {
        $this->date_arrivee = $date_arrivee;

        return $this;
    }

    public function getHeureArrivee(): ?\DateTimeInterface
    {
        return $this->heure_arrivee;
    }

    public function setHeureArrivee(\DateTimeInterface $heure_arrivee): static
    {
        $this->heure_arrivee = $heure_arrivee;

        return $this;
    }

    public function getLieuArrivee(): ?string
    {
        return $this->lieu_arrivee;
    }

    public function setLieuArrivee(string $lieu_arrivee): static
    {
        $this->lieu_arrivee = $lieu_arrivee;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getNbPlace(): ?int
    {
        return $this->nb_place;
    }

    public function setNbPlace(int $nb_place): static
    {
        $this->nb_place = $nb_place;

        return $this;
    }

    public function getPrixPersonne(): ?float
    {
        return $this->prix_personne;
    }

    public function setPrixPersonne(float $prix_personne): static
    {
        $this->prix_personne = $prix_personne;

        return $this;
    }

    public function getVoiture(): ?Voiture
    {
        return $this->voiture;
    }

    public function setVoiture(?Voiture $voiture): static
    {
        $this->voiture = $voiture;

        return $this;
    }
}
