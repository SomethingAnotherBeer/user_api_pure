<?php
declare(strict_types=1);
namespace App\Handler;
use App\Http\Response;

class ResponseHandler
{

    private array $codes = 
    [
        ['code' => 200, 'status' => 'OK'],
        ['code' => 201, 'status' => 'OK'],
        ['code' => 400, 'status' => 'Bad Request'],
    ];

    public static function getInstance(): ResponseHandler
    {
        return new ResponseHandler();
    }


    public function handleResponse(Response $response): void
    {
        $response_message = $response->getResponseMessage();
        $response_code = $response->getCode();

        $header_code_params =
        [
            'prefix' => 'HTTP/1.1',
            'code' => $response_code,
            'postfix' => '',
        ];

        foreach ($this->codes as $code_params) {
            if ($code_params['code'] === $response_code) {
                $header_code_params['postfix'] = $code_params['status'];
            }
        }

        $header_code_string = implode(' ', $header_code_params);

        header($header_code_string);
        echo $response_message;


    }

}