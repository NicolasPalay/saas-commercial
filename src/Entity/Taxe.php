<?php

namespace App\Entity;

use App\Repository\TaxeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaxeRepository::class)]
class Taxe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 10)]
    private ?string $contry_code = "FR";

    #[ORM\Column]
    private ?float $rate = 20;

    #[ORM\Column(length: 255)]
    private ?string $type = "standard";

    #[ORM\Column]
    private ?bool $isActive = false;

    /**
     * @var Collection<int, DevisDetails>
     */
    #[ORM\OneToMany(targetEntity: DevisDetails::class, mappedBy: 'taxe')]
    private Collection $devisDetails;

    #[ORM\ManyToOne(inversedBy: 'taxes')]
    private ?Company $company = null;


    public function __construct()
    {
        $this->devisDetails = new ArrayCollection();
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
        $this->name = $name;

        return $this;
    }

    public function getContryCode(): ?string
    {
        return $this->contry_code;
    }

    public function setContryCode(string $contry_code): static
    {
        $this->contry_code = $contry_code;

        return $this;
    }

    public function getRate(): ?float
    {
        return $this->rate;
    }

    public function setRate(float $rate): static
    {
        $this->rate = $rate;

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

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

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
            $devisDetail->setTaxe($this);
        }

        return $this;
    }

    public function removeDevisDetail(DevisDetails $devisDetail): static
    {
        if ($this->devisDetails->removeElement($devisDetail)) {
            // set the owning side to null (unless already changed)
            if ($devisDetail->getTaxe() === $this) {
                $devisDetail->setTaxe(null);
            }
        }

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
