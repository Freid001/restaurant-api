<?php

namespace Restaurant;

/**
 * Class Bill
 * @package Restaurant
 */
class Bill
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var array
     */
    private $transactions;

    /**
     * Bill constructor.
     * @param Order $order
     * @param array $transactions
     */
    public function __construct(Order $order, array $transactions = [])
    {
        $this->order = $order;
        $this->transactions = $transactions;
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
     * @return array
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    /**
     * @param array $transactions
     */
    public function setTransactions(array $transactions): void
    {
        $this->transactions = $transactions;
    }

    /**
     * @param array $orderedIds
     * @return float
     */
    public function getTotalPaid(array $orderedIds = []): float
    {
        return array_reduce($this->getTransactions(), function ($total, Transaction $transaction) use ($orderedIds) {
            if(empty($orderedIds) ||
                $transaction->getOrder()->getOrderedItems($orderedIds)){
                return $total + $transaction->getPaid();
            }
            return $total;
        }, 0);
    }

    /**
     * @return array
     */
    public function getTransactionOrderedItemIds(): array
    {
        return array_reduce($this->getTransactions(), function ($ids, Transaction $transaction) {
            $orderIds = array_map(function (OrderedItem $orderedItem) {
                return $orderedItem->getId();
            }, $transaction->getOrder()->getOrderedItems());

            return array_merge($ids, $orderIds);
        }, []);
    }

    /**
     * @param array $orderedIds
     * @return float
     */
    public function getTotalCharged(array $orderedIds = []): float
    {
        return array_reduce($this->getOrder()->getOrderedItems($orderedIds), function ($total, OrderedItem $orderedItem) {
            return $total + $orderedItem->getPriceCharged();
        }, 0);
    }

    /**
     * @param array $orderedIds
     * @return float
     */
    public function getTotalDue(array $orderedIds = []) : float
    {
        return $this->getTotalCharged($orderedIds) - $this->getTotalPaid($orderedIds);
    }

    /**
     * @return float
     */
    public function getTotalDiscount(): float
    {
        return round((($this->getTotalOriginalPrice() - $this->getTotalCharged()) / $this->getTotalOriginalPrice()), 2);
    }

    /**
     * @return float
     */
    public function getTotalOriginalPrice(): float
    {
        return array_reduce($this->getOrder()->getOrderedItems($this->getTransactionOrderedItemIds()), function ($total, OrderedItem $orderedItem) {
            return $total + $orderedItem->getItem()->getPrice();
        }, 0);
    }

    /**
     * @return float
     */
    public function getTotalSavings(): float
    {
        return array_reduce($this->getOrder()->getOrderedItems($this->getTransactionOrderedItemIds()), function ($total, OrderedItem $orderedItem) {
            return $total + ($orderedItem->getDiscount() * $orderedItem->getItem()->getPrice());
            }, 0);
    }

    /**
     * @return float
     */
    public function getTotalTip(): float
    {
        return array_reduce($this->getTransactions(), function ($total, Transaction $transaction) {
            if ($transaction->isTip()) {
                return $total + $transaction->getPaid();
            }
            return $total;
        }, 0);
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        if ($this->getTotalDue() <= 0) {
            return "closed";
        } else if ($this->getTotalDue() != $this->getTotalCharged()) {
            return "partially-paid";
        }

        return "open";
    }
}