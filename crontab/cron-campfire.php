<?php
require_once("/var/www/html/index.php");

try {
	$selectUsersCampfire = $pdo->prepare("SELECT * FROM users_campfire WHERE status = 1 AND user_id NOT IN (SELECT user_id FROM system_bans)");
	$selectUsersCampfire->execute();
} catch (Exception) {
	exit;
}

while ($rowUsersCampfire = $selectUsersCampfire->fetch(PDO::FETCH_ASSOC)) {
	try {
		$selectUsersStatistics = $pdo->prepare("SELECT * FROM users_statistics WHERE user_id = :user_id");
		$selectUsersStatistics->execute([":user_id" => $rowUsersCampfire["user_id"]]);
		$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
		$selectUsersProfiles = $pdo->prepare("SELECT level FROM users_profiles WHERE user_id = :user_id");
		$selectUsersProfiles->execute([":user_id" => $rowUsersCampfire["user_id"]]);
		$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
		$selectUsersPerks = $pdo->prepare("SELECT endurance_level FROM users_perks WHERE user_id = :user_id");
		$selectUsersPerks->execute([":user_id" => $rowUsersCampfire["user_id"]]);
		$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
		$formulaRecoveryHealthPoints = round(($rowUsersCampfire["level"] * 0.003 + $rowUsersProfiles["level"] * 0.0015 + $rowUsersPerks["endurance_level"] * 0.0003) * $rowUsersStatistics["max_health_points"] / 100) + 1;
		$formulaManaPoints = max(0.10, $rowUsersStatistics["max_mana_points"] * (1.67 / 100));
	} catch (Exception) {
		exit;
	}
	if (($rowUsersStatistics["energy_points"] == $rowUsersStatistics["max_energy_points"]) && ($rowUsersStatistics["health_points"] == $rowUsersStatistics["max_health_points"]) && ($rowUsersStatistics["mana_points"] == $rowUsersStatistics["max_mana_points"])) {
		$updateUsersCampfire = $pdo->prepare("UPDATE users_campfire SET status = 0 WHERE user_id = :user_id");
		$updateUsersCampfire->execute([":user_id" => $rowUsersCampfire["user_id"]]);
		exit;
	}
	try {
		$updateUsersStatistics = $pdo->prepare("
			UPDATE users_statistics 
			SET 
				energy_points = LEAST(energy_points + 1, max_energy_points),
				health_points = LEAST(health_points + :health_points, max_health_points),
				mana_points = LEAST(mana_points + :mana_points, max_mana_points)
			WHERE user_id = :user_id
		");
		$updateUsersStatistics->execute([
			"health_points" => $formulaRecoveryHealthPoints,
			"mana_points" => $formulaManaPoints,
			"user_id" => $rowUsersCampfire["user_id"]
		]);
	} catch (Exception) {
		exit;
	}
}
