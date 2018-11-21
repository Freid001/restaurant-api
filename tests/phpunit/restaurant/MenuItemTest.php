<?php

declare(strict_types=1);

namespace Test\phpunit\Restaurant;


use PHPUnit\Framework\TestCase;
use Restaurant\MenuItem;

/**
 * Class MenuItemTest
 * @package test\Restaurant
 */
class MenuItemTest extends TestCase
{
    public function testSetId()
    {
        $menuItem = new MenuItem(1, 'pizza', 1.00, false);
        $menuItem->setId(2);
        $this->assertEquals(2, $menuItem->getId());
    }

    public function testSetItem()
    {
        $menuItem = new MenuItem(1, 'pizza', 1.00, false);
        $menuItem->setItem('burger');
        $this->assertEquals('burger', $menuItem->getItem());
    }

    public function testSetPrice()
    {
        $menuItem = new MenuItem(1, 'pizza', 1.00, false);
        $menuItem->setPrice(2.00);
        $this->assertEquals(2.00, $menuItem->getPrice());
    }

    public function testSetAvailable()
    {
        $menuItem = new MenuItem(1, 'pizza', 1.00, false);
        $menuItem->setAvailable(true);
        $this->assertEquals(true, $menuItem->isAvailable());
    }

    public function testGetDiscountPrice()
    {
        $menuItem = new MenuItem(1, 'pizza', 1.00, false);
        $this->assertEquals(0.75, $menuItem->getDiscountPrice(0.25));
    }

    public function testGetNullDiscountPrice()
    {
        $menuItem = new MenuItem(1, 'pizza', 1.00, false);
        $this->assertEquals(1.00, $menuItem->getDiscountPrice(null));
    }
}
