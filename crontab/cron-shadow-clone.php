<?php
require_once("/var/www/html/index.php");

try {
	$selectUsersShadowClone = $pdo->prepare("SELECT * FROM users_shadow_clone WHERE status = 1 AND user_id NOT IN (SELECT user_id FROM system_bans)");
	$selectUsersShadowClone->execute();
} catch (Exception) {
	exit;
}

while ($rowUsersShadowClone = $selectUsersShadowClone->fetch(PDO::FETCH_ASSOC)) {
	try {
		$formulaManaPoints = max(1, 25 - (($rowUsersShadowClone["level"] - 1) * (24 / 98)));
		$selectUsersStatistics = $pdo->prepare("SELECT * FROM users_statistics WHERE mana_points >= :mana_points AND user_id = :user_id");
		$selectUsersStatistics->execute([":mana_points" => $formulaManaPoints, ":user_id" => $rowUsersShadowClone["user_id"]]);
		if ($rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC)) {
			$selectPayments = $pdo->prepare("SELECT 1 FROM payments WHERE product_name = 'Darkness Pass (DP)' AND product_datetime >= NOW() OR product_datetime IS NULL AND user_id = :user_id ORDER BY payments.id DESC LIMIT 1");
			$selectPayments->execute([":user_id" => $queryUserId]);
			$rowPayments = $selectPayments->fetch(PDO::FETCH_ASSOC);
			$darknessPass = $rowPayments ? 0.09 : 0;
			$formulaExperience = pow($rowUsersShadowClone["level"], 1.52) * 1 * (1 + $darknessPass);
			$updateUsersShadowClone = $pdo->prepare("UPDATE users_shadow_clone SET experience = experience + :experience WHERE user_id = :user_id");
			$updateUsersShadowClone->execute([":experience" => $formulaExperience,":user_id" => $rowUsersShadowClone["user_id"]]);
			$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET mana_points = mana_points - :mana_points WHERE user_id = :user_id");
			$updateUsersStatistics->execute([":mana_points" => $formulaManaPoints, ":user_id" => $rowUsersShadowClone["user_id"]]);
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE status = 1 AND user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $rowUsersShadowClone["user_id"]]);
			if ($rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC)) {
				$updateUsersProfiles = $pdo->prepare("UPDATE users_specializations SET experience = experience + :experience WHERE user_id = :user_id");
				$updateUsersProfiles->execute([":experience" => $formulaExperience, ":user_id" => $rowUsersShadowClone["user_id"]]);
			} else {
				$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET experience = experience + :experience WHERE user_id = :user_id");
				$updateUsersProfiles->execute([":experience" => $formulaExperience, ":user_id" => $rowUsersShadowClone["user_id"]]);
			}
		} else {
			$updateUsersShadowClone = $pdo->prepare("UPDATE users_shadow_clone SET status = 0 WHERE user_id = :user_id");
			$updateUsersShadowClone->execute([":user_id" => $rowUsersShadowClone["user_id"]]);
		}
	} catch (Exception) {
		exit;
	}
}
