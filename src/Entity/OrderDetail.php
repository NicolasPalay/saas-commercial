<?php

namespace App\Entity;

use App\Repository\OrderDetailRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderDetailRepository::class)]
class OrderDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $label = null;

    #[ORM\Column(nullable: true)]
    private ?float $price = 0.00;

    #[ORM\Column(nullable: true)]
    private ?float $reduce = 0.00;

    #[ORM\Column(nullable: true)]
    private ?float $quantity = 1;

    #[ORM\Column(nullable: true)]
    private ?float $total = null;

    #[ORM\ManyToOne(inversedBy: 'orderDetails')]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'orderDetails')]
    private ?Order $commande = null;

    #[ORM\ManyToOne(inversedBy: 'orderDetails')]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'orderDetails')]
    private ?Taxe $taxe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getReduce(): ?float
    {
        return $this->reduce;
    }

    public function setReduce(?float $reduce): static
    {
        $this->reduce = $reduce;

        return $this;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(?float $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): static
    {
        $this->total = $total;

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

    public function getCommande(): ?Order
    {
        return $this->commande;
    }

    public function setCommande(?Order $commande): static
    {
        $this->commande = $commande;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getTaxe(): ?Taxe
    {
        return $this->taxe;
    }

    public function setTaxe(?Taxe $taxe): static
    {
        $this->taxe = $taxe;

        return $this;
    }
}
