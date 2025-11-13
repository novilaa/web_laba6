<?php

namespace App;

use App\Helpers\ClientFactory;
use App\Models\WeatherData;
use GuzzleHttp\Exception\GuzzleException;

class ClickhouseExample
{
    private $client;

    public function __construct()
    {
        $this->client = ClientFactory::make('http://clickhouse:8123/');
    }

    /**
     * Выполняет SQL запрос к ClickHouse
     */
    public function query($sql)
    {
        try {
            $response = $this->client->post('', [
                'body' => $sql . ' FORMAT JSON',
                'headers' => [
                    'X-ClickHouse-Format' => 'JSON'
                ]
            ]);
            
            $result = $response->getBody()->getContents();
            return json_decode($result, true) ?? $result;
            
        } catch (GuzzleException $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Инициализирует базу данных и таблицы для погодных данных
     */
    public function initWeatherDatabase()
    {
        $queries = [
            // Создаем базу данных если не существует
            "CREATE DATABASE IF NOT EXISTS weather",
            
            // Создаем таблицу для ежедневных температурных данных
            "CREATE TABLE IF NOT EXISTS weather.daily_weather (
                city String,
                date Date,
                temperature Float32,
                humidity Float32,
                pressure Float32,
                wind_speed Float32,
                conditions String,
                created_at DateTime DEFAULT now()
            ) ENGINE = MergeTree()
            PARTITION BY toYYYYMM(date)
            ORDER BY (city, date)",
            
            // Создаем таблицу для почасовых данных (если нужно)
            "CREATE TABLE IF NOT EXISTS weather.hourly_weather (
                city String,
                datetime DateTime,
                temperature Float32,
                humidity Float32,
                pressure Float32,
                wind_speed Float32,
                conditions String
            ) ENGINE = MergeTree()
            PARTITION BY toYYYYMM(datetime)
            ORDER BY (city, datetime)"
        ];

        $results = [];
        foreach ($queries as $query) {
            $results[] = $this->query($query);
        }

        return $results;
    }

    /**
     * Сохраняет температурные данные
     */
    public function saveWeatherData(WeatherData $weatherData)
    {
        $data = $weatherData->toArray();
        
        $sql = "INSERT INTO weather.daily_weather (city, date, temperature, humidity, pressure, wind_speed, conditions) VALUES (
            '{$data['city']}',
            '{$data['date']}',
            {$data['temperature']},
            " . ($data['humidity'] ?? 'NULL') . ",
            " . ($data['pressure'] ?? 'NULL') . ",
            " . ($data['wind_speed'] ?? 'NULL') . ",
            " . ($data['conditions'] ? "'{$data['conditions']}'" : 'NULL') . "
        )";

        return $this->query($sql);
    }

    /**
     * Сохраняет несколько записей о погоде
     */
    public function saveMultipleWeatherData(array $weatherDataArray)
    {
        if (empty($weatherDataArray)) {
            return ['error' => 'No data provided'];
        }

        $values = [];
        foreach ($weatherDataArray as $weatherData) {
            $data = $weatherData->toArray();
            $values[] = "(
                '{$data['city']}',
                '{$data['date']}',
                {$data['temperature']},
                " . ($data['humidity'] ?? 'NULL') . ",
                " . ($data['pressure'] ?? 'NULL') . ",
                " . ($data['wind_speed'] ?? 'NULL') . ",
                " . ($data['conditions'] ? "'{$data['conditions']}'" : 'NULL') . "
            )";
        }

        $sql = "INSERT INTO weather.daily_weather (city, date, temperature, humidity, pressure, wind_speed, conditions) VALUES " . implode(', ', $values);
        
        return $this->query($sql);
    }

    /**
     * Получает статистику по городам
     */
    public function getWeatherStats()
    {
        $sql = "SELECT 
            city,
            COUNT() as records_count,
            ROUND(AVG(temperature), 2) as avg_temperature,
            ROUND(MAX(temperature), 2) as max_temperature,
            ROUND(MIN(temperature), 2) as min_temperature,
            ROUND(AVG(humidity), 2) as avg_humidity,
            ROUND(AVG(pressure), 2) as avg_pressure
        FROM weather.daily_weather 
        GROUP BY city
        ORDER BY avg_temperature DESC";

        return $this->query($sql);
    }

    /**
     * Получает данные по конкретному городу
     */
    public function getCityWeather($city, $limit = 10)
    {
        $sql = "SELECT 
            city,
            date,
            temperature,
            humidity,
            pressure,
            wind_speed,
            conditions
        FROM weather.daily_weather 
        WHERE city = '$city'
        ORDER BY date DESC
        LIMIT $limit";

        return $this->query($sql);
    }

    /**
     * Получает данные за период
     */
    public function getWeatherByDateRange($startDate, $endDate)
    {
        $sql = "SELECT 
            city,
            date,
            temperature,
            humidity,
            conditions
        FROM weather.daily_weather 
        WHERE date BETWEEN '$startDate' AND '$endDate'
        ORDER BY city, date";

        return $this->query($sql);
    }

    /**
     * Получает самые теплые/холодные дни
     */
    public function getTemperatureExtremes($limit = 5)
    {
        $warmest = $this->query("
            SELECT city, date, temperature, conditions 
            FROM weather.daily_weather 
            ORDER BY temperature DESC 
            LIMIT $limit
        ");

        $coldest = $this->query("
            SELECT city, date, temperature, conditions 
            FROM weather.daily_weather 
            ORDER BY temperature ASC 
            LIMIT $limit
        ");

        return [
            'warmest' => $warmest,
            'coldest' => $coldest
        ];
    }

    /**
     * Удаляет данные по городу (для очистки)
     */
    public function deleteCityData($city)
    {
        $sql = "ALTER TABLE weather.daily_weather DELETE WHERE city = '$city'";
        return $this->query($sql);
    }
}