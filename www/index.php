<?php
// Правильная автозагрузка классов
spl_autoload_register(function ($class) {
    // Project-specific namespace prefix
    $prefix = 'App\\';
    
    // Base directory for the namespace prefix
    $base_dir = __DIR__ . '/';
    
    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Get the relative class name
    $relative_class = substr($class, $len);
    
    // Replace namespace separators with directory separators
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // Если файл существует, загружаем его
    if (file_exists($file)) {
        require $file;
    }
});

echo "<!DOCTYPE html>";
echo "<html><head><title>Lab 6 - NoSQL DB</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 4px; }
    .success { color: #28a745; }
    .error { color: #dc3545; }
    .info { background: #d1ecf1; padding: 10px; border-radius: 4px; border-left: 4px solid #17a2b8; }
</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<h1>🚀 Лабораторная работа 6</h1>";

// Проверка системы
echo "<div class='info'>";
echo "<h3>🔍 Проверка системы и автозагрузки</h3>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Server Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Проверка классов и файлов
$classes = [
    'App\Helpers\ClientFactory' => 'Helpers/ClientFactory.php',
    'App\RedisExample' => 'RedisExample.php',
    'App\ElasticExample' => 'ElasticExample.php',
    'App\ClickhouseExample' => 'ClickhouseExample.php'
];

foreach ($classes as $class => $file) {
    $fullPath = __DIR__ . '/' . $file;
    $fileExists = file_exists($fullPath);
    
    echo "<p><strong>Класс:</strong> $class</p>";
    echo "<p><strong>Файл:</strong> $file</p>";
    echo "<p><strong>Полный путь:</strong> $fullPath</p>";
    echo "<p><strong>Файл существует:</strong> " . ($fileExists ? '✅ Да' : '❌ Нет') . "</p>";
    
    if ($fileExists) {
        // Пробуем загрузить файл вручную
        require_once $fullPath;
        
        if (class_exists($class)) {
            echo "<p class='success'>✅ Класс загружен успешно!</p>";
        } else {
            echo "<p class='error'>❌ Класс не загружен после require</p>";
            
            // Покажем содержимое файла для отладки
            $content = file_get_contents($fullPath);
            if (strpos($content, 'namespace') === false) {
                echo "<p class='error'>⚠️ В файле нет namespace!</p>";
            }
        }
    }
    echo "<hr>";
}
echo "</div>";

// Redis тест
echo "<div class='section'>";
echo "<h2>🔴 Redis Example</h2>";
try {
    // Загружаем ClientFactory вручную на всякий случай
    if (!class_exists('App\Helpers\ClientFactory') && file_exists(__DIR__ . '/Helpers/ClientFactory.php')) {
        require_once __DIR__ . '/Helpers/ClientFactory.php';
    }
    
    $redis = new App\RedisExample();
    echo "<p class='success'>✅ RedisExample инициализирован</p>";
    echo "<p><strong>SET:</strong> " . $redis->setValue('user:101', 'Alice') . "</p>";
    echo "<p><strong>GET:</strong> " . $redis->getValue('user:101') . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Ошибка Redis: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
echo "</div>";

// Elasticsearch тест
echo "<div class='section'>";
echo "<h2>🔍 Elasticsearch Example</h2>";
try {
    $elastic = new App\ElasticExample();
    echo "<p class='success'>✅ ElasticExample инициализирован</p>";
    echo "<p><strong>Create Index:</strong> " . $elastic->createIndex('books') . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Ошибка Elasticsearch: " . $e->getMessage() . "</p>";
}
echo "</div>";

// ClickHouse тест
echo "<div class='section'>";
echo "<h2>⚡️ ClickHouse Example</h2>";
try {
    $clickhouse = new App\ClickhouseExample();
    echo "<p class='success'>✅ ClickhouseExample инициализирован</p>";
    echo "<p><strong>Query:</strong> " . $clickhouse->query('SELECT count() FROM system.tables') . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Ошибка ClickHouse: " . $e->getMessage() . "</p>";
}
echo "</div>";

echo "</div>";
echo "</body></html>";