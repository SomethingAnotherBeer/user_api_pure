<?php
declare(strict_types=1);
namespace App\Service;
use App\DB\Connection;

abstract class Service
{
    protected \PDO $connection;

    public function __construct()
    {   
        $this->connection = Connection::getInstance();
    }


    public static function getInstance(): static
    {
        return new static();
    }
}
