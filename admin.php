<?php
// admin.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'config.php';
require 'Database.php';

$config = require 'config.php';

$db = new Database($config);

function backupTables($db, $config, $tables) {
    $backup_dir = __DIR__ . "/backup/";

    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0777, true);
    }

    foreach ($tables as $table) {
        $backup_file = $backup_dir . "{$table}_" . date('Y-m-d_H-i-s') . ".sql";
        $command = "mysqldump -u {$config['username']} -p{$config['password']} {$config['dbname']} $table > $backup_file";
        system($command, $retval);

        if ($retval != 0) {
            echo "<p style='color: red; font-weight: bold;'>Ошибка резервного копирования таблицы $table.</p><br>";
        } else {
            echo "Таблица $table успешно сохранена как резерв в папку backup. Не забывайте удалять из папки backup устаревшие файлы резерва.<br>";
        }
    }
}

function importCSV($db, $table, $csv_file) {
    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        $columns = fgetcsv($handle, 1000, ",");
        $columns = implode(',', $columns);

        // Проверка на соответствие столбцов
        $result = $db->query("SHOW COLUMNS FROM $table");
        $db_columns = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['Field'] != 'id') { // Пропускаем столбец id
                $db_columns[] = $row['Field'];
            }
        }

        $csv_columns = explode(',', $columns);
        $missing_columns = array_diff($db_columns, $csv_columns);
        $extra_columns = array_diff($csv_columns, $db_columns);

        if (!empty($missing_columns)) {
            echo "<p style='color: red; font-weight: bold;'>Ошибка: отсутствуют следующие столбцы в CSV: " . implode(', ', $missing_columns) . "</p><br>";
            // Удаляем временный файл
            unlink($csv_file);
            echo "Файл из папки temp удален.<br>";
            return;
        }

        if (!empty($extra_columns)) {
            echo "<p style='color: red; font-weight: bold;'>Ошибка: следующие столбцы в CSV не соответствуют таблице: " . implode(', ', $extra_columns) . "</p><br>";
            // Удаляем временный файл
            unlink($csv_file);
            echo "Файл из папки temp удален.<br>";
            return;
        }

        // Очистка таблицы перед загрузкой новых данных
        $db->query("TRUNCATE TABLE $table");

        // Подготовка SQL запроса для вставки данных
        $column_list = implode(',', $csv_columns);
        $placeholder_list = implode(',', array_fill(0, count($csv_columns), '?'));
        $sql_insert = "INSERT INTO $table ($column_list) VALUES ($placeholder_list)";
        $stmt = $db->getConnection()->prepare($sql_insert);

        // Чтение данных из CSV файла и вставка в таблицу
        $rowCount = 0;
        $batchCount = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $stmt->bind_param(str_repeat('s', count($data)), ...$data);
            $stmt->execute();
            $rowCount++;

            // Пакетная обработка
            if ($rowCount % 1000 == 0) {
                $batchCount++;
                echo "Обработано $batchCount пакетов по 1000 строк каждый.</p><br>";
                ob_flush();
                flush();
            }
        }

        fclose($handle);
        echo "<p style='color: green; font-weight: bold;'>Успех! Данные успешно загружены. Всего обработано строк: $rowCount.</p><br>";

        // Удаляем временный файл
        unlink($csv_file);
        echo "Файл из папки temp удален.<br>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>Ошибка открытия файла.</p><br>";
    }
}

ob_start(); // Включаем буферизацию вывода

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['backup'])) {
    $tables = ['Bapmic_cross', 'BAPMIC_DATA', 'Catalog'];
    backupTables($db, $config, $tables);
    echo '<br><a href="admin.php">Вернуться назад</a>';
    ob_end_flush(); // Очищаем (и отключаем) буфер вывода
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    $table = $_POST['table'];
    $csv_file = $_FILES['csv_file']['tmp_name'];
    $temp_file = __DIR__ . '/temp/' . basename($_FILES['csv_file']['name']);

    // Копируем файл в временную директорию
    if (move_uploaded_file($csv_file, $temp_file)) {
        echo "Файл успешно загружен в папку temp.<br>";
        ob_flush();
        flush();

        // Создание резервной копии перед очисткой таблицы
        backupTables($db, $config, [$table]);

        // Импорт данных из CSV файла
        importCSV($db, $table, $temp_file);

        echo '<br><a href="admin.php">Вернуться назад</a>';
        ob_end_flush(); // Очищаем (и отключаем) буфер вывода
    } else {
        echo "<p style='color: red; font-weight: bold;'>Ошибка загрузки файла.</p><br>";
        ob_end_flush(); // Очищаем (и отключаем) буфер вывода
    }
} else {
    ob_end_flush(); // Очищаем (и отключаем) буфер вывода в случае, если не было POST-запроса
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора</title>
</head>
<body>
    <h1>Панель администратора</h1>

    <form method="post" enctype="multipart/form-data">
        <h2>Резервное копирование таблиц</h2>
        <button type="submit" name="backup">Сделать резервную копию</button>
    </form>

    <form method="post" enctype="multipart/form-data">
        <h2>Загрузка данных из CSV</h2>
        <select name="table" required>
            <option value="Bapmic_cross">Bapmic_cross</option>
            <option value="BAPMIC_DATA">BAPMIC_DATA</option>
            <option value="Catalog">Catalog</option>
        </select>
        <input type="file" name="csv_file" accept=".csv" required>
        <button type="submit">Загрузить данные</button>
    </form>

    <h3>Инструкции по загрузке CSV файлов:</h3>
    <p>ШАГ 0 создаем папку на компьютере(название текущая дата) и копируем туда excel файл Если в процессе работы, где то вдруг появиться вопрос кодировки выбираем UTF-8</p>
    <p>ШАГ 1 копируем ниже заголовки столбцов и заменяем в excel, копируем название таблицы, переименовыем файл в таблицу бд</p>
    <p>Для каждой таблицы должны быть следующие заголовки столбцов в CSV файле:</p>
    <ul>
        <li><b>Bapmic_cross:</b> brand_from_supplier, article_from_supplier, brand_from_buyer, article_from_buyer</li>
        <li><b>BAPMIC_DATA:</b> article_from_supplier, Manufacturer, Model, Explanation, Year_of_release, Year_end_of_issue</li>
        <li><b>Catalog:</b> article_from_supplier, Manufacturer, Name, note, OE, Net_weight_kg_netto, Gross_weight_kg_brutto, Length_cm, Width_cm, Height_cm</li>
    </ul>
    <p>ШАГ 2 сохраняем данные и загружаем на сайт конвертации</p>
    <p><a href="https://convertio.co/ru/xlsx-csv/">Пример программы меняющий xlsx на csv без ошибок</a></p>
    <p>ШАГ 3 скачиваем на свой компьютер таблицу в формате CSV и открываем в блокноте или sublime text(бесплатная программа из интернета)</p>
    <p>Пример строки через просмотр в программе блокнот в CSV файле для таблицы Catalog:</p>
    <pre>
    "article_from_supplier","Manufacturer","Name","note","OE","Net_weight_kg_netto","Gross_weight_kg_brutto","Length_cm","Width_cm","Height_cm"
    "BF0140060001","VAG","4-х контактный штекер","описание","1J0973704","0.012","0","3.2","3.7","2"
    </pre>
    <p>ШАГ 4 выше в выпадающем списке(Загрузка данных из CSV) выбираем нужную таблицу в бд и загружаем подготовленный файл </p>
    <p>ШАГ 5 если название таблицы и подготовленного файла совпадают нажимаем кнопку загрузить данные, ждем </p>
</body>
</html>
