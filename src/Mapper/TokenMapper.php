<?php
declare(strict_types=1);
namespace App\Mapper;

class TokenMapper extends Mapper
{

    public function getTokenByCrypted(string $crypted_token): array|null
    {
        $sql = "SELECT id, user_id, token_key, token_untill FROM tokens WHERE token_key = :crypted_token";
        $params = [':crypted_token' => $crypted_token];
        $result = [];

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

}