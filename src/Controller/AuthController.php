<?php
declare(strict_types=1);
namespace App\Controller;
use App\Http\Request;
use App\Http\Response;
use App\Service\AuthService;
use App\Helper\HttpHelper;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = AuthService::getInstance();
    }

    public function login(Request $request): Response
    {
        $request_params = $request->getPost()->all();

        $response = $this->authService->login($request_params);
        $response = new Response(json_encode($response, JSON_UNESCAPED_UNICODE), 201);

        return $response;
    }


    public function checkAuth(Request $request): Response
    {

        $auth_header = $request->getHeader()->HTTP_AUTHORIZATION;
        HttpHelper::checkBearer($auth_header);

        $response = $this->authService->checkAuth(HttpHelper::getFromBearer($auth_header));
        $response = new Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);

        return $response;

        
    }

}