<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Devis $devis = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, InvoiceDetails>
     */
    #[ORM\OneToMany(targetEntity: InvoiceDetails::class, mappedBy: 'invoice')]
    private Collection $invoiceDetails;

    #[ORM\Column(length: 255)]
    private ?string $raisonSocial = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codePostal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column]
    private ?float $priceTotalHt = null;

    #[ORM\Column]
    private ?float $taxeTotal = null;

    #[ORM\Column]
    private ?float $priceTotalTTC = null;

    #[ORM\Column]
    private ?bool $isPay = false;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?Client $client = null;

    public function __construct()
    {
        $this->invoiceDetails = new ArrayCollection();
                $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDevis(): ?Devis
    {
        return $this->devis;
    }

    public function setDevis(?Devis $devis): static
    {
        $this->devis = $devis;

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
            $invoiceDetail->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoiceDetail(InvoiceDetails $invoiceDetail): static
    {
        if ($this->invoiceDetails->removeElement($invoiceDetail)) {
            // set the owning side to null (unless already changed)
            if ($invoiceDetail->getInvoice() === $this) {
                $invoiceDetail->setInvoice(null);
            }
        }

        return $this;
    }

    public function getRaisonSocial(): ?string
    {
        return $this->raisonSocial;
    }

    public function setRaisonSocial(string $raisonSocial): static
    {
        $this->raisonSocial = $raisonSocial;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getPriceTotalHt(): ?float
    {
        return $this->priceTotalHt;
    }

    public function setPriceTotalHt(float $priceTotalHt): static
    {
        $this->priceTotalHt = $priceTotalHt;

        return $this;
    }

    public function getTaxeTotal(): ?float
    {
        return $this->taxeTotal;
    }

    public function setTaxeTotal(float $taxeTotal): static
    {
        $this->taxeTotal = $taxeTotal;

        return $this;
    }

    public function getPriceTotalTTC(): ?float
    {
        return $this->priceTotalTTC;
    }

    public function setPriceTotalTTC(float $priceTotalTTC): static
    {
        $this->priceTotalTTC = $priceTotalTTC;

        return $this;
    }

    public function isPay(): ?bool
    {
        return $this->isPay;
    }

    public function setIsPay(bool $isPay): static
    {
        $this->isPay = $isPay;

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
}
