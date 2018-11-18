<?php

namespace App;


use Restaurant\Bill;
use Restaurant\Order;
use Restaurant\Transaction;

/**
 * Class BillRepository
 * @package App
 */
class BillRepository
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * BillRepository constructor.
     * @param OrderRepository $orderRepository
     * @param TransactionRepository $transactionRepository
     */
    public function __construct(OrderRepository $orderRepository,
                                TransactionRepository $transactionRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @return TransactionRepository
     */
    public function getTransactionRepository() : TransactionRepository
    {
        return $this->transactionRepository;
    }

    /**
     * @param int $orderId
     * @param int|null $customerId
     * @param int|null $payeeId
     * @return Bill|null
     */
    public function fetch(int $orderId, ?int $customerId, ?int $payeeId) : ?Bill
    {
        $result = $this->fetchAll($orderId, $customerId, $payeeId);

        return isset($result[0]) ? $result[0] : null;
    }

    /**
     * @param int|null $orderId
     * @param int|null $customerId
     * @param int|null $payeeId
     * @return array
     */
    public function fetchAll(?int $orderId, ?int $customerId, ?int $payeeId): array
    {
        $transactions = $this->transactionRepository->fetchAll($orderId, $payeeId);

        return array_map(function (Order $order) use ($transactions) {
            return new Bill(
                $order,
                array_values(
                    array_filter($transactions, function (Transaction $transaction) use ($order) {
                        if ($transaction->getOrder()->getId() == $order->getId()) {
                            return true;
                        }

                        return false;
                    })
                )
            );
        }, $this->orderRepository->fetchAll($orderId, $customerId));
    }
}
