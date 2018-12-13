<?php

declare(strict_types=1);

namespace App;


use Restaurant\Bill;
use Restaurant\Customer;
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
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * BillRoute constructor.
     * @param BillRepository $billRepository
     * @param CustomerRepository $customerRepository
     */
    public function __construct(BillRepository $billRepository,
                                CustomerRepository $customerRepository)
    {
        $this->billRepository = $billRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param int|null $orderId
     * @param int|null $customerId
     * @param int|null $payeeId
     * @return Response
     */
    public function bills(?int $orderId, ?int $customerId, ?int $payeeId): Response
    {
        $bills = $this->billRepository->fetchAll($orderId, $customerId, $payeeId);

        return new Response(
            !empty($bills) ? 200 : 404,
            $this->formatBills($bills,[])
        );
    }

    /**
     * @param \stdClass $body
     * @return Response
     */
    public function pay(\stdClass $body) : Response
    {
        $errors = $this->validateBody($body,['tip']);

        if (!empty($errors)) {
            return new Response(400, ["errors" => $errors]);
        }

        $this->billRepository->getTransactionRepository()->create(
            $body->customerId,
            $body->orderedId,
            false,
            $body->pay
        );

        return new Response(
            200,
            $this->formatBills(
                [$this->billRepository->fetch($body->orderId, null, null)],
                [$body->orderedId]
            )
        );
    }

    /**
     * @param \stdClass $body
     * @return Response
     */
    public function tip(\stdClass $body) : Response
    {
        $errors = $this->validateBody($body,['pay']);

        if (!empty($errors)) {
            return new Response(400, ["errors" => $errors]);
        }

        $this->billRepository->getTransactionRepository()->create(
            $body->customerId,
            $body->orderedId,
            true,
            $body->tip
        );

        return new Response(
            200,
            $this->formatBills(
                [$this->billRepository->fetch($body->orderId, null, null)],
                [$body->orderedId]
            )
        );
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

        if (!is_float($body->tip) && !in_array("tip", $ignore)) {
            $errors['tip'][] = "Must be float.";
        }

        if ($body->tip < 0 && !in_array("tip", $ignore)) {
            $errors['tip'][] = "Must be greater than 0.";
        }

        $customer = $this->customerRepository->fetch($body->customerId);
        if (!$customer instanceof Customer) {
            $errors["customerId"][] = "Invalid identifier.";
        }

        $bill = $this->billRepository->fetch($body->orderId,null, null);
        if(!$bill instanceof Bill && !in_array("orderId", $ignore)){
            $errors['orderId'][] = "Invalid identifier.";
        }else if(isset($body->orderedId) && !in_array("orderedId", $ignore)){
            if(count($bill->getOrder()->getOrderedItems([$body->orderedId])) == 0){
                $errors['orderedId'][] = "Invalid identifier.";
            }

            if(($body->pay - $bill->getTotalDue([$body->orderedId]) > 0) && !in_array("pay", $ignore) ) {
                $errors['pay'][] = 'Can only pay amount due: ' . round($bill->getTotalDue([$body->orderedId]), 2);
            }
        }

        return $errors;
    }

    /**
     * @param array $bills
     * @param array $orderedId
     * @return array
     */
    private function formatBills(array $bills, array $orderedId)
    {
        return array_map(function (Bill $bill) use ($orderedId) {
            return [
                'id' => $bill->getOrder()->getId(),
                'state' => $bill->getState($orderedId),
                'customer' => $bill->getOrder()->getCustomer()->getFullName(),
                'ordered' => $this->formatOrderedItems($bill, $orderedId),
                'transactions' => $this->formatTransactions($bill, $orderedId),
                'totalOriginalPrice' => round($bill->getTotalOriginalPrice($orderedId), 2),
                'totalDiscount' => round($bill->getTotalDiscount($orderedId), 2),
                'totalSavings' => round($bill->getTotalSavings($orderedId), 2),
                'totalCharged' => round($bill->getTotalCharged($orderedId), 2),
                'totalDue' => round($bill->getTotalDue($orderedId), 2),
                'totalTip' => round($bill->getTotalTip(), 2),
                'totalPaid' => round($bill->getTotalPaid($orderedId), 2)
            ];
        }, $bills);
    }

    /**
     * @param Bill $bill
     * @param array $orderedIds
     * @return array
     */
    private function formatOrderedItems(Bill $bill, array $orderedIds = []): array
    {
        return array_map(function (OrderedItem $ordered) {
            return [
                'id' => $ordered->getId(),
                "item" => [
                    "id" => $ordered->getItem()->getId(),
                    "name" => $ordered->getItem()->getItem(),
                    "originalPrice" => round($ordered->getItem()->getPrice(), 2)
                ],
                "priceCharged" => round($ordered->getPriceCharged(),2),
                "discount" => $ordered->getDiscount()
            ];
        }, $bill->getOrder()->getOrderedItems($orderedIds));
    }

    /**
     * @param Bill $bill
     * @param array $orderedIds
     * @return array
     */
    private function formatTransactions(Bill $bill, array $orderedIds = []): array
    {
        return array_reduce($bill->getTransactions(), function ($transactions, Transaction $transaction) use ($orderedIds){
            if($transaction->getOrder()->getOrderedItems($orderedIds)) {
                $transactions[] = [
                    'id' => $transaction->getId(),
                    'payee' => $transaction->getCustomer()->getFullName(),
                    'ordered' => array_map(function (OrderedItem $orderedItem) {
                        return [
                            "id" => $orderedItem->getId(),
                            "name" => $orderedItem->getItem()->getItem()
                        ];
                    }, $transaction->getOrder()->getOrderedItems()),
                    'paid' => round($transaction->getPaid(),2),
                    'tip' => round($transaction->isTip(), 2)
                ];
            }
            return $transactions;
        }, []);
    }
}