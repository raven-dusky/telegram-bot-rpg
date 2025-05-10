<?php
require_once("/var/www/html/index.php");
require_once("/var/www/html/inventory.php");

try {
	$selectUsersProfiles = $pdo->prepare("SELECT * FROM users_profiles 
		WHERE experience >= ROUND(POW(level, 3.80)) 
		AND user_id NOT IN (SELECT user_id FROM system_bans)");
	$selectUsersProfiles->execute();
} catch (Exception) {
	exit;
}

while ($rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC)) {
	try {
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
			$updateUsersAttributes->execute([":user_id" => $rowUsersProfiles["user_id"]]);
			$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET level = level + 1, experience = 0, diamonds = diamonds + 10 WHERE user_id = :user_id");
			$updateUsersProfiles->execute([":user_id" => $rowUsersProfiles["user_id"]]);
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
				":user_id" => $rowUsersProfiles["user_id"]
			]);
			addItemToInventory($pdo, $userId, 1);
			systemLogs($pdo, $rowUsersProfiles["user_id"], "INFO", "Level up from level " . $rowUsersProfiles["level"] . " to level " . ($rowUsersProfiles["level"] + 1) . ": Gained +" . number_format($formulaHealthPoints) . " (Health Points), +" . number_format($formulaPhysicalDamage) . " (Physical Damage), +" . number_format($formulaRangedDamage) . " (Ranged Damage), +" . number_format($formulaMagicDamage) . " (Magic Damage), +" . number_format($formulaPhysicalDefense) . " (Physical Defense), +" . number_format($formulaMagicDefense) . " (Magic Defense), +3 (Attribute Points) and +10 (Diamonds)");
			$pdo->commit();
		}  catch (Exception) {
			$pdo->rollBack();
			exit;
		}
	} catch (Exception) {
		exit;
	}
}
