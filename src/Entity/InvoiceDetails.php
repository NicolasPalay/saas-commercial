<?php

namespace App\Entity;

use App\Repository\InvoiceDetailsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceDetailsRepository::class)]
class InvoiceDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'invoiceDetails')]
    private ?Invoice $invoice = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(nullable: true)]
    private ?float $price = 0.00;

    #[ORM\Column(nullable: true)]
    private ?float $reduce = 0.00;

    #[ORM\Column(nullable: true)]
    private ?float $quantity = 1;

    #[ORM\Column(nullable: true)]
    private ?float $total = null;

    #[ORM\ManyToOne(inversedBy: 'invoiceDetails')]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'invoiceDetails')]
    private ?Taxe $taxe = null;

    #[ORM\ManyToOne(inversedBy: 'invoiceDetails')]
    private ?Company $company = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): static
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
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
    
        public function getTaxe(): ?Taxe
    {
        return $this->taxe;
    }

    public function setTaxe(?Taxe $taxe): static
    {
        $this->taxe = $taxe;

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
