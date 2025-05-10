<?php
if ($preCheckoutQueryId) {
	try {
		answerPreCheckoutQuery($preCheckoutQueryId);
	} catch (Exception) {
		exit;
	}
}

if ($successfulPayment) {
	switch ($successfulPaymentTotalAmount) {
		case 100:
			try {
				$pdo->beginTransaction();
				$insertPayments = $pdo->prepare("INSERT INTO payments (user_id, product_name) VALUES (:user_id, :product_name)");
				$insertPayments->execute([":user_id" => $userId, ":product_name" => "1,000 (Diamonds)"]);
				$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET diamonds = diamonds + 1050 WHERE user_id = :user_id");
				$updateUsersProfiles->execute([":user_id" => $userId]);
				$pdo->commit();
				sendMessage($chatId, "🎉 Thank you for your purchase! You've received 💎 <b>1,000 Diamonds</b> plus a <b>5%</b> bonus, bringing your total to <b>1,050 Diamonds</b>.");
				systemLogs($pdo, $userId, "INFO", "1,000 Diamonds plus a 5% bonus successfully purchased.");
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
			break;
		case 125:
			try {
				$pdo->beginTransaction();
				$insertPayments = $pdo->prepare("INSERT INTO payments (id, user_id, product_name, product_datetime) VALUES (:id, :user_id, :product_name, :product_datetime)");
				$insertPayments->execute([":user_id" => $userId, ":product_name" => "Darkness Pass (DP)", "product_datetime" => date("Y-m-d H:i:s", strtotime("+30 days"))]);
				$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET diamonds = diamonds + 300 WHERE user_id = :user_id");
				$updateUsersProfiles->execute([":user_id" => $userId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET max_energy_points = max_energy_points + 375 WHERE user_id = :user_id");
				$updateUsersStatistics->execute([":user_id" => $userId]);
				$updateUsersStatisticsBonuses = $pdo->prepare("UPDATE users_statistics_bonuses SET experience = experience + 25, coins = coins + 10 WHERE user_id = :user_id");
				$updateUsersStatisticsBonuses->execute([":user_id" => $userId]);
				$pdo->commit();
				sendMessage($chatId, "🎉 Thank you for embracing the shadows! You've unlocked the 😈 <i>Darkness Pass (DP)</i> – enjoy <b>30 days</b> of exclusive power and rewards!");
				systemLogs($pdo, $userId, "INFO", "(Darkness Pass) successfully unlocked.");
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
			break;
		case 500:
			try {
				$pdo->beginTransaction();
				$insertPayments = $pdo->prepare("INSERT INTO payments (user_id, product_name) VALUES (:user_id, :product_name)");
				$insertPayments->execute([":user_id" => $userId, ":product_name" => "5,000 (Diamonds)"]);
				$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET diamonds = diamonds + 5500 WHERE user_id = :user_id");
				$updateUsersProfiles->execute([":user_id" => $userId]);
				$pdo->commit();
				sendMessage($chatId, "🎉 Thank you for your purchase! You've received 💎 <b>5,000 Diamonds</b> plus a <b>10%</b> bonus, bringing your total to <b>5,500 Diamonds</b>.");
				systemLogs($pdo, $userId, "INFO", "5,000 Diamonds plus a 10% bonus successfully purchased.");
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
			break;
		case 1200:
			try {
				$pdo->beginTransaction();
				$insertPayments = $pdo->prepare("INSERT INTO payments (user_id, product_name) VALUES (:user_id, :product_name)");
				$insertPayments->execute([":user_id" => $userId, ":product_name" => "12,000 (Diamonds)"]);
				$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET diamonds = diamonds + 13800 WHERE user_id = :user_id");
				$updateUsersProfiles->execute([":user_id" => $userId]);
				$pdo->commit();
				sendMessage($chatId, "🎉 Thank you for your purchase! You've received 💎 <b>12,000 Diamonds</b> plus a <b>15%</b> bonus, bringing your total to <b>13,800 Diamonds</b>.");
				systemLogs($pdo, $userId, "INFO", "12,000 Diamonds plus a 15% bonus successfully purchased.");
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
			break;
		case 2500:
			try {
				$pdo->beginTransaction();
				$insertPayments = $pdo->prepare("INSERT INTO payments (user_id, product_name) VALUES (:user_id, :product_name)");
				$insertPayments->execute([":user_id" => $userId, ":product_name" => "25,000 (Diamonds)"]);
				$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET diamonds = diamonds + 30000 WHERE user_id = :user_id");
				$updateUsersProfiles->execute([":user_id" => $userId]);
				$pdo->commit();
				sendMessage($chatId, "🎉 Thank you for your purchase! You've received 💎 <b>25,000 Diamonds</b> plus a <b>20%</b> bonus, bringing your total to <b>30,000 Diamonds</b>.");
				systemLogs($pdo, $userId, "INFO", "25,000 Diamonds plus a 20% bonus successfully purchased.");
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
			break;
		case 5000:
			try {
				$pdo->beginTransaction();
				$insertPayments = $pdo->prepare("INSERT INTO payments (user_id, product_name) VALUES (:user_id, :product_name)");
				$insertPayments->execute([":user_id" => $userId, ":product_name" => "50,000 (Diamonds)"]);
				$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET diamonds = diamonds + 62500 WHERE user_id = :user_id");
				$updateUsersProfiles->execute([":user_id" => $userId]);
				$pdo->commit();
				sendMessage($chatId, "🎉 Thank you for your purchase! You've received 💎 <b>50,000 Diamonds</b> plus a <b>25%</b> bonus, bringing your total to <b>62,500 Diamonds</b>.");
				systemLogs($pdo, $userId, "INFO", "50,000 Diamonds plus a 25% bonus successfully purchased.");
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
			break;
	}
}
