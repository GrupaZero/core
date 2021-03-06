<?php
// Here you can initialize variables that will be available to your tests

use Codeception\Lib\Driver\PostgreSql;

if(isPlatformRun()){
    require_once __DIR__ . '/fixture/User.php';
    require_once __DIR__ . '/fixture/HelloWorld.php';
    require_once __DIR__ . '/fixture/UploadableEntity.php';
    return;
}

if (file_exists(dirname(__DIR__) . '/.env.testing')) {
    (new \Dotenv\Dotenv(dirname(__DIR__), '.env.testing'))->load();
}

$host     = env('DB_HOST', 'localhost');
$port     = env('DB_PORT', 5432);
$dbName   = env('DB_DATABASE', 'gzero_cms');
$user     = env('DB_USERNAME', 'postgres');
$password = env('DB_PASSWORD', '');

$sql = file_get_contents('vendor/gzero/testing/db/dump.sql');
// remove C-style comments (except MySQL directives)
$sql = preg_replace('%/\*(?!!\d+).*?\*/%s', '', $sql);
if (!empty($sql)) {
    // split SQL dump into lines
    $sql = preg_split('/\r\n|\n|\r/', $sql, -1, PREG_SPLIT_NO_EMPTY);
}

$db = new PostgreSql("pgsql:host=$host port=$port dbname=$dbName", $user, $password);

$db->cleanup();
$db->load($sql);

function isPlatformRun()
{
    return preg_match('|vendor\/gzero\/core\/$|', \Codeception\Configuration::projectDir());
}
