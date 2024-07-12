<?php
declare(strict_types=1);
namespace App\Service;

use App\Mapper\UserMapper;
use App\Mapper\RoleMapper;
use App\Dictionary\UserDictionary;
use App\Exception\Param\BadParamException;
use App\Exception\Param\MissedRequiredParamException;
use App\Exception\User\UserAlreadyExistsException;
use App\Exception\User\UserNotFoundException;

class UserService extends Service
{
    private UserMapper $userMapper;
    private RoleMapper $roleMapper;

    public function __construct()
    {
        parent::__construct();

        $this->userMapper =  UserMapper::getInstance();
        $this->roleMapper = RoleMapper::getInstance();
    }


    public function getUserInfo(int $user_id): array
    {   
        $user_params = $this->userMapper->getUserById($user_id);

        if (!$user_params) {
            throw new \Exception("Пользователь не найден");
        }
        
        $hidden_user_params = ['password'];
        $prepared_user_params = array_filter($user_params, fn(string $key) => !in_array($key, $hidden_user_params), ARRAY_FILTER_USE_KEY);

        return $prepared_user_params;
        
    }


    public function createUser(array $user_params): array
    {
        $user_login = $user_params['user_login'] ?? null;
        $user_password = $user_params['user_password'] ?? null;
        $user_email = $user_params['user_email'] ?? null;
        $user_firstname = $user_params['user_firstname'] ?? '';
        $user_lastname = $user_params['user_lastname'] ?? '';
        $user_roles = $user_params['user_roles'] ?? [];

        $user_roles = array_unique(array_merge($user_roles, ['ROLE_USER']));

        if (!$user_login) {
            throw new MissedRequiredParamException("Не указан логин пользователя");
        }

        if (!$user_password) {
            throw new MissedRequiredParamException("Не указан пароль пользователя");
        }

        if (!$user_email) {
            throw new MissedRequiredParamException("Не указан email пользователя");
        }
        
        $this->checkUserPassword($user_password);
        $this->checkUserEmail($user_email);
        
        if ($this->userMapper->checkUserByLogin($user_login)) {
            throw new UserAlreadyExistsException("Пользователь с данным логином уже существует в системе");
        }

        if ($this->userMapper->checkUserByEmail($user_email)) {
            throw new UserAlreadyExistsException("Пользователь с данным email уже существует в системе");
        }
        

        $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);

        $roles_ids = $this->roleMapper->getRolesIdsByNames($user_roles);

        try {

            $this->connection->beginTransaction();

            $sql = "INSERT INTO  users (login, password, email, first_name, last_name) VALUES (:login, :password, :email, :first_name, :last_name)";
            $sql_params = [':login' => $user_login, ':password' => $hashed_password, ':email' => $user_email, ':first_name' => $user_firstname, ':last_name' => $user_lastname];
            
            $statement = $this->connection->prepare($sql);
            $statement->execute($sql_params);

            $created_user_id = $this->userMapper->getUserIdByLogin($user_login);

            $sql = "INSERT INTO users_roles (user_id, role_id) VALUES (:user_id, :role_id)";
            $statement = $this->connection->prepare($sql);
            $statement->bindParam(':user_id', $created_user_id);
            
            foreach ($roles_ids as $role_id) {
                $params = [':user_id' => $created_user_id, ':role_id' => $role_id];
                $statement->execute($params);    
            }

            
            $this->connection->commit();
        }

        catch (\PDOException $e) {
            $this->connection->rollBack();
            throw $e;
        }

        return ['message' => 'Пользователь успешно создан', 'success' => true];

    }


    public function changeUser(int $user_id, array $changed_params): array
    {   
        $available_to_change = ['first_name', 'last_name'];
        $current_user_params = $this->userMapper->getUserById($user_id);
        $target_changed_params = [];

        if (!$current_user_params) {
            throw new UserNotFoundException("Пользователь не найден");
        }

        $changed_params = array_filter($changed_params, fn(string $key) => in_array($key, $available_to_change), ARRAY_FILTER_USE_KEY);

        //Получаем только те параметры пользователя, которые не равны текущим параметрам пользователя в таблице
        foreach ($changed_params as $changed_param_key => $changed_param_value) {

            if ($changed_param_value !== $current_user_params[$changed_param_key]) {
                $target_changed_params[$changed_param_key] = $changed_param_value;
            }
           
        }

        try {
            $this->connection->beginTransaction();

            foreach ($target_changed_params as $changed_param_key => $changed_param_value) {
                $sql  = "UPDATE users SET {$changed_param_key} = :changed_param_value WHERE id = :user_id";
                $params = [':changed_param_value' => $changed_param_value, ':user_id' => $current_user_params['id']];

                $statement = $this->connection->prepare($sql);
                $statement->execute($params);
            }


            $this->connection->commit();
        }

        catch (\PDOException $e) {
            $this->connection->rollBack();
            throw $e;
        }
        
        return ['message' => 'Параметры пользователя успешно изменены', 'success' => true];

    }



    public function deleteUser(int $user_id): array
    {
        if (!$this->userMapper->checkUserById($user_id)) {
            throw new UserNotFoundException("Пользователь не найден");
        }

        $sql = "DELETE FROM users WHERE id = :user_id";
        
        try {
            $statement = $this->connection->prepare($sql);
            $statement->bindValue(':user_id', $user_id);
            $statement->execute();
        }

        catch (\PDOException $e) {
            throw $e;
        }

        return ['message' => 'Пользователь успешно удален', 'success' => true];

    }



    private function checkUserPassword(string $user_password): void
    {
        $password_min_len = UserDictionary::USER_PASSWORD_MIN_LEN;

        if (mb_strlen($user_password) < $password_min_len) {
            throw new BadParamException("Пароль пользователя не может быть меньше {$password_min_len} символов");
        }
    }


    public function checkUserEmail(string $user_email): void
    {
        if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            throw new BadParamException("Невалидный email пользователя");
        }
    }


}
