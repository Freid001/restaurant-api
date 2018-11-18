<?php

namespace App;


use Restaurant\Customer;

/**
 * Class CustomerRoute
 * @package App
 */
class CustomerRoute
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * CustomerRoute constructor.
     * @param CustomerRepository $customerRepository
     */
    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param $firstName
     * @param $lastName
     * @return Response
     */
    public function customers($firstName, $lastName): Response
    {
        $customers = array_map(function(Customer $customer){
            return [
                "id"        => $customer->getId(),
                "firstName" => $customer->getFirstName(),
                "lastName"  => $customer->getLastName()
            ];
        }, $this->customerRepository->fetchAll($firstName, $lastName));

        return new Response(!empty($customers) ? 200 : 404, $customers);
    }
}