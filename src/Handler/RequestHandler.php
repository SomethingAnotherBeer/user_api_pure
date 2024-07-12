<?php
declare(strict_types=1);

namespace App\Handler;
use App\Http\Request;
use App\Http\Post;
use App\Http\Header;
use App\Http\Get;
use App\Model\HashImmutable;

class RequestHandler
{
    public static function getInstance(): RequestHandler
    {
        return new RequestHandler();
    }

    public function makeRequest(): Request
    {
        $post_params = $this->getPostParams();
        $query_params = $this->getQueryParams();
        $headers_params = $this->getHeaderParams();

        $header = Header::getInstance(HashImmutable::getInstance($headers_params));
        $post = Post::getInstance(HashImmutable::getInstance($post_params));
        $get = Get::getInstance(HashImmutable::getInstance($query_params));


        $request = Request::getInstance($header, $post, $get);


        return $request;
    }



    private function getPostParams(): array
    {
        $post_params = file_get_contents("php://input");
        $prepared_params = [];

        if ($post_params) {
            $post_params = json_decode($post_params, true);
            
            $prepared_params = $this->prepareParams($post_params);
        }

        return $prepared_params;
    }

    private function getQueryParams(): array
    {
        $query_params = $_GET;
        $prepared_params = [];

        if ($query_params) {
            $prepared_params = $this->prepareParams($query_params);
        }

        return $prepared_params;

    }

    private function getHeaderParams(): array
    {
        $acceptable_headers = ['HTTP_AUTHORIZATION', 'CONTENT_TYPE'];
        $headers = [];

        foreach ($acceptable_headers as $acceptable_header) {
            $headers[$acceptable_header] = $_SERVER[$acceptable_header] ?? null;
        }
        

        return $headers;

    }


    private function prepareParams(array $params): array
    {
        $prepared_params = [];

        foreach ($params as $param_key => $param_value) {
            $prepared_params[$param_key] = (is_array($param_value)) ? $this->prepareParams($param_value) : trim(htmlspecialchars($param_value));
        }

        return $prepared_params;
    }


}