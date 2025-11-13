<?php

namespace App;

use App\Helpers\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;

class ElasticExample
{
    private $client;

    public function __construct()
    {
        $this->client = ClientFactory::make('http://elasticsearch:9200/');
    }

    public function createIndex($index)
    {
        try {
            $response = $this->client->put($index);
            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            // Индекс уже существует - это нормально
            return $e->getMessage();
        }
    }

    public function indexDocument($index, $id, $data)
    {
        try {
            $response = $this->client->put("$index/_doc/$id", [
                'json' => $data,
                'headers' => ['Content-Type' => 'application/json']
            ]);
            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function search($index, $query)
    {
        try {
            $response = $this->client->get("$index/_search", [
                'json' => ['query' => $query]
            ]);
            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function bulkIndex($index, $documents)
    {
        try {
            $body = '';
            foreach ($documents as $id => $doc) {
                $body .= json_encode(['index' => ['_index' => $index, '_id' => $id]]) . "\n";
                $body .= json_encode($doc) . "\n";
            }

            $response = $this->client->post('_bulk', [
                'body' => $body,
                'headers' => ['Content-Type' => 'application/json']
            ]);
            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            return "Error: " . $e->getMessage();
        }
    }
}