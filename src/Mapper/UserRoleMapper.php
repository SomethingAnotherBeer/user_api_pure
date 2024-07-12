<?php
declare(strict_types=1);
namespace App\Mapper;

class UserRoleMapper extends Mapper
{
    public function getUserRolesByUserId(int $user_id): array
    {
        $sql = "SELECT roles.role_name FROM roles INNER JOIN users_roles ON roles.id = users_roles.role_id WHERE users_roles.user_id = :user_id";
        $params = [':user_id' => $user_id];
        $result = [];

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute($params);

            $result = $statement->fetchAll();

        }
        catch (\PDOException $e) {
            throw $e;
        }

        return  array_map(fn(array $row) => $row['role_name'], $result);

    }
}