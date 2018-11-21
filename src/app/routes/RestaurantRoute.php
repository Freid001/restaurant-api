<?php

namespace App;

use Restaurant\MenuItem;
use Restaurant\Restaurant;

/**
 * Class RestaurantRoute
 * @package App
 */
class RestaurantRoute
{

    /**
     * @var RestaurantRepository
     */
    private $restaurantRepository;

    /**
     * RestaurantRoute constructor.
     * @param RestaurantRepository $restaurantRepository
     */
    public function __construct(RestaurantRepository $restaurantRepository)
    {
        $this->restaurantRepository = $restaurantRepository;
    }

    /**
     * @param string|null $restaurant
     * @param string|null $item
     * @param bool|null $available
     * @return Response
     */
    public function restaurants(?string $restaurant, ?string $item, ?bool $available): Response
    {
        $restaurants = array_map(function (Restaurant $restaurant) {
            return [
                "id" => $restaurant->getId(),
                "restaurant" => $restaurant->getName(),
                "menu" => $this->formatMenuItems($restaurant->getMenuItems())
            ];
        }, $this->restaurantRepository->fetchAll($restaurant, $item, $available));

        return new Response(!empty($restaurants) ? 200 : 404, $restaurants);
    }

    /**
     * @param \stdClass $body
     * @return Response
     */
    public function createRestaurant(\stdClass $body): Response
    {
        $errors = $this->validateBody($body);

        if (!empty($errors)) {
            return new Response(400, ["errors" => $errors]);
        }

        $restaurantId = $this->restaurantRepository->createRestaurant($body->restaurant, $body->cuisine);

        $this->restaurantRepository->createdMenu($restaurantId, $body->items);

        $restaurant = $this->restaurantRepository->fetch($restaurantId);

        return new Response(201, [
            "id" => $restaurant->getId(),
            "restaurant" => $restaurant->getName(),
            "menu" => $this->formatMenuItems($restaurant->getMenuItems())
        ]);
    }

    /**
     * @param \stdClass $body
     * @param array $ignore
     * @return array
     */
    private function validateBody(\stdClass $body, $ignore = []): array
    {
        $errors = [];
        if (!(strlen($body->restaurant) >= 2 && strlen($body->restaurant) <= 255) &&
            !in_array("restaurant", $ignore)) {
            $errors['restaurant'][] = "Must be between 2 than 255 characters.";
        }

        if (!(strlen($body->cuisine) >= 2 && strlen($body->cuisine) <= 255) &&
            !in_array("cuisine", $ignore)) {
            $errors['cuisine'][] = "Must be between 2 than 255 characters.";
        }

        return array_reduce($body->items, function($errors, \stdClass $item) use ($ignore){
            if (!(strlen($item->item) >= 2 && strlen($item->item) <= 255) &&
                !in_array("item", $ignore)) {
                $errors['items']['item'][] = "Must be between 2 than 255 characters.";
            }

            if (!is_float($item->price) && !in_array("price", $ignore)) {
                $errors['items']['price'][] = "Must be float.";
            }

            if (!is_bool($item->available) && !in_array("available", $ignore)) {
                $errors['items']['available'][] = "Must be boolean.";
            }

            return $errors;
        }, $errors);
    }

    /**
     * @param array $menuItems
     * @return array
     */
    private function formatMenuItems(array $menuItems): array
    {
        return array_map(function (Restaurant $restaurant) {
            $menu = array_map(function (MenuItem $menu) {
                return [
                    "id" => $menu->getId(),
                    "item" => $menu->getItem(),
                    "price" => $menu->getPrice(),
                    "available" => $menu->isAvailable()
                ];
            }, $restaurant->getMenuItems());

            return [
                "id" => $restaurant->getId(),
                "restaurant" => $restaurant->getName(),
                "menu" => $menu
            ];
        }, $menuItems);
    }
}