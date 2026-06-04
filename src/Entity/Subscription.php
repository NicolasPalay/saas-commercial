<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Contract\OwnedByCompanyInterface;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
class Subscription implements OwnedByCompanyInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'subscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'subscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    // Idéalement decimal plutôt que string
    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2)]
    private ?string $montant = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isPay = false;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    private ?string $stripeSubscriptionId = null;

    #[ORM\Column(nullable: true)]
    private ?string $stripeCustomerId = null;

    #[ORM\ManyToOne(inversedBy: 'subscriptions')]
    private ?Plan $plan = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $endSubscription = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();

        // Abonnement annuel par défaut
        $this->endSubscription = $this->createdAt->modify('+12 months');
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;
        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
    {
        $this->montant = $montant;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        // Recalcule fin abonnement si basé sur createdAt
        $this->endSubscription = $createdAt->modify('+12 months');

        return $this;
    }

    public function isPay(): bool
    {
        return $this->isPay;
    }

    public function setIsPay(bool $isPay): static
    {
        $this->isPay = $isPay;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getStripeSubscriptionId(): ?string
    {
        return $this->stripeSubscriptionId;
    }

    public function setStripeSubscriptionId(?string $stripeSubscriptionId): static
    {
        $this->stripeSubscriptionId = $stripeSubscriptionId;
        return $this;
    }

    public function getStripeCustomerId(): ?string
    {
        return $this->stripeCustomerId;
    }

    public function setStripeCustomerId(?string $stripeCustomerId): static
    {
        $this->stripeCustomerId = $stripeCustomerId;
        return $this;
    }

    public function getPlan(): ?Plan
    {
        return $this->plan;
    }

    public function setPlan(?Plan $plan): static
    {
        $this->plan = $plan;
        return $this;
    }

    public function getEndSubscription(): ?\DateTimeImmutable
    {
        return $this->endSubscription;
    }

    public function setEndSubscription(\DateTimeImmutable $endSubscription): static
    {
        $this->endSubscription = $endSubscription;
        return $this;
    }

    public function renewForOneYear(): static
    {
        $this->endSubscription = $this->endSubscription
            ? $this->endSubscription->modify('+12 months')
            : (new \DateTimeImmutable())->modify('+12 months');

        return $this;
    }
    
}