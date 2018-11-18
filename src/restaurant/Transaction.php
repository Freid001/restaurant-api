<?php

namespace Restaurant;

/**
 * Class Transaction
 * @package Restaurant
 */
class Transaction
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var OrderedItem
     */
    private $order;

    /**
     * @var bool
     */
    private $tip;

    /**
     * @var float
     */
    private $paid;

    /**
     * Transaction constructor.
     * @param int $id
     * @param Customer $customer
     * @param Order $order
     * @param bool $tip
     * @param float $paid
     */
    public function __construct(int $id, Customer $customer, Order $order, bool $tip, float $paid)
    {
        $this->id = $id;
        $this->customer = $customer;
        $this->order = $order;
        $this->tip = $tip;
        $this->paid = $paid;
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
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer(Customer $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @param Order $order
     */
    public function setOrder(Order $order): void
    {
        $this->order = $order;
    }

    /**
     * @return float
     */
    public function getPaid(): float
    {
        return $this->paid;
    }

    /**
     * @param float $paid
     */
    public function setPaid(float $paid): void
    {
        $this->paid = $paid;
    }

    /**
     * @return bool
     */
    public function isTip(): bool
    {
        return $this->tip;
    }

    /**
     * @param bool $tip
     */
    public function setTip(bool $tip): void
    {
        $this->tip = $tip;
    }
}