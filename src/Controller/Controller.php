<?php
declare(strict_types=1);
namespace App\Controller;

abstract class Controller
{
    public static function getInstance(): static
    {
        return new static();
    }
}