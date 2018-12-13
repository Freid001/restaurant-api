<?php

declare(strict_types=1);

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
     * @param bool $excludeTip
     * @return array
     */
    public function getTransactions(bool $excludeTip = false): array
    {
        return array_filter($this->transactions, function (Transaction $transaction) use ($excludeTip) {
            if (!$excludeTip || !$transaction->isTip()) {
                return true;
            }

            return false;
        });
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
    public function getTotalDiscount(array $orderedIds = []): float
    {
        return (($this->getTotalOriginalPrice($orderedIds) - $this->getTotalCharged($orderedIds)) / $this->getTotalOriginalPrice($orderedIds));
    }

    /**
     * @param array $orderedIds
     * @return float
     */
    public function getTotalOriginalPrice(array $orderedIds = []): float
    {
        return array_reduce($this->getOrder()->getOrderedItems($orderedIds), function ($total, OrderedItem $orderedItem) {
            return $total + $orderedItem->getItem()->getPrice();
        }, 0);
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
    public function getTotalSavings(array $orderedIds = []): float
    {
        return array_reduce($this->getOrder()->getOrderedItems($orderedIds), function ($total, OrderedItem $orderedItem) {
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
     * @param array $orderedIds
     * @return string
     */
    public function getState(array $orderedIds = []): string
    {
        if ($this->getTotalDue($orderedIds) <= 0) {
            return "closed";
        }

        return "open";
    }

    /**
     * @param array $orderedIds
     * @return float
     */
    public function getTotalDue(array $orderedIds = []): float
    {
        return $this->getTotalCharged($orderedIds) - $this->getTotalPaid($orderedIds, true);
    }

    /**
     * @param array $orderedIds
     * @param bool $excludeTip
     * @return float
     */
    public function getTotalPaid(array $orderedIds = [], bool $excludeTip = false): float
    {
        return array_reduce($this->getTransactions($excludeTip), function ($total, Transaction $transaction) use ($orderedIds) {
            if (empty($orderedIds) ||
                $transaction->getOrder()->getOrderedItems($orderedIds)) {

                return $total + $transaction->getPaid();
            }
            return $total;
        }, 0);
    }
}