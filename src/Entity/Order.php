<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Contract\OwnedByCompanyInterface;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order implements OwnedByCompanyInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $total = 0;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $taxe = 0;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $totalTTC = 0;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deliveryStreet = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deliveryStreet2 = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $deliveryPostalCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deliveryCity = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deliveryLabel = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $deliveryPhone = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Devis $devis = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, OrderDetail>
     */
    #[ORM\OneToMany(targetEntity: OrderDetail::class, mappedBy: 'commande')]
    private Collection $orderDetails;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Client $client = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isInvoiced = null;

    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTaxe(): ?float
    {
        return $this->taxe;
    }

    public function setTaxe(?float $taxe): static
    {
        $this->taxe = $taxe;
        return $this;
    }

    public function getTotalTTC(): ?float
    {
        return $this->totalTTC;
    }

    public function setTotalTTC(?float $totalTTC): static
    {
        $this->totalTTC = $totalTTC;
        return $this;
    }

    public function getDeliveryStreet(): ?string
    {
        return $this->deliveryStreet;
    }

    public function setDeliveryStreet(?string $deliveryStreet): static
    {
        $this->deliveryStreet = $deliveryStreet;
        return $this;
    }

    public function getDeliveryStreet2(): ?string
    {
        return $this->deliveryStreet2;
    }

    public function setDeliveryStreet2(?string $deliveryStreet2): static
    {
        $this->deliveryStreet2 = $deliveryStreet2;
        return $this;
    }

    public function getDeliveryPostalCode(): ?string
    {
        return $this->deliveryPostalCode;
    }

    public function setDeliveryPostalCode(?string $deliveryPostalCode): static
    {
        $this->deliveryPostalCode = $deliveryPostalCode;
        return $this;
    }

    public function getDeliveryCity(): ?string
    {
        return $this->deliveryCity;
    }

    public function setDeliveryCity(?string $deliveryCity): static
    {
        $this->deliveryCity = $deliveryCity;
        return $this;
    }

    public function getDeliveryLabel(): ?string
    {
        return $this->deliveryLabel;
    }

    public function setDeliveryLabel(?string $deliveryLabel): static
    {
        $this->deliveryLabel = $deliveryLabel;
        return $this;
    }

    public function getDeliveryPhone(): ?string
    {
        return $this->deliveryPhone;
    }

    public function setDeliveryPhone(?string $deliveryPhone): static
    {
        $this->deliveryPhone = $deliveryPhone;
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

    public function getDevis(): ?Devis
    {
        return $this->devis;
    }

    public function setDevis(?Devis $devis): static
    {
        $this->devis = $devis;

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
            $orderDetail->setCommande($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetail $orderDetail): static
    {
        if ($this->orderDetails->removeElement($orderDetail)) {
            // set the owning side to null (unless already changed)
            if ($orderDetail->getCommande() === $this) {
                $orderDetail->setCommande(null);
            }
        }

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function isInvoiced(): ?bool
    {
        return $this->isInvoiced;
    }

    public function setIsInvoiced(?bool $isInvoiced): static
    {
        $this->isInvoiced = $isInvoiced;

        return $this;
    }
}
