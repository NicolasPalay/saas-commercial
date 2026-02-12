<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nameStreet = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nameStreet2 = null;

    #[ORM\Column(nullable: true)]
    private ?int $codePostal = null;

    #[ORM\Column(length: 255)]
    private ?string $ville = null;

    #[ORM\ManyToOne(inversedBy: 'address')]
    private ?Client $client = null;

    #[ORM\Column(nullable: true)]
    private ?int $businessPhone = null;

    #[ORM\Column(nullable: true)]
    private ?int $mobilePhone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameStreet(): ?string
    {
        return $this->nameStreet;
    }

    public function setNameStreet(?string $nameStreet): static
    {
        $this->nameStreet = $nameStreet;

        return $this;
    }

    public function getNameStreet2(): ?string
    {
        return $this->nameStreet2;
    }

    public function setNameStreet2(?string $nameStreet2): static
    {
        $this->nameStreet2 = $nameStreet2;

        return $this;
    }

    public function getCodePostal(): ?int
    {
        return $this->codePostal;
    }

    public function setCodePostal(?int $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getBusinessPhone(): ?int
    {
        return $this->businessPhone;
    }

    public function setBusinessPhone(?int $businessPhone): static
    {
        $this->businessPhone = $businessPhone;

        return $this;
    }

    public function getMobilePhone(): ?int
    {
        return $this->mobilePhone;
    }

    public function setMobilePhone(?int $mobilePhone): static
    {
        $this->mobilePhone = $mobilePhone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }
}
