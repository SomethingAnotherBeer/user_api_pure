<?php
declare(strict_types=1);

function createRoles(PDO $connection, array $roles): void
{
    $sql = "INSERT INTO roles (role_name) VALUES (:role_name)";
    $statement = $connection->prepare($sql);

    foreach ($roles as $role) {
        $statement->bindValue(':role_name', $role);
        $statement->execute();
    }

}

function createUser(PDO $connection, array $user_params): void
{
    $login = $user_params['login'];
    $password = $user_params['password'];
    $email = $user_params['email'];
    $first_name = $user_params['first_name'];
    $last_name = $user_params['last_name'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (login, password, email, first_name, last_name) VALUES (:login, :password, :email, :first_name, :last_name)";
    
    $params = 
    [
        ':login' => $login,
        ':password' => $hashed_password,
        ':email' => $email,
        ':first_name' => $first_name,
        ':last_name' => $last_name,
    ];

    $statement = $connection->prepare($sql);
    $statement->execute($params);
    

    $created_user = getUserByLogin($connection, $login);
    $created_user_id = $created_user['id'];
    
    $roles = $user_params['roles'];

    $roles_keys = array_map(fn($key) => ':role_key_' . $key, array_keys($roles));

    $roles_keys_str = implode(', ', $roles_keys);
    
    $sql = "SELECT id FROM roles WHERE role_name IN ($roles_keys_str)";
    $statement = $connection->prepare($sql);
    
    foreach ($roles as $role_key => $role_value) {
        $statement->bindValue(':role_key_'.$role_key, $role_value);
    } 
    

    $statement->execute();
    
    $roles_ids = array_map(fn(array $row) => $row['id'], $statement->fetchAll());
    

    
    $sql = "INSERT INTO users_roles (user_id, role_id) VALUES (:user_id, :role_id)";
    $statement = $connection->prepare($sql);

    
    foreach ($roles_ids as $role_id) {
        $params = [':user_id' => $created_user_id, ':role_id' => $role_id];
        $statement->execute($params);
    }

}


function getUserByLogin(PDO $connection, string $user_login): array
{
    $sql = "SELECT * FROM users WHERE login = :user_login";
    $params = [':user_login' => $user_login];

    $statement = $connection->prepare($sql);
    $statement->execute($params);

    return $statement->fetch(\PDO::FETCH_ASSOC);
}



$connection_params =
[

    'DBMS' => 'mysql',
    'db_host' => 'mariadb',
    'db_name' => 'user_api_db',
    'db_user' => 'root',
    'db_password' => '111',
];

$dbms = $connection_params['DBMS'];
$host = $connection_params['db_host'];
$db_name = $connection_params['db_name'];
$db_user = $connection_params['db_user'];
$db_password = $connection_params['db_password'];

$connection = new PDO("{$dbms}:host={$host};dbname={$db_name}", $db_user, $db_password);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $connection->beginTransaction();

    $delete_users_sql = "DELETE FROM  users";
    $delete_roles_sql = "DELETE FROM roles";

    $statement = $connection->prepare($delete_users_sql);
    $statement->execute();

    $statement = $connection->prepare($delete_roles_sql);
    $statement->execute();

    $roles = ['ROLE_ADMIN', 'ROLE_USER'];

    $base_user_params =
    [
        'login' => 'first_user',
        'password' => '111222555',
        'email' => 'first@somemail.com',
        'first_name' => 'John',
        'last_name' => 'Malkovich',
        'roles' => ['ROLE_ADMIN', 'ROLE_USER'],
    ];

    createRoles($connection, $roles);
    createUser($connection, $base_user_params);

    $connection->commit();

}

catch (PDOException $e) {
    $connection->rollBack();

    echo $e->getMessage() . "\n";
    die();
}
