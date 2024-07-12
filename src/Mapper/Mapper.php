<?php
declare(strict_types=1);

namespace App\Mapper;
use App\DB\Connection;

abstract class Mapper
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