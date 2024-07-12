<?php
declare(strict_types=1);

namespace App\Model;

class HashImmutable
{
    private array $data = [];

    public function __construct(array $params)
    {
        $this->data = $params;
    }


    public static function getInstance(array $params): HashImmutable
    {
        return new HashImmutable($params);
    }


    public function __get($name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function all(): array
    {
        return $this->data;
    }
    
}