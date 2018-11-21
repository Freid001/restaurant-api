<?php

declare(strict_types=1);

namespace Test\phpunit\Restaurant;


use PHPUnit\Framework\TestCase;
use Restaurant\Bill;
use Restaurant\Customer;
use Restaurant\MenuItem;
use Restaurant\Order;
use Restaurant\OrderedItem;
use Restaurant\Transaction;

/**
 * Class BillTest
 * @package test\Restaurant
 */
class BillTest extends TestCase
{
    public function testSetOrder()
    {
        $customer = new Customer(1, 'john', 'smith');
        $order  = new Order(1, $customer, []);
        $bill = new Bill($order,[]);

        $orderedItems = [
            new OrderedItem(1, new MenuItem(1, 'pizza', 1.00, true), 1.00, 0.00)
        ];

        $bill->setOrder(new Order(1, $customer, $orderedItems));

        $this->assertInstanceOf(Order::class, $bill->getOrder());
        $this->assertEquals(1, $bill->getOrder()->getId());
    }

    public function testSetTransactions()
    {
        $customer = new Customer(1, 'john', 'smith');
        $order  = new Order(1, $customer, []);
        $bill = new Bill($order,[]);

        $transactions = [
            new Transaction(1, $customer, $order, false, 1.00)
        ];

        $bill->setTransactions($transactions);

        $this->assertEquals(true, is_array($bill->getTransactions()));

        foreach($bill->getTransactions() as $transaction)
        {
            $this->assertInstanceOf(Transaction::class, $transaction);
        }
    }

    public function testGetTotalPaid()
    {
        $customer = new Customer(1, 'john', 'smith');

        $orderedItems = [
            new OrderedItem(1, new MenuItem(1, 'pizza', 1.00, true), 1.00, 0.00),
            new OrderedItem(2, new MenuItem(2, 'burger', 2.00, true), 2.00, 0.00)
        ];

        $order  = new Order(1, $customer, $orderedItems);

        $transactions = [
            new Transaction(1, $customer, $order, false, 1.00),
            new Transaction(2, $customer, $order, false, 2.00)
        ];

        $bill = new Bill($order,$transactions);

        $this->assertEquals(3.00, $bill->getTotalPaid());
    }

    public function testGetTotalCharged()
    {
        $customer = new Customer(1, 'john', 'smith');

        $orderedItems = [
            new OrderedItem(1, new MenuItem(1, 'pizza', 1.00, true), 1.00, 0.00),
            new OrderedItem(2, new MenuItem(2, 'burger', 2.00, true), 2.00, 0.00)
        ];

        $order  = new Order(1, $customer, $orderedItems);

        $transactions = [
            new Transaction(1, $customer, $order, false, 1.00),
            new Transaction(2, $customer, $order, false, 2.00)
        ];

        $bill = new Bill($order,$transactions);

        $this->assertEquals(3.00, $bill->getTotalCharged());
    }

    public function testGetTotalDue()
    {
        $customer = new Customer(1, 'john', 'smith');

        $orderedItems = [
            new OrderedItem(1, new MenuItem(1, 'pizza', 1.00, true), 1.00, 0.00),
            new OrderedItem(2, new MenuItem(2, 'burger', 2.00, true), 2.00, 0.00)
        ];

        $order  = new Order(1, $customer, $orderedItems);

        $transactions = [
            new Transaction(1, $customer, $order, false, 1.00)
        ];

        $bill = new Bill($order,$transactions);

        $this->assertEquals(2.00, $bill->getTotalDue());
    }

    public function testGetTotalDiscount()
    {
        $customer = new Customer(1, 'john', 'smith');

        $orderedItems = [
            new OrderedItem(1, new MenuItem(1, 'pizza', 1.00, true), 0.75, 0.25),
            new OrderedItem(2, new MenuItem(2, 'burger', 2.00, true), 1.00, 0.50)
        ];

        $order  = new Order(1, $customer, $orderedItems);

        $transactions = [];

        $bill = new Bill($order,$transactions);

        $this->assertEquals(0.42, round($bill->getTotalDiscount(),2));
    }

    public function testGetTotalOriginalPrice()
    {
        $customer = new Customer(1, 'john', 'smith');

        $orderedItems = [
            new OrderedItem(1, new MenuItem(1, 'pizza', 1.00, true), 0.75, 0.25),
            new OrderedItem(2, new MenuItem(2, 'burger', 2.00, true), 1.00, 0.50)
        ];

        $order  = new Order(1, $customer, $orderedItems);

        $transactions = [];

        $bill = new Bill($order,$transactions);

        $this->assertEquals(3.00, $bill->getTotalOriginalPrice());
    }

    public function testGetTotalSavings()
    {
        $customer = new Customer(1, 'john', 'smith');

        $orderedItems = [
            new OrderedItem(1, new MenuItem(1, 'pizza', 1.00, true), 0.75, 0.25),
            new OrderedItem(2, new MenuItem(2, 'burger', 2.00, true), 1.00, 0.50)
        ];

        $order  = new Order(1, $customer, $orderedItems);

        $transactions = [
            new Transaction(1, $customer, $order, false, 0.75),
            new Transaction(2, $customer, $order, false, 1.00)
        ];

        $bill = new Bill($order,$transactions);

        $this->assertEquals(1.25, $bill->getTotalSavings());
    }

    public function testGetTotalTip()
    {
        $customer = new Customer(1, 'john', 'smith');

        $orderedItems = [
            new OrderedItem(1, new MenuItem(1, 'pizza', 1.00, true), 1.00, 0.00),
            new OrderedItem(2, new MenuItem(2, 'burger', 2.00, true), 2.00, 0.00)
        ];

        $order  = new Order(1, $customer, $orderedItems);

        $transactions = [
            new Transaction(1, $customer, $order, false, 1.00),
            new Transaction(3, $customer, $order, false, 2.00),
            new Transaction(2, $customer, $order, true, 0.25),
        ];

        $bill = new Bill($order,$transactions);

        $this->assertEquals(0.25, $bill->getTotalTip());
    }

    public function testGetOpenState()
    {
        $customer = new Customer(1, 'john', 'smith');

        $orderedItems = [
            new OrderedItem(1, new MenuItem(1, 'pizza', 1.00, true), 1.00, 0.00),
            new OrderedItem(2, new MenuItem(2, 'burger', 2.00, true), 2.00, 0.00)
        ];

        $order  = new Order(1, $customer, $orderedItems);

        $transactions = [];

        $bill = new Bill($order,$transactions);

        $this->assertEquals("open", $bill->getState());
    }
    
    public function testGetClosedState()
    {
        $customer = new Customer(1, 'john', 'smith');

        $orderedItems = [
            new OrderedItem(1, new MenuItem(1, 'pizza', 1.00, true), 1.00, 0.00),
            new OrderedItem(2, new MenuItem(2, 'burger', 2.00, true), 2.00, 0.00)
        ];

        $order  = new Order(1, $customer, $orderedItems);

        $transactions = [
            new Transaction(1, $customer, $order, false, 1.00),
            new Transaction(3, $customer, $order, false, 2.00)
        ];

        $bill = new Bill($order,$transactions);

        $this->assertEquals("closed", $bill->getState());
    }
}
