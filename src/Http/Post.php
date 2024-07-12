<?php
declare(strict_types=1);
namespace App\Http;
use App\Model\HashImmutable;

class Post
{
    private HashImmutable $postCollection;

    public function __construct(HashImmutable $postCollection)
    {
        $this->postCollection = $postCollection;
    }

    public static function getInstance(HashImmutable $postCollection): Post
    {
        return new Post($postCollection);
    }


    public function __get($name): mixed
    {
        return $this->postCollection->$name;
    }

    public function all(): array
    {
        return $this->postCollection->all();
    }

}