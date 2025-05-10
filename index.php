<?php
require_once('/var/www/config/api_key.php');
require_once('/var/www/config/conn.php');

date_default_timezone_set('Europe/Rome');
define('WEBSITE', 'https://api.telegram.org/bot' . $bot);

$updates = json_decode(file_get_contents('php://input'), true);

try {
	$pdo = new PDO("mysql:host={$config['mysql_host']};dbname={$config['mysql_database']}", $config['mysql_username'], $config['mysql_password']);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception) {
    die;
}

require_once('/var/www/config/api_types.php');
require_once('/var/www/config/api_methods.php');

require_once('logging.php');
require_once('start.php');
include_once('back.php');

foreach (glob('*.php') as $filename) {
    include_once $filename;
}
