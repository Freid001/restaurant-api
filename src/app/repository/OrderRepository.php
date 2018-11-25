<?php

namespace App;


use Restaurant\MenuItem;
use Restaurant\Order;
use Restaurant\OrderedItem;
use Restaurant\Customer;
use PDO;

/**
 * Class OrderRepository
 * @package App
 */
class OrderRepository
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
     * @param int $orderId
     * @return Order|null
     */
    public function fetch(int $orderId) : ?Order
    {
        $result = $this->fetchAll($orderId,  null);

        return isset($result[0]) ? $result[0] : null;
    }

    /**
     * @param int|null $orderId
     * @param int|null $customerId
     * @return array
     */
    public function fetchAll(?int $orderId, ?int $customerId): array
    {
        $query = 'SELECT oo.*, 
                         o.customer_id,
                         c.first_name,
                         c.last_name, 
                         m.item, 
                         m.price AS original_price, 
                         m.available 
                  FROM ordered AS oo 
                  LEFT JOIN `order` AS o ON oo.order_id=o.id
                  LEFT JOIN menu AS m ON oo.item_id=m.id
                  LEFT JOIN customer AS c ON o.customer_id=c.id';
        $parameters = [];
        $and = false;

        if (!empty($customerId)) {
            $query .= ' WHERE o.customer_id = :customer_id';
            $parameters[':customer_id'] = $customerId;
            $and = true;
        }

        if (!empty($orderId)) {
            $query .= $and ? ' AND o.id = :id' : ' WHERE o.id = :id';
            $parameters[':id'] = $orderId;
        }

        $query .= ' ORDER BY id ASC;';

        $query = $this->conn->prepare($query);
        $query->execute($parameters);

        return $this->helper->arrayRemoveAssocKeys(
            array_reduce(
                $query->fetchAll(\PDO::FETCH_ASSOC),
                [$this, 'hydrateOrder'],
                []
            )
        );
    }

    /**
     * @param int $customerId
     * @return int
     */
    public function create(int $customerId) : int
    {
        $query = 'INSERT INTO `order` (`id`, `customer_id`) VALUES (NULL, :customerId)';
        $query = $this->conn->prepare($query);
        $query->execute([':customerId' => $customerId]);

        return $this->conn->lastInsertId();
    }

    /**
     * @param int $orderId
     * @param int $itemId
     * @param float $priceCharged
     * @param float|null $discount
     * @return int
     */
    public function appendOrderedItem(int $orderId, int $itemId, float $priceCharged, ?float $discount) : int
    {
        $query = 'INSERT INTO `ordered` (`id`, `order_id`, `item_id`, `price_charged`, `discount`) VALUES (NULL, :orderId, :itemId, :priceCharged, :discount)';
        $query = $this->conn->prepare($query);
        $query->execute([
            ':orderId'      => $orderId,
            ':itemId'       => $itemId,
            ':priceCharged' => $priceCharged,
            ':discount'     => is_null($discount) ? 0.00 : $discount
        ]);

        return $this->conn->lastInsertId();
    }

    /**
     * @param int $itemId
     * @return int
     */
    public function removeOrderedItem(int $itemId) : int
    {
        $query = 'DELETE FROM `ordered` WHERE id = :item_id';
        $query = $this->conn->prepare($query);
        $query->execute([':item_id' => $itemId]);

        return $itemId;
    }

    /**
     * @param int $orderId
     * @return int
     */
    public function delete(int $orderId) : int
    {
        $query = 'DELETE FROM `ordered` WHERE order_id = :order_id';
        $query = $this->conn->prepare($query);
        $query->execute([':order_id' => $orderId]);

        $query = 'DELETE FROM `order` WHERE id = :order_id';
        $query = $this->conn->prepare($query);
        $query->execute([':order_id' => $orderId]);

        return $orderId;
    }

    /**
     * @param Order[] $orders
     * @param $row
     * @return array
     */
    private function hydrateOrder(array $orders, $row): array
    {
        if (!$this->helper->arrayKeysExists([
            'order_id',
            'customer_id',
            'first_name',
            'last_name',
            'id',
            'item_id',
            'item',
            'original_price',
            'available',
            'price_charged',
            'discount'
        ], is_array($row) ? $row : [])
        ) {
            return [];
        }

        if (!isset($orders[$row['order_id']])) {
            $orders[$row['order_id']] = new Order(
                $row['order_id'],
                new Customer(
                    $row['customer_id'],
                    $row['first_name'],
                    $row['last_name']
                )
            );
        }

        $orders[$row['order_id']]->appendOrderedItem(
            new OrderedItem(
                $row['id'],
                new MenuItem(
                    $row['item_id'],
                    $row['item'],
                    $row['original_price'],
                    $row['available']
                ),
                $row['price_charged'],
                $row['discount']
            )
        );

        return $orders;
    }
}