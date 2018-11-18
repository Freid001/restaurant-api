<?php

namespace App;

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

        $dsn = "mysql:host=" . getenv('DB_HOST') . "; dbname=" . getenv('DB_DATABASE') . ";";

        $this->pdo = new \pdo($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'));
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
        return json_decode(stream_get_contents($this->detectRequestBody()));
    }

    public function health()
    {

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
                        )
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
                        )
                    );

                    return $bill->pay($this->parseJsonBody());

                case "POST /bill/tip":
                    $bill = new BillRoute(
                        new BillRepository(
                            new OrderRepository($this->pdo, $this->helper),
                            new TransactionRepository($this->pdo, $this->helper)
                        )
                    );

                    return $bill->tip($this->parseJsonBody());

//                case "GET /health":
//                  break;

                default:
                    return new Response(404, ["meta" => ["status" => "not found"]]);
            }
        } catch (\Exception $e) {
            return new Response(503, ["meta" => ["status" => "unavailable"]]);
        }
    }
}