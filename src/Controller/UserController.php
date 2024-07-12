<?php
declare(strict_types=1);
namespace App\Controller;
use App\Http\Request;
use App\Http\Response;
use App\Service\UserService;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct()
    {
        $this->userService = UserService::getInstance();
    }
    
    public function getUserInfo(Request $request, int $user_id): Response
    {
        $response = $this->userService->getUserInfo($user_id);
        $response = new Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);

        return $response;
    }

    public function createUser(Request $request): Response
    {
        $request_params = $request->getPost()->all();

        $response = $this->userService->createUser($request_params);


        $response = new Response(json_encode($response, JSON_UNESCAPED_UNICODE), 201);  

        return $response;
    }



    public function changeUser(Request $request, int $user_id): Response
    {
        $request_params = $request->getPost()->all();
        $response = $this->userService->changeUser($user_id, $request_params);

        $response = new Response(json_encode($response, JSON_UNESCAPED_UNICODE), 201);

        return $response;
    }


    public function deleteUser(Request $request, int $user_id): Response
    {
        $response = $this->userService->deleteUser($user_id);
        $response = new Response(json_encode($response, JSON_UNESCAPED_UNICODE), 201);

        return $response;
    
    }

}