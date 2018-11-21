<?php

namespace App;

use Restaurant\Restaurant;

/**
 * Class Routes
 * @package App
 */
class Router
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var array|null
     */
    private $parameters = [];

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var bool
     */
    private $dbStatus = false;

    /**
     * Router constructor.
     * @param string $method
     * @param string $uri
     * @param string|null $queryString
     */
    public function __construct(?string $method, string $uri, ?string $queryString = null)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->helper = new Helper();

        if (!empty($queryString)) {
            $this->parameters = array_reduce(explode("&", $queryString), function ($parameters, $queryString) {
                $query = explode("=", $queryString);
                $parameters[$query[0]] = $query[1];
                return $parameters;
            }, []);
        }

        $this->conn();
    }

    public function conn(): void
    {
        try {
            $dsn = "mysql:host=" . getenv('DB_HOST') . "; dbname=" . getenv('DB_DATABASE') . ";";
            $this->pdo = new \pdo($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'));
            $this->dbStatus = true;
        }catch (\Exception $e) {
            $this->dbStatus = false;
        }
    }

    /**
     * @param $key
     * @return string|null
     */
    public function getParameter($key): ?string
    {
        $parameter = null;
        if (isset($this->parameters[$key])) {
            $parameter = $this->parameters[$key];
        }
        return $parameter;
    }

    /**
     * @return bool|resource
     */
    public function detectRequestBody()
    {
        $rawInput = fopen('php://input', 'r');
        $tempStream = fopen('php://temp', 'r+');
        stream_copy_to_stream($rawInput, $tempStream);
        rewind($tempStream);

        return $tempStream;
    }

    /**
     * @return mixed
     */
    public function parseJsonBody()
    {
        $body = json_decode((string) stream_get_contents($this->detectRequestBody()));

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new \stdClass();
        }

        return $body;
    }

    /**
     * @return Response
     */
    public function route() : Response
    {
        try {
            switch ($this->method . " " . $this->uri) {
                case "GET /customers":
                    $customer = new CustomerRoute(new CustomerRepository($this->pdo, $this->helper));

                    return $customer->customers(
                        $this->getParameter("firstName"),
                        $this->getParameter("lastName")
                    );

                case "POST /customer":
                    $customer = new CustomerRoute(new CustomerRepository($this->pdo, $this->helper));

                    return $customer->createCustomer($this->parseJsonBody());

                case "GET /restaurants":
                    $restaurant = new RestaurantRoute(new RestaurantRepository($this->pdo, $this->helper));

                    $available = null;
                    if ($this->getParameter("available") == "true") {
                        $available = true;
                    }

                    if ($this->getParameter("available") == "false") {
                        $available = false;
                    }

                    return $restaurant->restaurants(
                        (int)$this->getParameter("restaurant"),
                        (int)$this->getParameter("item"),
                        $available
                    );

                case "POST /restaurant":
                    $restaurant = new RestaurantRoute(new RestaurantRepository($this->pdo, $this->helper));

                    return $restaurant->createRestaurant($this->parseJsonBody());

                case "GET /orders":
                    $orders = new OrderRoute(
                        new OrderRepository($this->pdo, $this->helper),
                        new RestaurantRepository($this->pdo, $this->helper),
                        new CustomerRepository($this->pdo, $this->helper)
                    );

                    return $orders->orders(
                        (int)$this->getParameter("orderId"),
                        (int)$this->getParameter("customerId")
                    );

                case "POST /order":
                    $order = new OrderRoute(
                        new OrderRepository($this->pdo, $this->helper),
                        new RestaurantRepository($this->pdo, $this->helper),
                        new CustomerRepository($this->pdo, $this->helper)
                    );

                    return $order->createOrder($this->parseJsonBody());

                case "POST /order/item":
                    $order = new OrderRoute(
                        new OrderRepository($this->pdo, $this->helper),
                        new RestaurantRepository($this->pdo, $this->helper),
                        new CustomerRepository($this->pdo, $this->helper)
                    );

                    return $order->appendItem($this->parseJsonBody());

                case "DELETE /order/item":
                    $order = new OrderRoute(
                        new OrderRepository($this->pdo, $this->helper),
                        new RestaurantRepository($this->pdo, $this->helper),
                        new CustomerRepository($this->pdo, $this->helper)
                    );

                    return $order->removeItem(
                        (int)$this->getParameter("orderId"),
                        (int)$this->getParameter("item_id")
                    );

                case "DELETE /order":
                    $order = new OrderRoute(
                        new OrderRepository($this->pdo, $this->helper),
                        new RestaurantRepository($this->pdo, $this->helper),
                        new CustomerRepository($this->pdo, $this->helper)
                    );

                    return $order->deleteOrder(
                        (int)$this->getParameter("orderId")
                    );

                case "GET /bills":
                    $bill = new BillRoute(
                        new BillRepository(
                            new OrderRepository($this->pdo, $this->helper),
                            new TransactionRepository($this->pdo, $this->helper)
                        ),
                        new CustomerRepository($this->pdo, $this->helper)
                    );

                    return $bill->bills(
                        (int)$this->getParameter("orderId"),
                        (int)$this->getParameter("customerId"),
                        (int)$this->getParameter("payeeId")
                    );

                case "POST /bill/pay":
                    $bill = new BillRoute(
                        new BillRepository(
                            new OrderRepository($this->pdo, $this->helper),
                            new TransactionRepository($this->pdo, $this->helper)
                        ),
                        new CustomerRepository($this->pdo, $this->helper)
                    );

                    return $bill->pay($this->parseJsonBody());

                case "POST /bill/tip":
                    $bill = new BillRoute(
                        new BillRepository(
                            new OrderRepository($this->pdo, $this->helper),
                            new TransactionRepository($this->pdo, $this->helper)
                        ),
                        new CustomerRepository($this->pdo, $this->helper)
                    );

                    return $bill->tip($this->parseJsonBody());

                case "GET /health":
                    return new Response(200, ["status" => !$this->dbStatus ? "down" : "up"]);

                default:
                    return new Response(404);
            }
        } catch (\Exception $e) {
            return new Response(503);
        }
    }
}