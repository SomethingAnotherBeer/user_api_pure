<?php
declare(strict_types=1);
namespace App\Service;

use App\Exception\Auth\TokenExpiredException;
use App\Exception\Auth\UnauthorizedException;
use App\Exception\Auth\WrongAuthDataException;
use App\Exception\Param\MissedRequiredParamException;
use App\Mapper\UserMapper;
use App\Mapper\TokenMapper;
use DateTimeImmutable;

class AuthService extends Service
{
    private UserMapper $userMapper;
    private TokenMapper $tokenMapper;

    public function __construct()
    {
        parent::__construct();

        $this->userMapper = UserMapper::getInstance();
        $this->tokenMapper = TokenMapper::getInstance();
    }


    public function login(array $params): array
    {
        $login = $params['login'] ?? null;
        $password = $params['password'] ?? null;

        if (!$login) {
            throw new MissedRequiredParamException("Не указан логин пользователя");
        }

        if (!$password) {
            throw new MissedRequiredParamException("Не указан пароль пользователя");
        }

        $user_params = $this->userMapper->getUserByLogin($login);
        
        if (!$user_params) {
            throw new WrongAuthDataException("Неправильный логин или пароль");
        }

        if (!password_verify($password, $user_params['password'])) {
            throw new WrongAuthDataException("Неправильный логин или пароль");
        }

        $this->clearCurrentTokenByUserId($user_params['id']);


        $token = $this->generateToken();
        $crypted_token = crypt($token, '$6$010109a_d');

        $token_untill = (new DateTimeImmutable())->getTimestamp() + 86400;


        $sql = "INSERT INTO tokens (user_id, token_key, token_untill) VALUES (:user_id, :token_key, :token_untill)";
        $params = [':user_id' => $user_params['id'], ':token_key' => $crypted_token, ':token_untill' => $token_untill];

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute($params);

        }
        
        catch (\PDOException $e) {
            throw $e;
        }

        return ['message' => "Токен аутентификации: {$token}", 'success' => true];

    }


    public function checkAuth(string $token): array
    {   
        
        $crypted_token = crypt($token, '$6$010109a_d');
        $token_params = $this->tokenMapper->getTokenByCrypted($crypted_token);

        if (!$token_params) {
            throw new UnauthorizedException("Authentication failed");
        }

        $current_timestamp = (new DateTimeImmutable())->getTimestamp();
        $token_untill = $token_params['token_untill'];
    
        if ($current_timestamp >= $token_untill) {
            throw new TokenExpiredException("token expired");
        }

        return ['message' => 'auth checked', 'user_id' => (int)$token_params['user_id'], 'success' => true];

    }

    


    private function clearCurrentTokenByUserId(int $user_id): void
    {
        $sql = "DELETE FROM tokens WHERE user_id = :user_id";
        $params = [':user_id' => $user_id];

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute($params);
        }

        catch (\PDOException $e) {
            throw $e;
        }

    }


    private function generateToken(): string
    {
        $token_chars = [];
        $token_len = 20;

        for ($i = 0; $i < $token_len; $i++) {
            $char_group = rand(0, 2);
            $current_char = '';

            switch($char_group) {
                case 0:
                    $current_char = chr(rand(65, 90));
                break;

                case 1:
                    $current_char = chr(rand(97, 122));
                break;

                case 2:
                    $current_char = (string)rand(0, 9);
                break;
            }

            $token_chars[] = $current_char;
            if ($i % 5 === 0) {
                $token_chars[] = '-';
            }

        }

        return implode('', $token_chars);

    }


}