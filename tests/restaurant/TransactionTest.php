<?php

declare(strict_types=1);

namespace test\Restaurant;


use PHPUnit\Framework\TestCase;
use Restaurant\Customer;
use Restaurant\Order;
use Restaurant\Transaction;

/**
 * Class TransactionTest
 * @package test\Restaurant
 */
class TransactionTest extends TestCase
{
    public function testSetId()
    {
        $customer = new Customer(1, 'john', 'smith');

        $transaction = new Transaction(1, $customer, new Order(1, $customer, []), false, 1.00);
        $transaction->setId(2);

        $this->assertEquals(2, $transaction->getId());
    }

    public function testSetName()
    {
        $customer = new Customer(1, 'john', 'smith');

        $transaction = new Transaction(1, $customer, new Order(1, $customer, []), false, 1.00);
        $transaction->setCustomer(new Customer(2, 'david', 'jones'));

        $this->assertInstanceOf(Customer::class, $transaction->getCustomer());
        $this->assertEquals(2, $transaction->getCustomer()->getId());
    }

    public function testSetOrder()
    {
        $customer = new Customer(1, 'john', 'smith');

        $transaction = new Transaction(1, $customer, new Order(1, $customer, []), false, 1.00);
        $transaction->setOrder(new Order(2, $customer, []));

        $this->assertInstanceOf(Order::class, $transaction->getOrder());
        $this->assertEquals(2, $transaction->getOrder()->getId());
    }

    public function testSetPaid()
    {
        $customer = new Customer(1, 'john', 'smith');

        $transaction = new Transaction(1, $customer, new Order(1, $customer, []), false, 1.00);
        $transaction->setPaid(2.00);

        $this->assertEquals(2.00, $transaction->getPaid());
    }

    public function testSetTip()
    {
        $customer = new Customer(1, 'john', 'smith');

        $transaction = new Transaction(1, $customer, new Order(1, $customer, []), false, 1.00);
        $transaction->setTip(true);

        $this->assertEquals(true, $transaction->isTip());
    }
}
