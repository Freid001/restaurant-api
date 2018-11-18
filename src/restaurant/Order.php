<?php

namespace Restaurant;

/**
 * Class Order
 * @package App\Domain
 */
class Order
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
     * @var OrderedItem[]
     */
    private $orderedItems;

    /**
     * Order constructor.
     * @param int $id
     * @param Customer $customer
     * @param array $orderedItems
     */
    public function __construct(int $id, Customer $customer, array $orderedItems = [])
    {
        $this->id = $id;
        $this->customer = $customer;
        $this->orderedItems = $orderedItems;
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getOrderedItems(array $ids = []): array
    {
        if(empty($ids)){
            return $this->orderedItems;
        }

        return array_values(
            array_filter($this->orderedItems, function (OrderedItem $order) use ($ids) {
                if (in_array($order->getId(), $ids)) {
                    return true;
                }
                return false;
            })
        );
    }

    /**
     * @return int
     */
    public function countOrderedItems(): int
    {
        return count($this->orderedItems);
    }

    /**
     * @param OrderedItem[] $orderItems
     */
    public function setOrderedItems(array $orderItems): void
    {
        $this->orderedItems = $orderItems;
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
     * @param OrderedItem $orderedItem
     */
    public function appendOrderedItem(OrderedItem $orderedItem): void
    {
        $this->orderedItems[] = $orderedItem;
    }
}