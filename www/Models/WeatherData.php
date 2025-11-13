<?php

namespace App\Models;

class WeatherData
{
    private $city;
    private $date;
    private $temperature;
    private $humidity;
    private $pressure;
    private $windSpeed;
    private $conditions;

    public function __construct($city, $date, $temperature, $humidity = null, $pressure = null, $windSpeed = null, $conditions = null)
    {
        $this->city = $city;
        $this->date = $date;
        $this->temperature = $temperature;
        $this->humidity = $humidity;
        $this->pressure = $pressure;
        $this->windSpeed = $windSpeed;
        $this->conditions = $conditions;
    }

    // Getters
    public function getCity() { return $this->city; }
    public function getDate() { return $this->date; }
    public function getTemperature() { return $this->temperature; }
    public function getHumidity() { return $this->humidity; }
    public function getPressure() { return $this->pressure; }
    public function getWindSpeed() { return $this->windSpeed; }
    public function getConditions() { return $this->conditions; }

    public function toArray()
    {
        return [
            'city' => $this->city,
            'date' => $this->date,
            'temperature' => $this->temperature,
            'humidity' => $this->humidity,
            'pressure' => $this->pressure,
            'wind_speed' => $this->windSpeed,
            'conditions' => $this->conditions
        ];
    }
}