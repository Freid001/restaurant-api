<?php

namespace App;


use Restaurant\Customer;
use PDO;
use Restaurant\MenuItem;
use Restaurant\Order;
use Restaurant\OrderedItem;
use Restaurant\Transaction;

/**
 * Class TransactionRepository
 * @package App
 */
class TransactionRepository
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
     * @param int|null $orderId
     * @param int|null $customerId
     * @return array
     */
    public function fetchAll(?int $orderId, ?int $customerId): array
    {
        $query = 'SELECT t.*,
                         c.id AS payee_id,
                         c.first_name AS payee_first_name,
                         c.last_name AS payee_last_name,
                         o.customer_id,
                         cc.first_name,
                         cc.last_name,
                         oo.id AS ordered_id,
                         oo.order_id, 
                         oo.price_charged,
                         oo.discount, 
                         m.id AS item_id,
                         m.item, 
                         m.price AS original_price, 
                         m.available
                  FROM transaction AS t 
                  LEFT JOIN `ordered` AS oo ON t.ordered_id=oo.id
                  LEFT JOIN `order` AS o ON oo.order_id=o.id
                  LEFT JOIN menu AS m ON oo.item_id=m.id
                  LEFT JOIN customer AS c ON t.customer_id=c.id
                  LEFT JOIN customer AS cc ON o.customer_id=cc.id';
        $parameters = [];
        $and = false;

        if (!empty($customerId)) {
            $query .= ' WHERE t.customer_id =:customer_id';
            $parameters[':customer_id'] = $customerId;
            $and = true;
        }

        if (!empty($orderId)) {
            $query .= $and ? ' AND o.id =:id' : ' WHERE o.id =:id';
            $parameters[':id'] = $orderId;
        }

        $query .= ' ORDER BY t.id ASC;';

        $query = $this->conn->prepare($query);
        $query->execute($parameters);

        return array_map([$this, 'hydrateTransaction'], $query->fetchAll(\PDO::FETCH_ASSOC));
    }

    /**
     * @param int $customerId
     * @param int $orderedId
     * @param bool $tip
     * @param float $paid
     * @return int
     */
    public function create(int $customerId, int $orderedId, bool $tip, float $paid) : int
    {
        $query = 'INSERT INTO `transaction` (`id`, `customer_id`, `ordered_id`, `tip`, `paid`) VALUES (NULL, :customer_id, :ordered_id, :tip, :paid)';
        $query = $this->conn->prepare($query);
        $query->execute([
            ':customer_id'  => $customerId,
            ':ordered_id'   => $orderedId,
            ':tip'          => 0,
            ':paid'         => $paid
        ]);

        return $this->conn->lastInsertId();
    }

    /**
     * @param $row
     * @return Transaction|null
     */
    private function hydrateTransaction($row): ?Transaction
    {
        if (!$this->helper->arrayKeysExists(
            [
                'id',
                'payee_id',
                'payee_first_name',
                'payee_last_name',
                'order_id',
                'customer_id',
                'first_name',
                'last_name',
                'tip',
                'paid'
            ], $row ? $row : [])
        ) {
            return null;
        }

        $order = new Order(
            $row['order_id'],
            new Customer(
                $row['customer_id'],
                $row['first_name'],
                $row['last_name']
            ));

        $order->appendOrderedItem(new OrderedItem(
            $row['ordered_id'],
            new MenuItem(
                $row['item_id'],
                $row['item'],
                $row['original_price'],
                $row['available']
            ),
            $row['price_charged'],
            $row['discount']
        ));

        return new Transaction(
            $row['id'],
            new Customer(
                $row['payee_id'],
                $row['payee_first_name'],
                $row['payee_last_name']
            ),
            $order,
            $row['tip'],
            $row['paid']
        );
    }
}
