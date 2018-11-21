<?php

namespace App;


use Restaurant\Customer;
use Restaurant\MenuItem;
use Restaurant\Order;
use Restaurant\OrderedItem;
use Restaurant\Restaurant;

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
     * OrderRoute constructor.
     * @param OrderRepository $orderRepository
     * @param RestaurantRepository $restaurantRepository
     * @param CustomerRepository $customerRepository
     */
    public function __construct(OrderRepository $orderRepository,
                                RestaurantRepository $restaurantRepository,
                                CustomerRepository $customerRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->restaurantRepository = $restaurantRepository;
        $this->customerRepository = $customerRepository;
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
        $errors = $this->validateBody($body,['orderId']);

        $customer = $this->customerRepository->fetch($body->customerId);
        if (!$customer instanceof Customer) {
            $errors["customerId"][] = "Invalid identifier.";
        }

        $item = $this->restaurantRepository->fetchMenuItem($body->itemId);
        if (!$item instanceof MenuItem) {
            $errors["itemId"][] = "Invalid identifier.";
        }else if(!$item->isAvailable()){
            $errors["itemId"][] = "Item is not available.";
        }

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
        $errors = $this->validateBody($body,['customerId']);

        $order = $this->orderRepository->fetch($body->orderId);
        if (!$order instanceof Order) {
            $errors["orderId"][] = "Invalid identifier.";
        }

//todo: check transactions
//        else if($order->isClosed()){
//            $errors["orderId"][] = "Can not append item to closed order.";
//        }

        $item = $this->restaurantRepository->fetchMenuItem($body->itemId);
        if (!$item instanceof MenuItem) {
            $errors["itemId"][] = "Invalid identifier.";
        }else if(!$item->isAvailable()){
            $errors["itemId"][] = "Item is not available.";
        }

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

        $errors = [];
        if (!$order instanceof Order) {
            $errors["orderId"][] = "Invalid identifier.";
        }

        if(($order->countOrderedItems()-1) == 0){
            $errors["orderId"][] = "Must have at least one ordered item.";
        }

//todo: check transactions
//        if ($order->getState() != "open") {
//            $errors["orderId"][] = "Must be an open order.";
//        }

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
        $order = $this->orderRepository->fetch($orderId);

        $errors = [];
        if (!$order instanceof Order) {
            $errors["orderId"][] = "Invalid identifier.";
        }

//todo - check transactions
//        if ($order->getState() != "open") {
//            $errors["orderId"][] = "Can not delete order which is not open.";
//        }

        if (!empty($errors)) {
            return new Response(400, ["errors" => $errors]);
        }

        $this->orderRepository->delete($orderId);

        return new Response(200, ["info" => "Order #" . $orderId . " deleted."]);
    }

    /**
     * @param \stdClass $body
     * @param array $ignore
     * @return array
     */
    private function validateBody(\stdClass $body, $ignore = []): array
    {
        $errors = [];
        if (!is_int($body->itemId) && !in_array("orderId", $ignore)) {
            $errors['orderId'][] = "Must be integer.";
        }

        if (!is_int($body->customerId) && !in_array("customerId", $ignore)) {
            $errors['customerId'][] = "Must be integer.";
        }

        if (!is_int($body->itemId) && !in_array("itemId", $ignore)) {
            $errors['itemId'][] = "Must be integer.";
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