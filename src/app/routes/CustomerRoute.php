<?php

declare(strict_types=1);

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
    public function customers(?string $firstName, ?string $lastName): Response
    {
        $customers = array_map(function (Customer $customer) {
            return [
                "id" => $customer->getId(),
                "firstName" => $customer->getFirstName(),
                "lastName" => $customer->getLastName()
            ];
        }, $this->customerRepository->fetchAll($firstName, $lastName));

        return new Response(!empty($customers) ? 200 : 404, $customers);
    }

    /**
     * @param \stdClass $body
     * @return Response
     * @throws \Exception
     */
    public function createCustomer(\stdClass $body): Response
    {
        $errors = $this->validateBody($body);

        if (!empty($errors)) {
            return new Response(400, ["errors" => $errors]);
        }

        $customerId = $this->customerRepository->create($body->firstName, $body->lastName);
        $customer = $this->customerRepository->fetch($customerId);

        $response = [];
        if ($customer instanceof Customer) {
            $response = [
                "id" => $customer->getId(),
                "firstName" => $customer->getFirstName(),
                "lastName" => $customer->getLastName()
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
        if (!(strlen($body->firstName) >= 2 && strlen($body->firstName) <= 255) &&
            !in_array("firstName", $ignore)) {
            $errors['firstName'][] = "Must be between 2 than 255 characters.";
        }

        if (!(strlen($body->lastName) >= 2 && strlen($body->lastName) <= 255) &&
            !in_array("lastName", $ignore)) {
            $errors['lastName'][] = "Must be between 2 than 255 characters.";
        }

        return $errors;
    }
}