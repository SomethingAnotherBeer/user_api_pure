<?php
declare(strict_types=1);
namespace App\Http;
use App\Model\HashImmutable;

class Header
{
    private HashImmutable $headerCollection;

    public function __construct(HashImmutable $headerCollection)
    {
        $this->headerCollection = $headerCollection;
    }

    public static function getInstance(HashImmutable $headerCollection): Header
    {
        return new Header($headerCollection);
    }

    public function __get($name): mixed
    {
        return $this->headerCollection->$name;
    }

    public function all(): array
    {
        return $this->headerCollection->all();
    }

}