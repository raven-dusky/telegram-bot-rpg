<?php
require_once("/var/www/html/index.php");

$selectUsersConsumables = $pdo->prepare("SELECT * FROM users_consumables WHERE expiration_datetime <= NOW() AND user_id NOT IN (SELECT user_id FROM system_bans)");
$selectUsersConsumables->execute();

while ($rowUsersConsumables = $selectUsersConsumables->fetch(PDO::FETCH_ASSOC)) {
	try {
		$pdo->beginTransaction();
		$selectConsumables = $pdo->prepare("SELECT * FROM consumables WHERE item_id = :item_id");
		$selectConsumables->execute([":item_id" => $rowUsersConsumables["item_id"]]);
		$rowConsumables = $selectConsumables->fetch(PDO::FETCH_ASSOC);
		if ($rowConsumables["base_attributes"] !== null) {
			$baseAttributes = json_decode($rowConsumables["base_attributes"], true);
			$baseAttributes["hit_rate"] = isset($baseAttributes["hit_rate"]) ? $baseAttributes["hit_rate"] : 0;
			$baseAttributes["physical_damage"] = isset($baseAttributes["physical_damage"]) ? $baseAttributes["physical_damage"] : 0;
			$baseAttributes["ranged_damage"] = isset($baseAttributes["ranged_damage"]) ? $baseAttributes["ranged_damage"] : 0;
			$baseAttributes["magic_damage"] = isset($baseAttributes["magic_damage"]) ? $baseAttributes["magic_damage"] : 0;
			$baseAttributes["physical_defense"] = isset($baseAttributes["physical_defense"]) ? $baseAttributes["physical_defense"] : 0;	
			$baseAttributes["magic_defense"] = isset($baseAttributes["magic_defense"]) ? $baseAttributes["magic_defense"] : 0;$baseAttributes["critical_damage"] = isset($baseAttributes["critical_damage"]) ? $baseAttributes["critical_damage"] : 0;		
			$baseAttributes["evade"] = isset($baseAttributes["evade"]) ? $baseAttributes["evade"] : 0;	
			$baseAttributes["action_speed"] = isset($baseAttributes["action_speed"]) ? $baseAttributes["action_speed"] : 0;
			$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET hit_rate = hit_rate - :hit_rate, physical_damage = physical_damage - :physical_damage, ranged_damage = ranged_damage - :ranged_damage, magic_damage = magic_damage - :magic_damage, physical_defense = physical_defense - :physical_defense, magic_defense = magic_defense - :magic_defense, critical_damage = critical_damage - :critical_damage, evade = evade - :evade, action_speed = action_speed - :action_speed WHERE user_id = :user_id");
			$updateUsersStatistics->execute([
				":hit_rate" => $baseAttributes["hit_rate"], 
				":physical_damage" => $baseAttributes["physical_damage"], 
				":ranged_damage" => $baseAttributes["ranged_damage"], 
				":magic_damage" => $baseAttributes["magic_damage"], 
				":physical_defense" => $baseAttributes["physical_defense"], 
				":magic_defense" => $baseAttributes["magic_defense"],
				":critical_damage" => $baseAttributes["critical_damage"],
				":evade" => $baseAttributes["evade"],
				":action_speed" => $baseAttributes["action_speed"],
				":user_id" => $rowUsersConsumables["user_id"]
			]);
		}
		if ($rowConsumables["bonus_attributes"] !== null) {
			$bonusAttributes = json_decode($rowConsumables["bonus_attributes"], true);
			$bonusAttributes["experience"] = isset($bonusAttributes["experience"]) ? $bonusAttributes["experience"] : 0;
			$bonusAttributes["coins"] = isset($bonusAttributes["coins"]) ? $bonusAttributes["coins"] : 0;
			$bonusAttributes["hit_rate"] = isset($bonusAttributes["hit_rate"]) ? $bonusAttributes["hit_rate"] : 0;
			$bonusAttributes["physical_damage"] = isset($bonusAttributes["physical_damage"]) ? $bonusAttributes["physical_damage"] : 0;
			$bonusAttributes["ranged_damage"] = isset($bonusAttributes["ranged_damage"]) ? $bonusAttributes["ranged_damage"] : 0;
			$bonusAttributes["magic_damage"] = isset($bonusAttributes["magic_damage"]) ? $bonusAttributes["magic_damage"] : 0;
			$bonusAttributes["physical_defense"] = isset($bonusAttributes["physical_defense"]) ? $bonusAttributes["physical_defense"] : 0;	
			$bonusAttributes["magic_defense"] = isset($bonusAttributes["magic_defense"]) ? $bonusAttributes["magic_defense"] : 0;
			$bonusAttributes["critical_damage"] = isset($bonusAttributes["critical_damage"]) ? $bonusAttributes["critical_damage"] : 0;		
			$bonusAttributes["evade"] = isset($bonusAttributes["evade"]) ? $bonusAttributes["evade"] : 0;	
			$bonusAttributes["action_speed"] = isset($bonusAttributes["action_speed"]) ? $bonusAttributes["action_speed"] : 0;
			$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics_bonuses SET experience = experience - :experience, coins = coins - :coins, hit_rate = hit_rate - :hit_rate, physical_damage = physical_damage - :physical_damage, ranged_damage = ranged_damage - :ranged_damage, magic_damage = magic_damage - :magic_damage, physical_defense = physical_defense - :physical_defense, magic_defense = magic_defense - :magic_defense, critical_damage = critical_damage - :critical_damage, evade = evade - :evade, action_speed = action_speed - :action_speed WHERE user_id = :user_id");
			$updateUsersStatistics->execute([
				":experience" => $bonusAttributes["experience"], 
				":coins" => $bonusAttributes["coins"], 
				":hit_rate" => $bonusAttributes["hit_rate"], 
				":physical_damage" => $bonusAttributes["physical_damage"], 
				":ranged_damage" => $bonusAttributes["ranged_damage"], 
				":magic_damage" => $bonusAttributes["magic_damage"], 
				":physical_defense" => $bonusAttributes["physical_defense"], 
				":magic_defense" => $bonusAttributes["magic_defense"],
				":critical_damage" => $bonusAttributes["critical_damage"],
				":evade" => $bonusAttributes["evade"],
				":action_speed" => $bonusAttributes["action_speed"],
				":user_id" => $rowUsersConsumables["user_id"]
			]);
		}
		$deleteUsersConsumables = $pdo->prepare("DELETE FROM users_consumables WHERE item_id = :item_id AND user_id = :user_id");
		$deleteUsersConsumables->execute([":item_id" => $rowUsersConsumables["item_id"], ":user_id" => $rowUsersConsumables["user_id"]]);
		$pdo->commit();
	} catch (Exception) {
		$pdo->rollBack();
		exit;
	}
}
