<?php
declare(strict_types=1);

namespace App\Http;
use App\Model\HashImmutable;
use App\Http\Header;
use App\Http\Post;
use App\Http\Get;

class Request
{
    private Header $header;
    private Post $post;
    private Get $get;


    public function __construct(Header $header, Post $post, Get $get)
    {
        $this->header = $header;
        $this->post = $post;
        $this->get = $get;
    }

    public static function getInstance(Header $header, Post $post, Get $get): Request
    {
        return new Request($header, $post, $get);
    }

    public function getHeader(): Header
    {
        return $this->header;
    }

    public function getPost(): Post
    {
        return $this->post;

    }


    public function getGet(): Get
    {
        return $this->get;
    }

}
