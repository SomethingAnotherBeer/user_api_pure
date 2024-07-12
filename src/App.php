<?php
declare(strict_types=1);

namespace App;
use FastRoute;
use App\Handler\RequestHandler;
use App\Handler\ResponseHandler;
use App\Handler\ParamHandler;
use App\Http\Request;
use App\Http\Response;

use App\Helper\HttpHelper;

use App\DB\Connection;

use App\Controller\UserController;
use App\Controller\AuthController;
use App\Exception\Auth\AccessDeniedException;
use App\Exception\Auth\AuthException;
use App\Exception\Auth\TokenExpiredException;
use App\Exception\Auth\UnauthorizedException;
use App\Exception\Auth\WrongAuthDataException;
use App\Exception\Http\HttpException;
use App\Exception\Http\MethodNotAllowedException;
use App\Exception\Http\RouteNotFoundException;
use App\Service\AuthService;

use App\Mapper\UserRoleMapper;

class App
{
    public function start(array $settings): void
    {
        $this->setUp($settings);

        $request = RequestHandler::getInstance()->makeRequest();



        $dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {

            $r->addRoute("GET", '/api/users/{id:\d+}/info', ['controller' => UserController::getInstance(), 'method' => 'getUserInfo', 'roles' => ['ROLE_USER']]);
            $r->addRoute('POST', '/api/users/create', ['controller' => UserController::getInstance(), 'method' => 'createUser', 'roles' => ['ROLE_ADMIN']]);
            $r->addRoute('PATCH', '/api/users/{id:\d+}/change', ['controller' => UserController::getInstance(), 'method' => 'changeUser', 'roles' => ['ROLE_ADMIN']]);
            $r->addRoute('DELETE', '/api/users/{id:\d+}/delete', ['controller' => UserController::getInstance(), 'method' => 'deleteUser', 'roles' => ['ROLE_ADMIN']]);
        
            $r->addRoute('POST', '/api/auth/login', ['controller' => AuthController::getInstance(), 'method' => 'login']);
            $r->addRoute('GET', '/api/auth/check', ['controller' => AuthController::getInstance(), 'method' => 'checkAuth']);


        });
        
        // Fetch method and URI from somewhere
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        
        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        
        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        try {
            switch ($routeInfo[0]) {
                case FastRoute\Dispatcher::NOT_FOUND:
                   throw new RouteNotFoundException("Маршрут не найден");
                    break;
                case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                    $allowedMethods = $routeInfo[1];
                    throw new MethodNotAllowedException("Данный метод не поддерживается на данном маршруте");
                    break;
                case FastRoute\Dispatcher::FOUND:
                    $handler = $routeInfo[1];
                    $vars = $routeInfo[2];
                    $prepared_params = ParamHandler::getInstance()->prepareParams($vars);
                    // ... call $handler with $vars

                    if (!method_exists($handler['controller'], $handler['method'])) {
                        throw new \Exception("Метод не найден");
                    }

                    if (array_key_exists('roles', $handler)) {
                        $this->checkPermissions($request, $handler['roles']);
                    }
                    

                    $controller = $handler['controller'];
                    $method = $handler['method'];

                    $response = (count($vars) > 0) ? $controller->$method($request, ...array_values($prepared_params)) : $controller->$method($request);
                    
                    if (!is_object($response) || !($response instanceof Response)) {
                        throw new \Exception("Ответ контроллера должен быть инстансом класса Response");
                    }

                    ResponseHandler::getInstance()->handleResponse($response);


                    break;
            }
        }
        catch (\Exception $e) {

            $http_code = 400;

            if ($e instanceof AuthException) {
                $http_code = $this->getHttpCodeForAuthException($e);
            }

            if ($e instanceof HttpException) {
                $http_code = $this->getHttpCodeForHttpException($e);
            }

            $message = json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            $response = new Response($message, $http_code);

            ResponseHandler::getInstance()->handleResponse($response);
        }
        catch (\Error $e) {
            $message = json_encode(['system_error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            $response = new Response($message, 500);
            ResponseHandler::getInstance()->handleResponse($response);
        }

    }


    private function setUp(array $settings): void
    {
        Connection::setUp($settings['db_settings']);
    }

    private function checkPermissions(Request $request, array $required_roles)
    {
        $authorization_header = $request->getHeader()->HTTP_AUTHORIZATION;

        if (!$authorization_header) {
            throw new \Exception("Не указан токен аутентификации");
        }

        HttpHelper::checkBearer($authorization_header);
        
        $token = HttpHelper::getFromBearer($authorization_header);

        $token_params = AuthService::getInstance()->checkAuth($token);
        $user_id = $token_params['user_id'];

        $userRoleMapper = UserRoleMapper::getInstance();

        $user_roles = $userRoleMapper->getUserRolesByUserId($user_id);
    
        foreach ($required_roles as $required_role) {
            if (!in_array($required_role, $user_roles)) {
                throw new AccessDeniedException("Доступ запрещен");
            }
        }

    }

    private function getHttpCodeForAuthException(AuthException $e): int
    {
        $http_code = null;

        if ($e instanceof AccessDeniedException) {
            $http_code = 403;
        }

        if ($e instanceof UnauthorizedException) {
            $http_code = 401;
        }

        if ($e instanceof TokenExpiredException) {
            $http_code = 419;
        }

        if ($e instanceof WrongAuthDataException) {
            $http_code = 400;
        }

        return $http_code;
    }


    private function getHttpCodeForHttpException(HttpException $e): int
    {
        $http_code = null;

        if ($e instanceof MethodNotAllowedException) {
            $http_code = 405;
        }

        if ($e instanceof RouteNotFoundException) {
            $http_code = 404;
        }

        return $http_code;
    }

}