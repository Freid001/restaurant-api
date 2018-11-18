<?php

namespace App;


use Restaurant\Bill;
use Restaurant\Order;
use Restaurant\OrderedItem;
use Restaurant\Transaction;

/**
 * Class BillRoute
 * @package App
 */
class BillRoute
{
    /**
     * @var BillRepository
     */
    private $billRepository;

    /**
     * BillRoute constructor.
     * @param BillRepository $billRepository
     */
    public function __construct(BillRepository $billRepository)
    {
        $this->billRepository = $billRepository;
    }

    /**
     * @param int|null $orderId
     * @param int|null $customerId
     * @param int|null $payeeId
     * @return Response
     */
    public function bills(?int $orderId, ?int $customerId, ?int $payeeId): Response
    {
        $bills = array_map(function (Bill $bill) {
            return [
                'id' => $bill->getOrder()->getId(),
                'state' => $bill->getState(),
                'customer' => $bill->getOrder()->getCustomer()->getFullName(),
                'ordered' => $this->formatOrderedItems($bill),
                'transactions' => $this->formatTransactions($bill),
                'totalOriginalPrice' => $bill->getTotalOriginalPrice(),
                'totalDiscount' => $bill->getTotalDiscount(),
                'totalSavings' => $bill->getTotalSavings(),
                'totalCharged' => $bill->getTotalCharged(),
                'totalDue' => $bill->getTotalDue(),
                'totalTip' => $bill->getTotalTip(),
                'totalPaid' => $bill->getTotalPaid()
            ];
        }, $this->billRepository->fetchAll(null, $orderId, $customerId, $payeeId));

        return new Response(200, $bills);
    }

    /**
     * @param \stdClass $body
     * @return Response
     */
    public function pay(\stdClass $body) : Response
    {
        $errors = $this->validateBody($body);

        if(isset($body->orderedId)){
            $bill = $this->billRepository->fetch($body->orderId,null, null);

            if($body->pay > $bill->getTotalDue([$body->orderedId])) {
                $errors['pay'][] = 'Can only pay up to amount due: ' . $bill->getTotalDue([$body->orderedId]);
            }
        }

        if (!empty($errors)) {
            return new Response(400, ["errors" => $errors]);
        }

        $this->billRepository->getTransactionRepository()->create(
//            $body->customerId,
//            $body->orderedId,
//            0,
//            $body->pay

            3,
            6,
            1,
            3.00
        );

        return new Response(200,
            array_map(function (Bill $bill) {
                return [
                    'id' => $bill->getOrder()->getId(),
                    'state' => $bill->getState(),
                    'customer' => $bill->getOrder()->getCustomer()->getFullName(),
                    'ordered' => $this->formatOrderedItems($bill),
                    'transactions' => $this->formatTransactions($bill),
                    'totalOriginalPrice' => $bill->getTotalOriginalPrice(),
                    'totalDiscount' => $bill->getTotalDiscount(),
                    'totalSavings' => $bill->getTotalSavings(),
                    'totalCharged' => $bill->getTotalCharged(),
                    'totalDue' => $bill->getTotalDue(),
                    'totalTip' => $bill->getTotalTip(),
                    'totalPaid' => $bill->getTotalPaid()
                ];
            }, [
                $this->billRepository->fetch($body->orderId, null, $body->customerId)
            ])
        );
    }

    /**
     * @param \stdClass $body
     * @return Response
     */
    public function tip(\stdClass $body) : Response
    {
        $errors = $this->validateBody($body);
        if (!empty($errors)) {
            return new Response(400, ["errors" => $errors]);
        }

        if (!empty($errors)) {
            return new Response(400, ["errors" => $errors]);
        }

        $transactionId = $this->billRepository->getTransactionRepository()->create(
            $body->customerId,
            $body->orderedId,
            1,
            $body->pay
        );

        return new Response(200, ["id" => $transactionId]);
    }

    /**
     * @param \stdClass $body
     * @param array $ignore
     * @return array
     */
    private function validateBody(\stdClass $body, $ignore = []): array
    {
        $errors = [];
        if (!is_int($body->orderedId) && !in_array("orderId", $ignore)) {
            $errors['orderId'][] = "Must be integer.";
        }

        if (!is_int($body->customerId) && !in_array("customerId", $ignore)) {
            $errors['customerId'][] = "Must be integer.";
        }

        if (!is_int($body->orderedId) && !in_array("orderedId", $ignore)) {
            $errors['orderedId'][] = "Must be integer.";
        }

        if (!is_float($body->pay) && !in_array("pay", $ignore)) {
            $errors['pay'][] = "Must be float.";
        }

        if ($body->pay < 0 && !in_array("pay", $ignore)) {
            $errors['pay'][] = "Must be greater than 0.";
        }

        return $errors;
    }

    /**
     * @param Bill $bill
     * @return array
     */
    private function formatOrderedItems(Bill $bill): array
    {
        return array_map(function (OrderedItem $ordered) {
            return [
                'id' => $ordered->getId(),
                "item" => [
                    "id" => $ordered->getItem()->getId(),
                    "name" => $ordered->getItem()->getItem(),
                    "originalPrice" => $ordered->getItem()->getPrice()
                ],
                "priceCharged" => $ordered->getPriceCharged(),
                "discount" => $ordered->getDiscount()
            ];
        }, $bill->getOrder()->getOrderedItems(
            $bill->getTransactionOrderedItemIds()
        ));
    }

    /**
     * @param Bill $bill
     * @return array
     */
    private function formatTransactions(Bill $bill): array
    {
        return array_map(function (Transaction $transaction) {
            return [
                'id' => $transaction->getId(),
                'payee' => $transaction->getCustomer()->getFullName(),
                'ordered' => array_map(function (OrderedItem $orderedItem) {
                    return [
                        "id" => $orderedItem->getId(),
                        "name" => $orderedItem->getItem()->getItem()
                    ];
                }, $transaction->getOrder()->getOrderedItems()),
                'paid' => $transaction->getPaid(),
                'tip' => $transaction->isTip()
            ];
        }, $bill->getTransactions());
    }
}