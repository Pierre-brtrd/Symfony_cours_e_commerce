<?php

namespace App\Entity;

use App\Entity\Traits\DateTimeTrait;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    use DateTimeTrait;

    public const STATUS_CART = 'cart';
    public const STATUS_NEW = 'new';
    public const STATUS_PAID = 'paid';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_CANCELLED = 'cancelled';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'orderRef', orphanRemoval: true, cascade: ["persist", "remove"])]
    private Collection $items;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Assert\Choice(
        choices: [self::STATUS_CART, self::STATUS_NEW, self::STATUS_PAID, self::STATUS_SHIPPED, self::STATUS_CANCELLED]
    )]
    private string $status = self::STATUS_CART;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * Calculates the order total.
     *
     * @return float
     */
    public function getTotal(): float
    {
        $total = 0;

        foreach ($this->getItems() as $item) {
            $total += $item->getTotal();
        }

        return $total;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): static
    {
        foreach ($this->getItems() as $existingItem) {
            // The item already exists, update the quantity
            if ($existingItem->equals($item)) {
                $existingItem->setQuantity(
                    $existingItem->getQuantity() + $item->getQuantity()
                );

                return $this;
            }
        }

        $this->items[] = $item;
        $item->setOrderRef($this);

        return $this;
    }

    public function removeItem(OrderItem $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getOrderRef() === $this) {
                $item->setOrderRef(null);
            }
        }

        return $this;
    }

    public function removeItems(): self
    {
        foreach ($this->getItems() as $item) {
            $this->removeItem($item);
        }

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
