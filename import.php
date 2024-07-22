<?php
//не размещать на сайте
ini_set('max_execution_time', 0); // Установка неограниченного времени выполнения скрипта
ini_set('memory_limit', '250M'); // Увеличение лимита памяти, если это необходимо

$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "dbname";
$batchSize = 1000; // Размер пакета для обработки

// Создаем подключение к базе данных
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверяем соединение
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Устанавливаем кодировку соединения
$conn->set_charset("utf8");

// Создаем таблицу BAPMIC_DATA
$sql_create_table = "CREATE TABLE IF NOT EXISTS BAPMIC_DATA (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    article_from_supplier VARCHAR(50),
    Manufacturer VARCHAR(50),
    Model VARCHAR(50),
    Explanation VARCHAR(255),
    Year_of_release INT(4),
    Year_end_of_issue INT(4)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

if ($conn->query($sql_create_table) === TRUE) {
    echo "Table BAPMIC_DATA created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Путь к CSV файлу
$csv_file = 'BAPMIC_DATA.csv';

// Проверяем, существует ли файл
if (!file_exists($csv_file)) {
    die("File not found. Make sure you specified the correct path.");
}

// Открываем файл для чтения
if (($handle = fopen($csv_file, "r")) !== FALSE) {
    // Пропускаем заголовок CSV файла
    fgetcsv($handle, 1000, ",");

    // Подготовка SQL запроса для вставки данных
    $sql_insert = "INSERT INTO BAPMIC_DATA (article_from_supplier, Manufacturer, Model, Explanation, Year_of_release, Year_end_of_issue) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);

    $rowCount = 0;
    $batchCount = 0;

    // Чтение данных из CSV файла и вставка в таблицу
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $stmt->bind_param("ssssii", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);
        $stmt->execute();
        $rowCount++;

        // Пакетная обработка
        if ($rowCount % $batchSize == 0) {
            $batchCount++;
            echo "Processed $batchCount batches of $batchSize rows each.<br>";
            ob_flush();
            flush();
        }
    }

    fclose($handle);
    echo "Data imported successfully. Total rows processed: $rowCount<br>";
} else {
    echo "Error opening the file.<br>";
}

// Закрываем соединение
$conn->close();
?>
