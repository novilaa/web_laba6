<?php
require_once 'vendor/autoload.php';

use App\ClickhouseExample;
use App\Models\WeatherData;

echo "<!DOCTYPE html>";
echo "<html><head><title>Погодная станция - ClickHouse</title>";
echo "<style>
    body { 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        margin: 0; 
        padding: 20px; 
        background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
        min-height: 100vh;
    }
    .container { 
        max-width: 1200px; 
        margin: 0 auto; 
        background: white; 
        padding: 30px; 
        border-radius: 15px; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .header { 
        text-align: center; 
        margin-bottom: 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
    }
    .section { 
        margin: 25px 0; 
        padding: 25px; 
        border: 1px solid #e1e8ed; 
        border-radius: 10px;
        background: #f8f9fa;
    }
    .success { 
        color: #27ae60; 
        font-weight: bold;
        padding: 10px;
        background: #d5f4e6;
        border-radius: 5px;
    }
    .error { 
        color: #e74c3c; 
        font-weight: bold;
        padding: 10px;
        background: #fadbd8;
        border-radius: 5px;
    }
    .weather-card {
        background: white;
        border-radius: 10px;
        padding: 15px;
        margin: 10px 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 4px solid #3498db;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin: 15px 0;
    }
    .stat-card {
        background: white;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .form-group {
        margin: 15px 0;
    }
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #2c3e50;
    }
    input, select {
        width: 100%;
        padding: 10px;
        border: 1px solid #bdc3c7;
        border-radius: 5px;
        font-size: 14px;
    }
    button {
        background: #3498db;
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        margin: 5px;
    }
    button:hover {
        background: #2980b9;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
    }
    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ecf0f1;
    }
    th {
        background: #34495e;
        color: white;
    }
    tr:hover {
        background: #f8f9fa;
    }
</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>🌡️ Система мониторинга погодных данных</h1>";
echo "<p>Хранение и анализ температурных данных в ClickHouse</p>";
echo "</div>";

try {
    $clickhouse = new ClickhouseExample();
    
    // Инициализация базы данных
    echo "<div class='section'>";
    echo "<h2>🗃️ Инициализация базы данных</h2>";
    $initResult = $clickhouse->initWeatherDatabase();
    echo "<p class='success'>✅ База данных и таблицы инициализированы</p>";
    echo "</div>";

    // Генерация тестовых данных
    echo "<div class='section'>";
    echo "<h2>📊 Загрузка тестовых данных</h2>";
    
    $testData = [
        new WeatherData('Москва', '2024-01-15', -5.5, 85, 1013, 12.5, 'Снег'),
        new WeatherData('Москва', '2024-01-16', -7.1, 82, 1015, 5.2, 'Ясно'),
        new WeatherData('Москва', '2024-01-17', -3.8, 78, 1012, 8.7, 'Облачно'),
        new WeatherData('Санкт-Петербург', '2024-01-15', -8.2, 87, 1010, 15.3, 'Снегопад'),
        new WeatherData('Санкт-Петербург', '2024-01-16', -6.5, 84, 1011, 12.1, 'Пасмурно'),
        new WeatherData('Сочи', '2024-01-15', 8.5, 65, 1015, 3.2, 'Солнечно'),
        new WeatherData('Сочи', '2024-01-16', 9.2, 62, 1014, 2.8, 'Солнечно'),
        new WeatherData('Новосибирск', '2024-01-15', -15.3, 79, 1008, 18.7, 'Метель'),
        new WeatherData('Новосибирск', '2024-01-16', -18.1, 81, 1009, 22.3, 'Метель'),
        new WeatherData('Екатеринбург', '2024-01-15', -12.7, 76, 1011, 14.2, 'Снег')
    ];

    $saveResult = $clickhouse->saveMultipleWeatherData($testData);
    echo "<p class='success'>✅ Загружено " . count($testData) . " записей о погоде</p>";
    echo "</div>";

    // Статистика по городам
    echo "<div class='section'>";
    echo "<h2>📈 Статистика по городам</h2>";
    $stats = $clickhouse->getWeatherStats();
    
    if (isset($stats['data'])) {
        echo "<div class='stats-grid'>";
        foreach ($stats['data'] as $cityStats) {
            echo "<div class='stat-card'>";
            echo "<h3>" . htmlspecialchars($cityStats['city']) . "</h3>";
            echo "<p>🌡️ Средняя: " . $cityStats['avg_temperature'] . "°C</p>";
            echo "<p>🔥 Макс: " . $cityStats['max_temperature'] . "°C</p>";
            echo "<p>❄️ Мин: " . $cityStats['min_temperature'] . "°C</p>";
            echo "<p>📊 Записей: " . $cityStats['records_count'] . "</p>";
            echo "</div>";
        }
        echo "</div>";
    }
    echo "</div>";

    // Экстремальные температуры
    echo "<div class='section'>";
    echo "<h2>🔥 Самые высокие и низкие температуры</h2>";
    $extremes = $clickhouse->getTemperatureExtremes(3);
    
    echo "<h3>Самые теплые дни:</h3>";
    if (isset($extremes['warmest']['data'])) {
        foreach ($extremes['warmest']['data'] as $record) {
            echo "<div class='weather-card'>";
            echo "<strong>" . htmlspecialchars($record['city']) . "</strong> - " . $record['date'] . ": " . $record['temperature'] . "°C (" . ($record['conditions'] ?? 'N/A') . ")";
            echo "</div>";
        }
    }
    
    echo "<h3>Самые холодные дни:</h3>";
    if (isset($extremes['coldest']['data'])) {
        foreach ($extremes['coldest']['data'] as $record) {
            echo "<div class='weather-card'>";
            echo "<strong>" . htmlspecialchars($record['city']) . "</strong> - " . $record['date'] . ": " . $record['temperature'] . "°C (" . ($record['conditions'] ?? 'N/A') . ")";
            echo "</div>";
        }
    }
    echo "</div>";

    // Данные по Москве
    echo "<div class='section'>";
    echo "<h2>🏙️ Погода в Москве</h2>";
    $moscowData = $clickhouse->getCityWeather('Москва', 5);
    
    if (isset($moscowData['data'])) {
        echo "<table>";
        echo "<tr><th>Дата</th><th>Температура</th><th>Влажность</th><th>Давление</th><th>Ветер</th><th>Условия</th></tr>";
        foreach ($moscowData['data'] as $record) {
            echo "<tr>";
            echo "<td>" . $record['date'] . "</td>";
            echo "<td>" . $record['temperature'] . "°C</td>";
            echo "<td>" . ($record['humidity'] ?? 'N/A') . "%</td>";
            echo "<td>" . ($record['pressure'] ?? 'N/A') . " гПа</td>";
            echo "<td>" . ($record['wind_speed'] ?? 'N/A') . " м/с</td>";
            echo "<td>" . ($record['conditions'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";

    // Форма для добавления новых данных
    echo "<div class='section'>";
    echo "<h2>➕ Добавить новые данные о погоде</h2>";
    echo "<form method='POST'>";
    echo "<div class='form-group'>";
    echo "<label for='city'>Город:</label>";
    echo "<input type='text' id='city' name='city' value='Москва' required>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label for='date'>Дата:</label>";
    echo "<input type='date' id='date' name='date' value='" . date('Y-m-d') . "' required>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label for='temperature'>Температура (°C):</label>";
    echo "<input type='number' step='0.1' id='temperature' name='temperature' required>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label for='humidity'>Влажность (%):</label>";
    echo "<input type='number' id='humidity' name='humidity'>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label for='pressure'>Давление (гПа):</label>";
    echo "<input type='number' id='pressure' name='pressure'>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label for='wind_speed'>Скорость ветра (м/с):</label>";
    echo "<input type='number' step='0.1' id='wind_speed' name='wind_speed'>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label for='conditions'>Погодные условия:</label>";
    echo "<select id='conditions' name='conditions'>";
    echo "<option value=''>Выберите...</option>";
    echo "<option value='Солнечно'>Солнечно</option>";
    echo "<option value='Облачно'>Облачно</option>";
    echo "<option value='Пасмурно'>Пасмурно</option>";
    echo "<option value='Дождь'>Дождь</option>";
    echo "<option value='Снег'>Снег</option>";
    echo "<option value='Туман'>Туман</option>";
    echo "</select>";
    echo "</div>";
    
    echo "<button type='submit' name='add_weather'>Добавить данные</button>";
    echo "</form>";
    echo "</div>";

    // Обработка формы
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_weather'])) {
        $newWeather = new WeatherData(
            $_POST['city'],
            $_POST['date'],
            (float)$_POST['temperature'],
            $_POST['humidity'] ? (float)$_POST['humidity'] : null,
            $_POST['pressure'] ? (int)$_POST['pressure'] : null,
            $_POST['wind_speed'] ? (float)$_POST['wind_speed'] : null,
            $_POST['conditions'] ?: null
        );
        
        $result = $clickhouse->saveWeatherData($newWeather);
        echo "<p class='success'>✅ Новые данные о погоде добавлены успешно!</p>";
    }

} catch (Exception $e) {
    echo "<div class='section'>";
    echo "<p class='error'>❌ Ошибка: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</div>";
echo "</body></html>";