<?php

namespace App\Helpers;

class ClientFactory
{
    public static function make(string $baseUri)
    {
        // Простая заглушка для тестирования
        return new class($baseUri) {
            private $baseUri;
            
            public function __construct($baseUri) {
                $this->baseUri = $baseUri;
            }
            
            public function get($url) {
                return new class {
                    public function getBody() {
                        return $this;
                    }
                    public function getContents() {
                        return "Simulated HTTP GET response";
                    }
                };
            }
            
            public function post($url, $options = []) {
                return new class {
                    public function getBody() {
                        return $this;
                    }
                    public function getContents() {
                        return "Simulated HTTP POST response";
                    }
                };
            }
            
            public function put($url, $options = []) {
                return new class {
                    public function getBody() {
                        return $this;
                    }
                    public function getContents() {
                        return "Simulated HTTP PUT response";
                    }
                };
            }
        };
    }
}