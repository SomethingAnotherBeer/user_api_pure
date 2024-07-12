<?php
declare(strict_types=1);
namespace App\Handler;

class ParamHandler
{

    public static function getInstance(): ParamHandler
    {
        return new ParamHandler();
    }

    public function prepareParams(array $params): array
    {
        $prepared_params = [];

        foreach ($params as $param_key => $param_value) {
            $prepared_params[$param_key] = (is_numeric($param_value)) ? (int)$param_value : $param_value;
        }

        return $prepared_params;
    }


}