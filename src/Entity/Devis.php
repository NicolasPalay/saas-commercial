<?php

namespace App\Entity;

use App\Repository\DevisRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Contract\OwnedByCompanyInterface;


#[ORM\Entity(repositoryClass: DevisRepository::class)]
class Devis implements OwnedByCompanyInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\ManyToOne(inversedBy: 'devis')]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'devis')]
    private ?User $user = null;

    #[ORM\OneToMany(targetEntity: DevisDetails::class, mappedBy: 'devis')]
    private Collection $devisDetails;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $total = 0;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $taxe = 0;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $totalTTC = 0;

    #[ORM\ManyToOne(inversedBy: 'devis')]
    private ?Client $client = null;

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

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private bool $isInvoiced = false;

    #[ORM\OneToOne(mappedBy: 'devis', targetEntity: Invoice::class)]
    private ?Invoice $invoice = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'devis')]
    private Collection $orders;


    public function __construct()
    {
        $this->devisDetails = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = mb_strtoupper($reference);
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
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
            $devisDetail->setDevis($this);
        }
        return $this;
    }

    public function removeDevisDetail(DevisDetails $devisDetail): static
    {
        if ($this->devisDetails->removeElement($devisDetail)) {
            if ($devisDetail->getDevis() === $this) {
                $devisDetail->setDevis(null);
            }
        }
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
        $this->deliveryStreet = mb_strtoupper($deliveryStreet);
        return $this;
    }

    public function getDeliveryStreet2(): ?string
    {
        return $this->deliveryStreet2;
    }

    public function setDeliveryStreet2(?string $deliveryStreet2): static
    {
        $this->deliveryStreet2 = mb_strtoupper($deliveryStreet2);
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
        $this->deliveryCity = mb_strtoupper($deliveryCity);
        return $this;
    }

    public function getDeliveryLabel(): ?string
    {
        return $this->deliveryLabel;
    }

    public function setDeliveryLabel(?string $deliveryLabel): static
    {
        $this->deliveryLabel = mb_strtoupper($deliveryLabel);
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function isInvoiced(): bool
    {
        return $this->isInvoiced;
    }

    public function setIsInvoiced(bool $isInvoiced): static
    {
        $this->isInvoiced = $isInvoiced;
        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setDevis($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getDevis() === $this) {
                $order->setDevis(null);
            }
        }

        return $this;
    }   
}
