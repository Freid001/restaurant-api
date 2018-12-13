<?php

declare(strict_types=1);

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
     * @throws \Exception
     */
    public function createRestaurant(\stdClass $body): Response
    {
        $errors = $this->validateBody($body);

        if (!empty($errors)) {
            return new Response(400, ["errors" => $errors]);
        }

        $restaurantId = $this->restaurantRepository->createRestaurant($body->restaurant, $body->cuisine);

        $this->restaurantRepository->createdMenu($restaurantId, $body->menu);

        $restaurant = $this->restaurantRepository->fetch($restaurantId);

        $response = [];
        if ($restaurant instanceof Restaurant) {
            $response = [
                "id" => $restaurant->getId(),
                "restaurant" => $restaurant->getName(),
                "menu" => $this->formatMenuItems($restaurant->getMenuItems())
            ];
        }

        return new Response(201, $response);
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

        return array_reduce($body->menu, function($errors, \stdClass $menu) use ($ignore){
            if (!(strlen($menu->item) >= 2 && strlen($menu->item) <= 255) &&
                !in_array("menu", $ignore)) {
                $errors['menu']['item'][] = "Must be between 2 than 255 characters.";
            }

            if (!is_float($menu->price) && !in_array("price", $ignore)) {
                $errors['menu']['price'][] = "Must be float.";
            }

            if (!is_bool($menu->available) && !in_array("available", $ignore)) {
                $errors['menu']['available'][] = "Must be boolean.";
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
        return array_map(function (MenuItem $menu) {
            return [
                "id" => $menu->getId(),
                "item" => $menu->getItem(),
                "price" => $menu->getPrice(),
                "available" => $menu->isAvailable()
            ];
        }, $menuItems);
    }
}