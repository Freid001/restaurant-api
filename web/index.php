<?php

require_once('../vendor/autoload.php');

use App\Router;

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