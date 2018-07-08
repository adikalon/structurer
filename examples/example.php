<?php
header('Content-type: text/html; charset=utf-8');
require_once(__DIR__ . '/../vendor/autoload.php');

use Hellpers\Structurer;

$structurer = new Structurer(__DIR__);

// Чистый путь
$path = $structurer->get(
    $structurer->q('new/folder'),
    $structurer->q('file.txt')
);

echo $path; // ...new/folder/file.txt

// С автоопределением даты
$path = $structurer->get(
    $structurer->q('new/folder'),
    'Y-m-d' . $structurer->q('.txt')
);

echo $path; // ...new/folder/2018-02-25.txt 