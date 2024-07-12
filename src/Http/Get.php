<?php
declare(strict_types=1);
namespace App\Http;
use App\Model\HashImmutable;

class Get
{
    private HashImmutable $getCollection;

    public function __construct(HashImmutable $getCollection)
    {   
        $this->getCollection = $getCollection;
    }


    public static function getInstance(HashImmutable $getCollection): Get
    {
        return new Get($getCollection);
    }

    public function __get($name): mixed
    {
        return $this->getCollection->$name;
    }


    public function all(): array
    {
        return $this->getCollection->all();
    }

}