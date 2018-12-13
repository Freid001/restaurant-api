<?php

declare(strict_types=1);

namespace App;


use Restaurant\Bill;
use Restaurant\Customer;
use Restaurant\MenuItem;
use Restaurant\Order;
use Restaurant\OrderedItem;

/**
 * Class OrderRoute
 * @package App
 */
class OrderRoute
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var RestaurantRepository
     */
    private $restaurantRepository;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * @var Bill
     */
    private $bill;

    /**
     * OrderRoute constructor.
     * @param OrderRepository $orderRepository
     * @param RestaurantRepository $restaurantRepository
     * @param CustomerRepository $customerRepository
     * @param TransactionRepository $transactionRepository
     * @param Bill $bill
     */
    public function __construct(OrderRepository $orderRepository,
                                RestaurantRepository $restaurantRepository,
                                CustomerRepository $customerRepository,
                                TransactionRepository $transactionRepository,
                                Bill $bill)
    {
        $this->orderRepository = $orderRepository;
        $this->restaurantRepository = $restaurantRepository;
        $this->customerRepository = $customerRepository;
        $this->transactionRepository = $transactionRepository;
        $this->bill = $bill;
    }

    /**
     * @param int|null $orderId
     * @param int|null $customerId
     * @return Response
     */
    public function orders(?int $orderId, ?int $customerId): Response
    {
        $orders = array_map(function (Order $order) {
            return [
                "id" => $order->getId(),
                "customer" => $order->getCustomer()->getFullName(),
                "items" => $this->formatOrderedItems($order)
            ];
        }, $this->orderRepository->fetchAll($orderId, $customerId));

        return new Response(!empty($orders) ? 200 : 404, $orders);
    }

    /**
     * @param $body
     * @return Response
     */
    public function createOrder(\stdClass $body): Response
    {
        $customer = null;
        if (is_int($body->customerId)) {
            $customer = $this->customerRepository->fetch($body->customerId);
        }

        $item = null;
        if (is_int($body->itemId)) {
            $item = $this->restaurantRepository->fetchMenuItem($body->itemId);
        }

        $errors = $this->validateBody($body, $customer, null, $item, ['orderId']);

        if (!empty($errors)) {
            return new Response(400, ["errors" => $errors]);
        }

        $orderId = $this->orderRepository->create($customer->getId());

        $this->orderRepository->appendOrderedItem(
            $orderId,
            $item->getId(),
            $item->getDiscountPrice($body->discount),
            $body->discount
        );

        $order = $this->orderRepository->fetch($orderId);

        return new Response(201, [
            "id" => $order->getId(),
            "customer" => $order->getCustomer()->getFullName(),
            "items" => $this->formatOrderedItems($order)
        ]);
    }

    /**
     * @param \stdClass $body
     * @return Response
     */
    public function appendItem(\stdClass $body): Response
    {
        $errors = [];

        $order = null;
        if (is_int($body->orderId)) {
            $order = $this->orderRepository->fetch($body->orderId);
            $transactions = $this->transactionRepository->fetchAll($body->orderId, null);

            if (!is_null($order)) {
                $this->bill->setOrder($order);
            }

            $this->bill->setTransactions($transactions);
        }

        if ($this->bill->getState() == "closed") {
            $errors["orderId"][] = "Can not append item to closed order.";
        }

        $item = null;
        if (is_int($body->itemId)) {
            $item = $this->restaurantRepository->fetchMenuItem($body->itemId);
        }

        $errors = array_merge($errors, $this->validateBody($body, null, $order, $item, ['customerId']));

        if (!empty($errors)) {
            return new Response(400, ["errors" => $errors]);
        }

        $this->orderRepository->appendOrderedItem(
            $body->orderId,
            $item->getId(),
            $item->getDiscountPrice($body->discount),
            $body->discount
        );

        $order = $this->orderRepository->fetch($body->orderId);

        return new Response(200, [
            "id" => $order->getId(),
            "customer" => $order->getCustomer()->getFullName(),
            "items" => $this->formatOrderedItems($order)
        ]);
    }

    /**
     * @param int|null $orderId
     * @param int|null $itemId
     * @return Response
     */
    public function removeItem(?int $orderId, ?int $itemId): Response
    {
        $order = $this->orderRepository->fetch($orderId);
        $transactions = $this->transactionRepository->fetchAll($orderId, null);

        $errors = [];
        if (!$order instanceof Order) {
            $errors["orderId"][] = "Invalid identifier.";
        }else {
            if (($order->countOrderedItems()-1) == 0) {
                $errors["orderId"][] = "Must have at least one ordered item in order.";
            }

            $this->bill->setOrder($order);
        }

        $this->bill->setTransactions($transactions);

        if (in_array($itemId, $this->bill->getTransactionOrderedItemIds())) {
            $errors["itemId"][] = "Can not remove item which has transactions associated with it.";
        }

        if (!empty($errors)) {
            return new Response(400, ["errors" => $errors]);
        }

        $this->orderRepository->removeOrderedItem($itemId);

        return new Response(200, ["info" => "Item #" . $itemId . " removed from order #" . $orderId . "."]);
    }

    /**
     * @param int|null $orderId
     * @return Response
     */
    public function deleteOrder(?int $orderId): Response
    {
        $transactions = $this->transactionRepository->fetchAll($orderId, null);

        $errors = [];
        if (!empty($transactions)) {
            $errors["orderId"][] = "Can not delete order which has transactions associated with it.";
        }

        if (!empty($errors)) {
            return new Response(400, ["errors" => $errors]);
        }

        $this->orderRepository->delete($orderId);

        return new Response(200, ["info" => "Order #" . $orderId . " deleted."]);
    }

    /**
     * @param \stdClass $body
     * @param Customer|null $customer
     * @param Order|null $order
     * @param MenuItem|null $item
     * @param array $ignore
     * @return array
     */
    private function validateBody(\stdClass $body, ?Customer $customer, ?Order $order, ?MenuItem $item, $ignore = []): array
    {
        $errors = [];
        if (!in_array("orderId", $ignore)) {
            if (!is_int($body->itemId)) {
                $errors['orderId'][] = "Must be integer.";
            }

            if (!$order instanceof Order) {
                $errors["orderId"][] = "Invalid identifier.";
            }
        }

        if (!in_array("customerId", $ignore)) {
            if (!is_int($body->customerId)) {
                $errors['customerId'][] = "Must be integer.";
            }

            if (!$customer instanceof Customer) {
                $errors["customerId"][] = "Invalid identifier.";
            }
        }

        if (!in_array("itemId", $ignore)) {
            if (!is_int($body->itemId)) {
                $errors['itemId'][] = "Must be integer.";
            }

            if (!$item instanceof MenuItem) {
                $errors["itemId"][] = "Invalid identifier.";
            } else if (!$item->isAvailable()) {
                $errors["itemId"][] = "Item is not available.";
            }
        }

        if (!in_array("discount", $ignore)) {
            if (!is_float($body->discount) && !is_null($body->discount)) {
                $errors['discount'][] = "Must be float or null.";
            }

            if ($body->discount > 1) {
                $errors['discount'][] = "Can not be greater than 1.00";
            }
        }

        return $errors;
    }

    /**
     * @param Order $order
     * @return array
     */
    private function formatOrderedItems(Order $order): array
    {
        return array_map(function (OrderedItem $item) {
            return [
                "id" => $item->getId(),
                "item" => [
                    "id" => $item->getItem()->getId(),
                    "name" => $item->getItem()->getItem(),
                    "originalPrice" => $item->getItem()->getPrice()
                ],
                "priceCharged" => $item->getPriceCharged(),
                "discount" => $item->getDiscount()
            ];
        }, $order->getOrderedItems());
    }
}