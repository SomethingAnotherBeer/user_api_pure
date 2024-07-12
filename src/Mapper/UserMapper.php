<?php
declare(strict_types=1);

namespace App\Mapper;

class UserMapper extends Mapper
{

    public function getUserById(int $user_id): array|null
    {
        $sql = "SELECT id, login, email, first_name, last_name FROM users WHERE id = :user_id";
        $params = [':user_id' => $user_id];
        $result = null;

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute($params);

            $result = $statement->fetch(\PDO::FETCH_ASSOC);
        }

        catch (\PDOException $e) {
            throw $e;
        }

        return ($result) ? $result : null;

    }

    public function getUserByLogin(string $user_login): array|null
    {
        $sql = "SELECT id, login, password, email, first_name, last_name FROM users WHERE login = :user_login";
        $params = [':user_login' => $user_login];
        $result = null;

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute($params);

            $result = $statement->fetch(\PDO::FETCH_ASSOC);

        }

        catch (\PDOException $e) {
            throw $e;
        }

        return ($result) ? $result : null;

    }


    public function getUserIdByLogin(string $user_login): int|null
    {
        $sql = "SELECT id FROM users WHERE login = :user_login";
        $params = [':user_login' => $user_login];
        $result = null;

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute($params);
            $result = $statement->fetch(\PDO::FETCH_ASSOC);

        }
        catch (\PDOException $e) {
            throw $e;
        }
        
        
        return ($result) ? (int)$result['id'] : null;

    }


    public function checkUserById(int $user_id): bool
    {
        $sql = "SELECT id FROM users WHERE id = :user_id";
        $params = [':user_id' => $user_id];
        $result = null;

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute($params);
            $result = $statement->fetchColumn();
        }
        catch (\PDOException $e) {
            throw $e;
        }
    
        return ($result) ? true : false;

    }

    public function checkUserByLogin(string $user_login): bool
    {
        $sql = "SELECT id FROM users WHERE login = :user_login";
        $params = [':user_login' => $user_login];
        $result = null;

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute($params);
            $result = $statement->fetchColumn();
        }

        catch(\PDOException $e) {
            throw $e;
        }

        return ($result) ? true : false;

    }


    public function checkUserByEmail(string $user_email): bool 
    {
        $sql = "SELECT id FROM users WHERE email = :user_email";
        $params = [':user_email' => $user_email];
        $result = null;

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute($params);
            $result = $statement->fetchColumn();
        }

        catch (\PDOException $e) {
            throw $e;
        }

        return ($result) ? true : false;

    }

}