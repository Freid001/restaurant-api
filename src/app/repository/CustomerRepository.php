<?php

namespace App;


use Restaurant\Customer;
use PDO;

/**
 * Class CustomerRepository
 * @package App
 */
class CustomerRepository
{
    /**
     * @var \PDO
     */
    private $conn;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * CustomerRepository constructor.
     * @param PDO $conn
     * @param Helper $helper
     */
    public function __construct(PDO $conn, Helper $helper)
    {
        $this->conn = $conn;
        $this->helper = $helper;
    }

    /**
     * @param int $customerId
     * @return Customer|null
     */
    public function fetch(?int $customerId): ?Customer
    {
        $query = 'SELECT * FROM customer WHERE id=:id';
        $parameters = [':id' => $customerId];

        $query = $this->conn->prepare($query);
        $query->execute($parameters);

        $result = array_map([$this, 'hydrateCustomer'], $query->fetchAll(\PDO::FETCH_ASSOC));

        return isset($result[0]) ? $result[0] : null;
    }

    /**
     * @param string|null $firstName
     * @param string|null $lastName
     * @return array
     */
    public function fetchAll(?string $firstName, ?string $lastName): array
    {
        $query = 'SELECT * FROM customer';
        $parameters = [];
        $and = false;

        if (!empty($firstName)) {
            $query .= ' WHERE first_name LIKE :first_name';
            $parameters[':first_name'] = '%' . $firstName . '%';
            $and = true;
        }

        if (!empty($lastName)) {
            $query .= $and ? ' AND last_name LIKE :last_name' : ' WHERE last_name LIKE :last_name';
            $parameters[':last_name'] = '%' . $lastName . '%';
        }

        $query .= ' ORDER BY id ASC;';

        $query = $this->conn->prepare($query);
        $query->execute($parameters);

        return array_map([$this, 'hydrateCustomer'], $query->fetchAll(\PDO::FETCH_ASSOC));
    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @return int
     */
    public function create(string $firstName, string $lastName) : int
    {
        $query = 'INSERT INTO `customer` (`id`, `first_name`, `last_name`) VALUES (NULL, :first_name, :last_name)';
        $query = $this->conn->prepare($query);
        $query->execute([
            ':first_name'  => $firstName,
            ':last_name'   => $lastName,
        ]);

        return $this->conn->lastInsertId();
    }


    /**
     * @param $row
     * @return Customer|null
     */
    private function hydrateCustomer($row): ?Customer
    {
        if (!$this->helper->arrayKeysExists(
            [
                'id',
                'first_name',
                'last_name'
            ], $row ? $row : [])
        ) {
            return null;
        }

        return new Customer(
            $row['id'],
            $row['first_name'],
            $row['last_name']
        );
    }
}
