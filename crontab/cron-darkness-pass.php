<?php
require_once("/var/www/html/index.php");

try {
	$selectPayments = $pdo->prepare("SELECT * FROM payments WHERE product_name = 'Darkness Pass (DP)' AND product_datetime <= NOW() AND user_id NOT IN (SELECT user_id FROM system_bans) ORDER BY payments.id DESC");
	$selectPayments->execute();
} catch (Exception) {
	exit;
}

while ($rowPayments = $selectPayments->fetch(PDO::FETCH_ASSOC)) {
	try {
		$pdo->beginTransaction();
		$deletePayment = $pdo->prepare("DELETE FROM payments WHERE user_id = :user_id AND product_name = 'Darkness Pass (DP)'");
		$deletePayment->execute([":user_id" => $rowPayments["user_id"]]);
		$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET energy_points = 125, max_energy_points = max_energy_points - 375 WHERE user_id = :user_id");
		$updateUsersStatistics->execute([":user_id" => $rowPayments["user_id"]]);
		$updateUsersStatisticsBonuses = $pdo->prepare("UPDATE users_statistics_bonuses SET experience = experience - 25, coins = coins - 10 WHERE user_id = :user_id");
		$updateUsersStatisticsBonuses->execute([":user_id" => $rowPayments["user_id"]]);
		$pdo->commit();
	} catch (Exception) {
		$pdo->rollBack();
		exit;
	}
}
