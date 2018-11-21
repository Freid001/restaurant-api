<?php

namespace App;

/**
 * Class Response
 * @package App
 */
class Response
{
    /**
     * @var
     */
    private $code;

    /**
     * @var array
     */
    private $body;

    /**
     * Response constructor.
     * @param int $code
     * @param array $body
     */
    public function __construct(int $code, array $body = [])
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
     * @return array
     */
    public function getMeta(): array
    {
        switch($this->code) {
            case 200:
                $meta = ["status" => "ok"];
                break;

            case 201:
                $meta = ["status" => "created"];
                break;

            case 400:
                $meta = ["status" => "bad request"];
                break;

            case 404:
                $meta = ["status" => "not found"];
                break;

            case 500:
                $meta = ["status" => "internal server error"];
                break;

            case 503:
                $meta = ["status" => "unavailable"];
                break;

            default:
                $meta = [];
        }

        return $meta;
    }

    /**
     * @return string
     */
    public function getJsonBody(): string
    {
        return json_encode(['meta'=>$this->getMeta(), 'data'=>$this->body]);
    }
}