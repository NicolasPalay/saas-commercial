<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use App\Contract\OwnedByCompanyInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address implements OwnedByCompanyInterface
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

    #[ORM\Column(length: 255, nullable: true)]
    private ?bool $isDefault = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isDelivery = null;

    #[ORM\ManyToOne(inversedBy: 'addresses')]
    private ?Company $company = null;

    


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
        $this->nameStreet = mb_strtoupper($nameStreet);

        return $this;
    }

    public function getNameStreet2(): ?string
    {
        return $this->nameStreet2;
    }

    public function setNameStreet2(?string $nameStreet2): static
    {
        $this->nameStreet2 = mb_strtoupper($nameStreet2);

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
        $this->ville = mb_strtoupper($ville);

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

    public function isIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(?bool $isDefault): static
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function isDelivery(): ?bool
    {
        return $this->isDelivery;
    }

    public function setIsDelivery(?bool $isDelivery): static
    {
        $this->isDelivery = $isDelivery;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }
}
