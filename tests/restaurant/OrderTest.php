<?php

declare(strict_types=1);

namespace test\Restaurant;


use PHPUnit\Framework\TestCase;
use Restaurant\Customer;
use Restaurant\MenuItem;
use Restaurant\Order;
use Restaurant\OrderedItem;

/**
 * Class OrderTest
 * @package test\Restaurant
 */
class OrderTest extends TestCase
{
    public function testSetId()
    {
        $order = new Order(1, new Customer(1, 'john', 'smith'), []);
        $order->setId(2);

        $this->assertEquals(2, $order->getId());
    }

    public function testSetCustomer()
    {
        $order = new Order(1, new Customer(1, 'john', 'smith'), []);
        $order->setCustomer(new Customer(2, 'david', 'jones'));

        $this->assertInstanceOf(Customer::class, $order->getCustomer());
        $this->assertEquals(2, $order->getCustomer()->getId());
    }

    public function testSetOrderedItems()
    {
        $orderedItems = [
            new OrderedItem(1, new MenuItem(1, 'pizza', 1.00, true), 1.00, 0.00)
        ];

        $order = new Order(1, new Customer(1, 'john', 'smith'), []);
        $order->setOrderedItems($orderedItems);

        $this->assertEquals(true, is_array($order->getOrderedItems()));
        $this->assertEquals(1, $order->countOrderedItems());

        foreach($order->getOrderedItems() as $orderedItem)
        {
            $this->assertInstanceOf(OrderedItem::class, $orderedItem);
        }
    }

    public function testAppendOrderedItem()
    {
        $orderedItems = [
            new OrderedItem(1, new MenuItem(1, 'pizza', 1.00, true), 1.00, 0.00)
        ];

        $order = new Order(1, new Customer(1, 'john', 'smith'), $orderedItems);
        $order->appendOrderedItem(new OrderedItem(2, new MenuItem(2, 'burger', 2.00, true), 2.00, 0.00));

        $this->assertEquals(true, is_array($order->getOrderedItems()));
        $this->assertEquals(2, $order->countOrderedItems());

        foreach($order->getOrderedItems() as $orderedItem)
        {
            $this->assertInstanceOf(OrderedItem::class, $orderedItem);
        }
    }

    public function testGetOrderedItemByIds()
    {
        $orderedItems = [
            new OrderedItem(1, new MenuItem(1, 'pizza', 1.00, true), 1.00, 0.00)
        ];

        $order = new Order(1, new Customer(1, 'john', 'smith'), $orderedItems);
        $order->appendOrderedItem(new OrderedItem(2, new MenuItem(2, 'burger', 2.00, true), 2.00, 0.00));

        $this->assertEquals(true, is_array($order->getOrderedItems()));
        $this->assertEquals(2, $order->countOrderedItems());

        /** @var OrderedItem $orderedItem */
        foreach($order->getOrderedItems([2]) as $orderedItem)
        {
            $this->assertInstanceOf(OrderedItem::class, $orderedItem);
            $this->assertEquals(2, $orderedItem->getId());
        }
    }
}