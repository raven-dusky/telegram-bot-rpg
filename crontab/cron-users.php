<?php
require_once("/var/www/html/index.php");

try {
	$deleteUsers = $pdo->prepare("DELETE FROM users WHERE user_id NOT IN (SELECT user_id FROM users_maps WHERE map_id >= 1 AND current_stage > 1) AND NOW() > registration_datetime + INTERVAL 24 HOUR");
	$deleteUsers->execute();
} catch (Exception) {
	exit;
}
