<?php

declare(strict_types=1);

namespace test\Restaurant;


use PHPUnit\Framework\TestCase;
use Restaurant\MenuItem;
use Restaurant\OrderedItem;

/**
 * Class OrderedItemTest
 * @package test\Restaurant
 */
class OrderedItemTest extends TestCase
{
    public function testSetId()
    {
        $orderItem = new OrderedItem(1, new MenuItem(1, 'pizza', 1.00, true), 1.00, 0.00);
        $orderItem->setId(2);

        $this->assertEquals(2, $orderItem->getId());
    }

    public function testSetItem()
    {
        $orderItem = new OrderedItem(1, new MenuItem(1, 'pizza', 1.00, true), 1.00, 0.00);
        $orderItem->setItem(new MenuItem(2, 'burger', 2.00, true));

        $this->assertInstanceOf(MenuItem::class, $orderItem->getItem());
        $this->assertEquals('burger', $orderItem->getItem()->getItem());
    }

    public function testSetPriceCharged()
    {
        $orderItem = new OrderedItem(1, new MenuItem(1, 'pizza', 1.00, true), 1.00, 0.00);
        $orderItem->setPriceCharged(0.90);

        $this->assertEquals(0.90, $orderItem->getPriceCharged());
    }

    public function testSetDiscountCharged()
    {
        $orderItem = new OrderedItem(1, new MenuItem(1, 'pizza', 1.00, true), 1.00, 0.00);
        $orderItem->setDiscount(0.10);

        $this->assertEquals(0.10, $orderItem->getDiscount());
    }
}