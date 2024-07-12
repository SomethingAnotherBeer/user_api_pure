<?php
declare(strict_types=1);
namespace App\Helper;


class HttpHelper
{
    public static function checkBearer(string $authorization_header): void
    {
        $authorization_header_params = explode(' ', $authorization_header);

        if ('bearer' !== strtolower($authorization_header_params[0])) {
            throw new \Exception("Неверный формат заголовка авторизации");
        }
    }

    public static function getFromBearer(string $authorization_header): string
    {
        $authorization_header_params = explode(' ', $authorization_header);

        return $authorization_header_params[1];
    }

}