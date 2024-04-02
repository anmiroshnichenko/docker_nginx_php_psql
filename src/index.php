<?php
define("DBNAME",     "motiware");
define("DBHOST",     "db");
define("DBUSER",    "webuser");
define("DBPASS",    "SecretPassword");

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

$dbconn = pg_connect("host=" . DBHOST . " dbname=" . DBNAME . " user=" . DBUSER . " password=" . DBPASS)
or die('Не удалось соединиться: ' . pg_last_error());

$sqlQuery = '
DO $$
BEGIN
        IF NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE table_name = \'testlog\') THEN
              CREATE TABLE testlog
              (
                  client_ip inet  NULL,
                  viewdate timestamp without time zone
              );
        END IF;
END$$;
          ';
pg_query($sqlQuery) or die('Ошибка запроса: ' . pg_last_error());

$sqlQuery = 'INSERT INTO testlog values (\'' . $ip . '\', now())';
pg_query($sqlQuery) or die('Ошибка запроса: ' . pg_last_error());

$sqlQuery = 'SELECT * FROM testlog ORDER BY viewdate DESC LIMIT 1';
$result = pg_query($sqlQuery) or die('Ошибка запроса: ' . pg_last_error());

$line = pg_fetch_array($result, null, PGSQL_ASSOC);
echo "Открытие страницы. IP: " . $line['client_ip'] . ' Время: '. $line['viewdate'];

$sqlQuery = 'TRUNCATE testlog;';
pg_query($sqlQuery) or die('Ошибка запроса: ' . pg_last_error());

pg_close($dbconn);


echo '<!doctype html>
        <html lang=ru>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport"
                  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <title>Document</title>
            <style>
                .header{
                    text-align: left;
                    font-weight: bold;
                    font-size: 18px;
                    padding: 30px 0;
                }
                .message{
                    color: #fe0000;
                }
            </style>
        </head>
        <body>
            <div class="header">Загрузить файл</div>';
    if ($_FILES && $_FILES["filename"]["error"]== UPLOAD_ERR_OK)
    {
        $name = $_FILES["userfile"]["name"];
        move_uploaded_file($_FILES["userfile"]["tmp_name"], __DIR__ . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . $name);
        echo "<div class='message'>Файл загружен</div>";
    }

echo '
    <form enctype="multipart/form-data" action="" method="POST">
        <input name="userfile" type="file" />
        <input type="submit" value="Отправить файл" />
    </form>
';

        $files = scandir(__DIR__ . DIRECTORY_SEPARATOR . 'upload');
        if(!empty($files) && is_array($files)){
            echo '<div class="header">Загруженные файлы:</div><ul>';

            foreach ($files as $filename){
                if($filename === '.' || $filename === '..') continue;
                echo '<li><a href="upload'. DIRECTORY_SEPARATOR . $filename . '">' . $filename . '</a></li>';
            }
            echo "</ul>";

        }
echo '</body></html>';
