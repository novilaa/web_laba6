<?php

namespace App;

use App\Helpers\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;

class ClickhouseExample
{
    private $client;

    public function __construct()
    {
        $this->client = ClientFactory::make('http://clickhouse:8123/');
    }

    public function query($sql)
    {
        try {
            $response = $this->client->post('', [
                'body' => $sql . ' FORMAT JSON',
                'headers' => ['X-ClickHouse-Format' => 'JSON']
            ]);
            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function initWeatherTable()
    {
        $queries = [
            "CREATE DATABASE IF NOT EXISTS weather",
            "CREATE TABLE IF NOT EXISTS weather.temperature_data (
                city String,
                date Date,
                temperature Float32,
                humidity Float32,
                pressure Float32
            ) ENGINE = MergeTree()
            ORDER BY (city, date)"
        ];

        foreach ($queries as $query) {
            $this->query($query);
        }
    }

    public function insertWeatherData($data)
    {
        $values = [];
        foreach ($data as $record) {
            $city = $record['city'] ?? 'Unknown';
            $date = $record['date'] ?? date('Y-m-d');
            $temp = $record['temperature'] ?? 0;
            $humidity = $record['humidity'] ?? 0;
            $pressure = $record['pressure'] ?? 0;

            $values[] = "('$city', '$date', $temp, $humidity, $pressure)";
        }

        $sql = "INSERT INTO weather.temperature_data VALUES " . implode(', ', $values);
        return $this->query($sql);
    }

    public function getWeatherStats()
    {
        $sql = "SELECT 
                city,
                avg(temperature) as avg_temp,
                max(temperature) as max_temp,
                min(temperature) as min_temp,
                count() as measurements
            FROM weather.temperature_data 
            GROUP BY city
            ORDER BY avg_temp DESC";

        return $this->query($sql);
    }
}