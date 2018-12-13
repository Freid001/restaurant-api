<?php

declare(strict_types=1);

require_once('../vendor/autoload.php');

use App\Router;

error_reporting(E_ERROR | E_WARNING | E_PARSE);

$router = new Router(
    isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null,
    isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : "",
    isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null
);

/** @var \App\Response $response */
$response = $router->route();

if(!empty($response->getCode())) {
    header('Content-Type: application/json');
    http_response_code($response->getCode());
}

if(!empty($response->getJsonBody())) {
    echo $response->getJsonBody();
}