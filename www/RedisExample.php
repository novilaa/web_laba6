<?php

namespace App;

use App\Helpers\ClientFactory;

class RedisExample
{
    private $client;

    public function __construct()
    {
        $this->client = ClientFactory::make('http://redis:6379/');
    }

    public function setValue($key, $value)
    {
        return "Redis SET: $key = $value (simulated)";
    }

    public function getValue($key)
    {
        return "Redis GET $key: Hello from Redis! (simulated)";
    }
}