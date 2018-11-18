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
            $menu = array_map(function(MenuItem $menu) {
                return [
                    "id"        => $menu->getId(),
                    "item"      => $menu->getItem(),
                    "price"     => $menu->getPrice(),
                    "available" => $menu->isAvailable()
                ];
            }, $restaurant->getMenuItems());

            return [
                "id"            => $restaurant->getId(),
                "restaurant"    => $restaurant->getName(),
                "menu"          => $menu
            ];
        }, $this->restaurantRepository->fetchAll($restaurant, $item, $available));

        return new Response(!empty($customers) ? 200 : 404, $restaurants);
    }
}