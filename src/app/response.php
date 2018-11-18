<?php

namespace App;

/**
 * Class Response
 * @package App
 */
class Response
{
    /**
     * Response constructor.
     * @param $code
     * @param $body
     */
    public function __construct($code, $body)
    {
        $this->code = $code;
        $this->body = $body;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getJsonBody(): string
    {
        return json_encode($this->body);
    }
}