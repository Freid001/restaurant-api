<?php

namespace App;


use Restaurant\MenuItem;
use Restaurant\Restaurant;
use PDO;

/**
 * Class RestaurantRepository
 * @package App
 */
class RestaurantRepository
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
     * @param int|null $restaurantId
     * @return Restaurant|null
     */
    public function fetch(?int $restaurantId): ?Restaurant
    {
        $query = 'SELECT m.*, 
                         r.restaurant 
                  FROM menu AS m 
                  LEFT JOIN restaurant AS r ON m.restaurant_id=r.id
                  WHERE r.id=:id';
        $parameters = [':id' => $restaurantId];

        $query = $this->conn->prepare($query);
        $query->execute($parameters);

        $result = $this->helper->arrayRemoveAssocKeys(
            array_reduce(
                $query->fetchAll(\PDO::FETCH_ASSOC),
                [$this, 'hydrateRestaurant'],
                []
            )
        );

        return isset($result[0]) ? $result[0] : null;
    }

    /**
     * @param string|null $restaurant
     * @param string|null $item
     * @param bool|null $available
     * @return Restaurant[]
     */
    public function fetchAll(?string $restaurant, ?string $item, ?bool $available) : array
    {
        $query = 'SELECT m.*, 
                         r.restaurant 
                  FROM menu AS m 
                  LEFT JOIN restaurant AS r ON m.restaurant_id=r.id';
        $parameters = [];
        $and = false;

        if (!empty($restaurant)) {
            $query .= ' WHERE r.restaurant LIKE :restaurant';
            $parameters[':restaurant'] = '%' . $restaurant . '%';
            $and = true;
        }

        if (!empty($item)) {
            $query .= $and ? ' AND m.item LIKE :item' : ' WHERE m.item LIKE :item';
            $parameters[':item'] = '%' . $item . '%';
            $and = true;
        }

        if (is_bool($available)) {
            $query .= $and ? ' AND m.available = :available' : ' WHERE m.available = :available';
            $parameters[':available'] = $available;
        }

        $query .= ' ORDER BY id ASC;';

        $query = $this->conn->prepare($query);
        $query->execute($parameters);

        return $this->helper->arrayRemoveAssocKeys(
            array_reduce(
                $query->fetchAll(\PDO::FETCH_ASSOC),
                [$this, 'hydrateRestaurant'],
                []
            )
        );
    }

    /**
     * @param int $itemId
     * @return MenuItem|null
     */
    public function fetchMenuItem(?int $itemId)
    {
        $query = 'SELECT * FROM menu WHERE id=:id';
        $query = $this->conn->prepare($query);
        $query->execute([':id'=>$itemId]);

        $result = $query->fetch(\PDO::FETCH_ASSOC);

        if(!$this->helper->arrayKeysExists([
            'restaurant_id',
            'id',
            'item',
            'price',
            'available'
        ], $result ? $result : [])
        ){
            return null;
        }

        return new MenuItem(
            $result['id'],
            $result['item'],
            $result['price'],
            $result['available']
        );
    }

    /**
     * @param string $restaurant
     * @param string $cuisine
     * @return string
     */
    public function createRestaurant(string $restaurant, string $cuisine) : int
    {
        $query = 'INSERT INTO `restaurant` (`id`, `restaurant`, `cuisine`) VALUES (NULL, :restaurant, :cuisine)';
        $query = $this->conn->prepare($query);
        $query->execute([
            ':restaurant'   => $restaurant,
            ':cuisine'      => $cuisine,
        ]);

        return $this->conn->lastInsertId();
    }

    /**
     * @param int $restaurantId
     * @param array $items
     * @return int
     */
    public function createdMenu(int $restaurantId, array $items): int
    {
        $values = [];
        $parameters = [];
        foreach ($items as $item) {
            if($item instanceof \stdClass) {
                $values[] = '(NULL, ?, ?, ?, ?)';
                $parameters[] = $restaurantId;
                $parameters[] = $item->item;
                $parameters[] = $item->price;
                $parameters[] = $item->available ? 1 : 0;
            }
        }

        $query = 'INSERT INTO `menu` (`id`, `restaurant_id`, `item`, `price`, `available`) VALUES ' . implode(', ', $values) . ';';
        $query = $this->conn->prepare($query);
        $query->execute($parameters);

        return $restaurantId;
    }

    /**
     * @param array|null $restaurants
     * @param $row
     * @return array
     */
    private function hydrateRestaurant(array $restaurants, $row) : array
    {
        if(!$this->helper->arrayKeysExists([
            'restaurant_id',
            'restaurant',
            'id',
            'item',
            'price',
            'available'
        ], is_array($row) ? $row : [])
        ){
            return [];
        }

        if (!isset($restaurants[$row['restaurant_id']])) {
            $restaurants[$row['restaurant_id']] = new Restaurant(
                $row['restaurant_id'],
                $row['restaurant']
            );
        }

        $restaurants[$row['restaurant_id']]->appendMenuItem(
            new MenuItem(
                $row['id'],
                $row['item'],
                $row['price'],
                $row['available']
            )
        );

        return $restaurants;
    }

}