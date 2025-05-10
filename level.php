<?php
include_once("/var/www/html/inventory.php");

if ($updates) {
	$userId = $userId ?? $queryUserId;
	try {
		$selectUsers = $pdo->prepare("SELECT 1 FROM users WHERE user_id = :user_id AND user_id NOT IN (SELECT user_id FROM system_bans)");
		$selectUsers->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage());
		exit;
	}
	if ($rowUsers = $selectUsers->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersProfiles = $pdo->prepare("SELECT * FROM users_profiles WHERE user_id = :user_id");
			$selectUsersProfiles->execute([":user_id" => $userId]);
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
			if ($rowUsersProfiles["experience"] >= round(pow($rowUsersProfiles["level"], 3.80))) {
				$formulaHealthPoints = round(10 * pow($rowUsersProfiles["level"], 1.2));
				$formulaPhysicalDamage = round(0.13113 * pow($rowUsersProfiles["level"],  1.1)) + 1;
				$formulaRangedDamage = round(0.10684 * pow($rowUsersProfiles["level"], 1.08)) + 1;
				$formulaMagicDamage = round(0.11599 * pow($rowUsersProfiles["level"], 1.06)) + 1;
				$formulaPhysicalDefense = round(0.08058 * pow($rowUsersProfiles["level"], 1.05)) + 1;
				$formulaMagicDefense = round(0.07873 * pow($rowUsersProfiles["level"], 1.03)) + 1;
				$formulaManaPoints = round(3 * pow($rowUsersProfiles["level"], 1.05));
				try {
					$pdo->beginTransaction();
					$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points + 3 WHERE user_id = :user_id");
					$updateUsersAttributes->execute([":user_id" => $userId]);
					$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET level = level + 1, experience = 0, diamonds = diamonds + 10 WHERE user_id = :user_id");
					$updateUsersProfiles->execute([":user_id" => $userId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET 
						max_health_points = max_health_points + :max_health_points, 
						health_points = max_health_points, 
						energy_points = max_energy_points, 
						physical_damage = physical_damage + :physical_damage, 
						ranged_damage = ranged_damage + :ranged_damage, 
						magic_damage = magic_damage + :magic_damage, 
						physical_defense = physical_defense + :physical_defense, 
						magic_defense = magic_defense + :magic_defense, 
						max_mana_points = max_mana_points + :max_mana_points, 
						mana_points = max_mana_points 
						WHERE user_id = :user_id");
					$updateUsersStatistics->execute([
						":max_health_points" => $formulaHealthPoints,
						":physical_damage" => $formulaPhysicalDamage,
						":ranged_damage" => $formulaRangedDamage,
						":magic_damage" => $formulaMagicDamage,
						":physical_defense" => $formulaPhysicalDefense,
						":magic_defense" => $formulaMagicDefense,
						":max_mana_points" => $formulaManaPoints,
						":user_id" => $userId
					]);
					addItemToInventory($pdo, $userId, 1);
					$pdo->commit();
				}  catch (Exception $exception) {
					$pdo->rollBack();
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage());
					exit;
				}
			}
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage());
			exit;
		}
	}
}
