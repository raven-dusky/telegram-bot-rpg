<?php
require_once("/var/www/html/index.php");

try {
	$updateUsersGames = $pdo->prepare("UPDATE users_games SET dice = 0, darts = 0, slots = 0 WHERE user_id NOT IN (SELECT user_id FROM system_bans)");
	$updateUsersGames->execute();
} catch (Exception) {
	exit;
}
