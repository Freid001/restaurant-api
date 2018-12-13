<?php

declare(strict_types=1);

namespace Restaurant;

/**
 * Class OrderBreakdown
 * @package App\Domain
 */
class OrderedItem
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var MenuItem
     */
    private $item;

    /**
     * @var float
     */
    private $priceCharged;

    /**
     * @var float
     */
    private $discount;

    /**
     * OrderedItem constructor.
     * @param int $id
     * @param MenuItem $item
     * @param float $priceCharged
     * @param float|null $discount
     */
    public function __construct(int $id, MenuItem $item, float $priceCharged, ?float $discount)
    {
        $this->id = $id;
        $this->item = $item;
        $this->priceCharged = $priceCharged;
        $this->discount = $discount;
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
     * @return MenuItem
     */
    public function getItem(): MenuItem
    {
        return $this->item;
    }

    /**
     * @param MenuItem $item
     */
    public function setItem(MenuItem $item): void
    {
        $this->item = $item;
    }

    /**
     * @return float
     */
    public function getPriceCharged(): float
    {
        return $this->priceCharged;
    }

    /**
     * @param float $priceCharged
     */
    public function setPriceCharged(float $priceCharged): void
    {
        $this->priceCharged = $priceCharged;
    }

    /**
     * @return float|null
     */
    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    /**
     * @param float $discount
     */
    public function setDiscount(float $discount): void
    {
        $this->discount = $discount;
    }
}