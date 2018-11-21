<?php

declare(strict_types=1);

namespace Test\phpunit\Restaurant;


use PHPUnit\Framework\TestCase;
use Restaurant\Customer;

/**
 * Class CustomerTest
 * @package test\Restaurant
 */
class CustomerTest extends TestCase
{
    public function testSetId()
    {
        $customer = new Customer(1, 'john', 'smith');
        $customer->setId(2);

        $this->assertEquals(2, $customer->getId());
    }

    public function testSetFirstName()
    {
        $customer = new Customer(1, 'john', 'smith');
        $customer->setFirstName('david');
        $this->assertEquals('david', $customer->getFirstName());
    }

    public function testSetLastName()
    {
        $customer = new Customer(1, 'john', 'smith');
        $customer->setLastName('jones');
        $this->assertEquals('jones', $customer->getLastName());
    }

    public function testGetFullName()
    {
        $customer = new Customer(1, 'john', 'smith');

        $this->assertEquals('john smith', $customer->getFullName());
    }
}
