<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(nullable: true,type: 'decimal', precision: 10, scale: 2)]
    private ?float $price = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'product')]
    private Collection $categories;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Company $company = null;

    #[ORM\Column(length: 50)]
    private ?string $reference = null;

    /**
     * @var Collection<int, DevisDetails>
     */
    #[ORM\OneToMany(targetEntity: DevisDetails::class, mappedBy: 'product')]
    private Collection $devisDetails;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setProduct($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getProduct() === $this) {
                $category->setProduct(null);
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

       public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

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
}
