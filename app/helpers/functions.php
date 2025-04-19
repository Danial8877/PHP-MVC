<?php

function dd($value)
{
    echo "
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        pre {
            background: #191e2df2;
            color: white;
            margin: 20px;
            padding: 10px;
            border-radius: 20px;
            box-shadow: 0 0 25px 0.5px #bf0fff;
            font-size: 17.5px;
            outline-offset: 2px;
            outline-width: 2px;
            outline-color: rgb(251, 0, 255);
            outline-style: solid;
        }
    </style>
    ";
    if (is_array($value)) {
        echo "<pre>";
        print_r($value);
        echo "</pre>";
    } else {
        echo "<pre>";
        var_dump($value);
        echo "</pre>";
    }
}

function dateTime()
{
    return date("Y-m-d H:i:s a l");
}

function pdf()
{
    echo "<script>window.print();</script>";
}

function download($path, $name)
{
    if (empty($name) || empty($path)) {
        return false;
    }

    $fileName = basename($name);
    $filePath = realpath($path . "/" . $fileName);

    if (
        empty($fileName) ||
        !file_exists($filePath) ||
        strpos($filePath, realpath(PUBLICROOT)) !== 0
    ) {
        return false;
    }

    $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=\"" . htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') . "\"");
    header("Content-Type: " . $mimeType);
    header("Content-Length: " . filesize($filePath));
    header("Content-Transfer-Encoding: binary");
    header("Expires: 0");

    readfile($filePath);
    exit;
}

function require_view(string $path)
{

    if (file_exists(APPROOT . "/resources/views/" . str_replace(".", "/", $path) . ".php")) {
        require_once APPROOT . "/resources/views/" . str_replace(".", "/", $path) . ".php";
    } else {
        $error = new ErrorPage();
        $error->_404_();
    }
}

function redirect($redirect)
{
    header("location:" . URLROOT . "/" . $redirect);
}

function urlPath($path)
{
    return URLROOT . "/" . $path;
}

function publicPath($path)
{
    echo "./" . $path;
}

function show($value)
{
    return htmlspecialchars($value);
}

function session($name, $value)
{
    $_SESSION[$name] = $value;
}

function unsetSession($name)
{
    unset($_SESSION[$name]);
}

function cookie($name, $time, $value)
{
    setcookie($name, bin2hex($value), [
        "expires" => time() + $time,
        "secure" => true,
        "httponly" => true,
        "samesite" => "Strict"
    ]);
}

function unsetCookie($name)
{
    unset($_COOKIE[$name]);
}

function ip()
{
    $ip = $_SERVER['REMOTE_ADDR'];
    return $ip;
}

function device()
{
    $devic = $_SERVER['HTTP_USER_AGENT'];
    return $devic;
}

function method()
{
    $method = $_SERVER['REQUEST_METHOD'];
    return $method;
}

function pageUrl()
{
    $pageUrl = $_SERVER['PHP_SELF'];
    echo $pageUrl;
    return $pageUrl;
}

function excel($tableName)
{
    $dbHost = DB__HOST;
    $dbName = DB__NAME;
    $dbUser = DB__USER;
    $dbPass = DB__PASS;

    try {
        $conn = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        $stmt = $conn->prepare("SELECT * FROM $tableName LIMIT 0");
        $stmt->execute();
        $columnCount = $stmt->columnCount();

        $columns = [];
        for ($i = 0; $i < $columnCount; $i++) {
            $meta = $stmt->getColumnMeta($i);
            $columns[] = $meta['name'];
        }

        $output = '';
        $output .= implode("\t", $columns) . "\n";

        $stmt = $conn->prepare("SELECT * FROM $tableName");
        $stmt->execute();

        $rowCount = 0;

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $rowData = [];
            foreach ($row as $value) {
                $value = strval($value);
                $value = str_replace(["\t", "\n", "\r"], ' ', $value);
                $value = str_replace('"', '""', $value);
                $value = '"' . $value . '"';
                $rowData[] = $value;
            }
            $output .= implode("\t", $rowData) . "\n";
            $rowCount++;
        }

        if ($rowCount === 0) {
            die("No data found in table '$tableName'");
        }

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"" . $tableName . "_export_" . date('Y-m-d') . ".xls\"");
        header("Cache-Control: max-age=0");

        echo $output;
    } catch (PDOException $e) {
        $error = new ErrorPage();
        $error->_500_();
    } finally {
        if (isset($conn)) {
            $conn = null;
        }
    }
}

function word($tableName)
{
    $dbHost = DB__HOST;
    $dbName = DB__NAME;
    $dbUser = DB__USER;
    $dbPass = DB__PASS;

    try {
        $conn = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        $stmt = $conn->prepare("SELECT * FROM $tableName LIMIT 0");
        $stmt->execute();
        $columnCount = $stmt->columnCount();

        $columns = [];
        for ($i = 0; $i < $columnCount; $i++) {
            $meta = $stmt->getColumnMeta($i);
            $columns[] = $meta['name'];
        }

        $stmt = $conn->prepare("SELECT * FROM $tableName");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);

        if (empty($data)) {
            die("No data found in table '$tableName'");
        }

        $content = '<html xmlns:o="urn:schemas-microsoft-com:office:office"
                xmlns:w="urn:schemas-microsoft-com:office:word"
                xmlns="http://www.w3.org/TR/REC-html40">
                <head>
                <meta charset="UTF-8">
                <title>' . $tableName . '</title>
                <style>
                    table {
                        border-collapse: collapse;
                        width: 100%;
                    }
                    th, td {
                        border: 1px solid #000;
                        padding: 8px;
                        text-align: right;
                    }
                    th {
                        background-color: #f2f2f2;
                        font-weight: bold;
                    }
                </style>
                </head>
                <body>';

        $content .= '<table dir="rtl">';

        $content .= '<tr>';
        foreach ($columns as $column) {
            $content .= '<th>' . htmlspecialchars($column) . '</th>';
        }
        $content .= '</tr>';

        foreach ($data as $row) {
            $content .= '<tr>';
            foreach ($row as $value) {
                $content .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $content .= '</tr>';
        }

        $content .= '</table></body></html>';

        header("Content-Type: application/vnd.ms-word");
        header("Content-Disposition: attachment; filename=\"" . $tableName . "_export_" . date('Y-m-d') . ".doc\"");
        header("Cache-Control: max-age=0");

        echo $content;
    } catch (PDOException $e) {

        $error = new ErrorPage();
        $error->_500_();
    } finally {
        if (isset($conn)) {
            $conn = null;
        }
    }
}

function csv($tableName)
{

    $dbHost = DB__HOST;
    $dbName = DB__NAME;
    $dbUser = DB__USER;
    $dbPass = DB__PASS;

    try {
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $stmt = $pdo->prepare("SELECT * FROM $tableName");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=" ' . $tableName . "_export_" . date('Y-m-d') . ' .csv"');

        $output = fopen('php://output', 'w');

        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    } catch (PDOException $e) {

        $error = new ErrorPage();
        $error->_500_();
    }
}

function tableExport($tableName)
{
    $dbHost = DB__HOST;
    $dbName = DB__NAME;
    $dbUser = DB__USER;
    $dbPass = DB__PASS;

    try {
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $stmt = $pdo->prepare("SHOW CREATE TABLE $tableName");
        $stmt->execute();
        $createTable = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->query("SELECT * FROM $tableName");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $tableName . '_export_' . date('Y-m-d') . '.sql"');

        echo "-- SQL Export for table: $tableName\n";
        echo "-- Export time: " . date('Y-m-d H:i:s') . "\n\n";
        echo "DROP TABLE IF EXISTS `$tableName`;\n";
        echo $createTable['Create Table'] . ";\n\n";

        if (!empty($data)) {
            foreach ($data as $row) {
                $columns = array_map(function ($col) {
                    return "`$col`";
                }, array_keys($row));

                $values = array_map(function ($value) use ($pdo) {
                    return $value === null ? 'NULL' : $pdo->quote($value);
                }, array_values($row));

                echo "INSERT INTO `$tableName` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
        }

        exit;
    } catch (PDOException $e) {
        $error = new ErrorPage();
        $error->_500_();
    }
}

function databaseExport()
{
    $dbHost = DB__HOST;
    $dbName = DB__NAME;
    $dbUser = DB__USER;
    $dbPass = DB__PASS;

    try {
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $dbName . '_full_export_' . date('Y-m-d') . '.sql"');

        echo "-- SQL Export for database: $dbName\n";
        echo "-- Export time: " . date('Y-m-d H:i:s') . "\n";
        echo "-- Host: $dbHost\n";
        echo "-- PHP Version: " . phpversion() . "\n\n";
        echo "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $createTable = $stmt->fetch(PDO::FETCH_ASSOC);

            echo "--\n-- Table structure for table `$table`\n--\n";
            echo "DROP TABLE IF EXISTS `$table`;\n";
            echo $createTable['Create Table'] . ";\n\n";

            echo "--\n-- Dumping data for table `$table`\n--\n";

            $data = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($data)) {
                foreach ($data as $row) {
                    $columns = array_map(function ($col) {
                        return "`$col`";
                    }, array_keys($row));

                    $values = array_map(function ($value) use ($pdo) {
                        return $value === null ? 'NULL' : $pdo->quote($value);
                    }, array_values($row));

                    echo "INSERT INTO `$table` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
                echo "\n";
            }
        }

        echo "SET FOREIGN_KEY_CHECKS = 1;\n";
        exit;
    } catch (PDOException $e) {

        $error = new ErrorPage();
        $error->_500_();
    }
}
