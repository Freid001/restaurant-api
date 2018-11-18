<?php

declare(strict_types=1);

namespace test\Restaurant;


use PHPUnit\Framework\TestCase;
use Restaurant\MenuItem;
use Restaurant\Restaurant;

/**
 * Class RestaurantTest
 * @package test\Restaurant
 */
class RestaurantTest extends TestCase
{
    public function testSetId()
    {
        $restaurant = new Restaurant(1, 'dominos', []);
        $restaurant->setId(2);

        $this->assertEquals(2, $restaurant->getId());
    }

    public function testSetName()
    {
        $restaurant = new Restaurant(1, 'dominos', []);
        $restaurant->setName('pizza go go');

        $this->assertEquals('pizza go go', $restaurant->getName());
    }

    public function testSetMenuItems()
    {
        $menuItems = [
            new MenuItem(1, 'pizza', 1.00, true)
        ];

        $restaurant = new Restaurant(1, 'dominos', []);
        $restaurant->setMenuItems($menuItems);

        $this->assertEquals( true, is_array($restaurant->getMenuItems()));

        foreach($restaurant->getMenuItems() as $menuItem)
        {
            $this->assertInstanceOf(MenuItem::class, $menuItem);
        }
    }

    public function testAppendMenuItems()
    {
        $restaurant = new Restaurant(1, 'dominos', []);
        $restaurant->appendMenuItem(new MenuItem(1, 'pizza', 1.00, true));

        $this->assertEquals( true, is_array($restaurant->getMenuItems()));

        foreach($restaurant->getMenuItems() as $menuItem)
        {
            $this->assertInstanceOf(MenuItem::class, $menuItem);
        }
    }
}