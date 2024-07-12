<?php
declare(strict_types=1);
namespace App\Http;

class Response
{
    private int $code;
    private string $response_message;

    public function __construct(string $message = '', int $code = 200)
    {   
        $this->response_message = $message;
        $this->code = $code;
    }


    public function getCode(): int
    {
        return $this->code;
    }

    public function getResponseMessage(): string
    {
        return $this->response_message;
    }

}