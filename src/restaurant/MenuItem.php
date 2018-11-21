<?php

namespace Restaurant;

/**
 * Class MenuItem
 * @package App\Domain
 */
class MenuItem
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $item;

    /**
     * @var float
     */
    private $price;

    /**
     * @var bool
     */
    private $available;

    /**
     * MenuItem constructor.
     * @param int $id
     * @param string $item
     * @param float $price
     * @param bool $available
     */
    public function __construct(int $id, string $item, float $price, bool $available)
    {
        $this->id = $id;
        $this->item = $item;
        $this->price = $price;
        $this->available = $available;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getItem(): string
    {
        return $this->item;
    }

    /**
     * @param string $item
     */
    public function setItem(string $item): void
    {
        $this->item = $item;
    }

    /**
     * @param float $discount
     * @return float
     */
    public function getDiscountPrice(?float $discount): float
    {
        if (is_null($discount)) {
            return $this->getPrice();
        }

        return $this->getPrice() - ($discount * $this->getPrice());
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * @param bool $available
     */
    public function setAvailable(bool $available): void
    {
        $this->available = $available;
    }
}