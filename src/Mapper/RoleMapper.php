<?php
declare(strict_types=1);
namespace App\Mapper;

class RoleMapper extends Mapper
{
    public function getRolesIdsByNames(array $role_names): array
    {
        $connection = $this->connection;
        $result = [];

        $role_names_params = array_map(fn($key) => ':role_name_' . $key, array_keys($role_names));
        $role_names_params = implode(', ', $role_names_params);

        $sql = "SELECT id FROM roles WHERE role_name IN ($role_names_params)";
        $statement = $connection->prepare($sql);

        
        foreach ($role_names as $role_name_key => $role_name_value) {
            $statement->bindValue(':role_name_' . $role_name_key, $role_name_value);
        }

        try {
            $statement->execute();
            $result = $statement->fetchAll();
        }
        
        catch (\PDOException $e) {
            throw $e;
        }

        return array_map(fn(array $row) =>(int)$row['id'], $result);

    }
}