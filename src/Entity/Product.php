<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Contract\OwnedByCompanyInterface;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product implements OwnedByCompanyInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(nullable: true,type: 'decimal', precision: 10, scale: 2)]
    private ?float $price = null;




    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Company $company = null;

    #[ORM\Column(length: 50)]
    private ?string $reference = null;

    /**
     * @var Collection<int, DevisDetails>
     */
    #[ORM\OneToMany(targetEntity: DevisDetails::class, mappedBy: 'product', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $devisDetails;

    #[ORM\Column(nullable: true)]
    private ?float $stock = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Taxe $taxe = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column]
    private ?bool $isService = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $costPrice = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $barcode = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    /**
     * @var Collection<int, OrderDetail>
     */
    #[ORM\OneToMany(targetEntity: OrderDetail::class, mappedBy: 'product')]
    private Collection $orderDetails;

    /**
     * @var Collection<int, InvoiceDetails>
     */
    #[ORM\OneToMany(targetEntity: InvoiceDetails::class, mappedBy: 'product')]
    private Collection $invoiceDetails;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Category $category = null;

     public function __construct()
    {
        $this->devisDetails = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->orderDetails = new ArrayCollection();
        $this->invoiceDetails = new ArrayCollection();
   }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = mb_strtoupper($name);
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

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

       public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = mb_strtoupper($reference);
   

        return $this;
    }

    /**
     * @return Collection<int, DevisDetails>
     */
    public function getDevisDetails(): Collection
    {
        return $this->devisDetails;
    }

    public function addDevisDetail(DevisDetails $devisDetail): static
    {
        if (!$this->devisDetails->contains($devisDetail)) {
            $this->devisDetails->add($devisDetail);
            $devisDetail->setProduct($this);
        }

        return $this;
    }

    public function removeDevisDetail(DevisDetails $devisDetail): static
    {
        if ($this->devisDetails->removeElement($devisDetail)) {
            // set the owning side to null (unless already changed)
            if ($devisDetail->getProduct() === $this) {
                $devisDetail->setProduct(null);
            }
        }

        return $this;
    }

    public function __toString(): string
{
    return $this->name ?? ''; // ou reference, ou name + reference
}

    public function getStock(): ?float
    {
        return $this->stock;
    }

    public function setStock(?float $stock): static
    {
        $this->stock = $stock;

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

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isService(): ?bool
    {
        return $this->isService;
    }

    public function setIsService(bool $isService): static
    {
        $this->isService = $isService;

        return $this;
    }

    public function getCostPrice(): ?string
    {
        return $this->costPrice;
    }

    public function setCostPrice(?string $costPrice): static
    {
        $this->costPrice = $costPrice;

        return $this;
    }

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function setBarcode(?string $barcode): static
    {
        $this->barcode = $barcode;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, OrderDetail>
     */
    public function getOrderDetails(): Collection
    {
        return $this->orderDetails;
    }

    public function addOrderDetail(OrderDetail $orderDetail): static
    {
        if (!$this->orderDetails->contains($orderDetail)) {
            $this->orderDetails->add($orderDetail);
            $orderDetail->setProduct($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetail $orderDetail): static
    {
        if ($this->orderDetails->removeElement($orderDetail)) {
            // set the owning side to null (unless already changed)
            if ($orderDetail->getProduct() === $this) {
                $orderDetail->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, InvoiceDetails>
     */
    public function getInvoiceDetails(): Collection
    {
        return $this->invoiceDetails;
    }

    public function addInvoiceDetail(InvoiceDetails $invoiceDetail): static
    {
        if (!$this->invoiceDetails->contains($invoiceDetail)) {
            $this->invoiceDetails->add($invoiceDetail);
            $invoiceDetail->setProduct($this);
        }

        return $this;
    }

    public function removeInvoiceDetail(InvoiceDetails $invoiceDetail): static
    {
        if ($this->invoiceDetails->removeElement($invoiceDetail)) {
            // set the owning side to null (unless already changed)
            if ($invoiceDetail->getProduct() === $this) {
                $invoiceDetail->setProduct(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }
}
