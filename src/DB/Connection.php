<?php
declare(strict_types=1);
namespace App\DB;

class Connection
{

    private static array $connection_settings = [];
    private static \PDO $connection;
    private static bool $is_init = false;


    public static function getInstance(): \PDO
    {
        if (!self::$is_init) {

            self::createConnection();
            self::$is_init = true;
        }

        return self::$connection;
    }

    public static function setUp(array $params): void
    {
        self::$connection_settings = $params;
    }


    private static function createConnection(): void
    {
        $dbms = self::$connection_settings['DBMS'];
        $db_host = self::$connection_settings['db_host'];
        $db_name = self::$connection_settings['db_name'];
        $db_user = self::$connection_settings['db_user'];
        $db_password = self::$connection_settings['db_password'];

        self::$connection = new \PDO("{$dbms}:host={$db_host};dbname={$db_name}", $db_user, $db_password);
        self::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        

    }



}