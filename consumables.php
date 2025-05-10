<?php
include_once("/var/www/html/inventory.php");

if ($queryData == "consumables_consume") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT * FROM users_utilities WHERE section = 'Inventory (search)' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		preg_match("/\[([^\]]+)\]/", $queryText, $matches);
		try {
			$selectItems = $pdo->prepare("SELECT * FROM items WHERE name LIKE :name");
			$selectItems->execute([":name" => trim($matches[1])]);
			$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
			$selectConsumables = $pdo->prepare("SELECT * FROM consumables WHERE item_id = :item_id");
			$selectConsumables->execute([":item_id" => $rowItems["id"]]);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		if ($rowConsumables = $selectConsumables->fetch(PDO::FETCH_ASSOC)) {
			try {
				$selectInventory = $pdo->prepare("SELECT * FROM users_inventory WHERE item_id = :item_id AND user_id = :user_id AND quantity >= 1");
				$selectInventory->execute([":user_id" => $queryUserId, ":item_id" => $rowConsumables["item_id"]]);
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
			if ($rowInventory = $selectInventory->fetch(PDO::FETCH_ASSOC)) {
				if ($rowConsumables["duration"] !== null) {
					try {
						$selectUsersConsumables = $pdo->prepare("SELECT * FROM users_consumables WHERE user_id = :user_id AND item_id = :item_id");
						$selectUsersConsumables->execute([":user_id" => $queryUserId, ":item_id" => $rowConsumables["item_id"]]);
					} catch (Exception $exception) {
						systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
						exit;
					}
					if ($rowUsersConsumables = $selectUsersConsumables->fetch(PDO::FETCH_ASSOC)) {
						try {
							$updateUsersConsumables = $pdo->prepare("UPDATE users_consumables SET expiration_datetime = expiration_datetime + INTERVAL :expiration_datetime MINUTE WHERE item_id = :item_id AND user_id = :user_id");
							$updateUsersConsumables->execute([":expiration_datetime" => $rowConsumables["duration"], ":item_id" => $rowConsumables["item_id"], ":user_id" => $queryUserId]);
							removeItemFromInventory($pdo, $queryUserId, $rowConsumables["item_id"]);
							answerCallbackQuery($queryId, "⏳ (Consumable) already active. Duration extended!");
						} catch (Exception $exception) {
							systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
							exit;
						}
					} else {
						try {
							$insertUsersConsumables = $pdo->prepare("INSERT INTO users_consumables (user_id, item_id, expiration_datetime) VALUES (:user_id, :item_id, NOW() + INTERVAL :expiration_datetime MINUTE)");
							$insertUsersConsumables->execute([":user_id" => $queryUserId, ":item_id" => $rowConsumables["item_id"], ":expiration_datetime" => $rowConsumables["duration"]]);
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
								$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET hit_rate = hit_rate + :hit_rate, physical_damage = physical_damage + :physical_damage, ranged_damage = ranged_damage + :ranged_damage, magic_damage = magic_damage + :magic_damage, physical_defense = physical_defense + :physical_defense, magic_defense = magic_defense + :magic_defense, critical_damage = critical_damage + :critical_damage, evade = evade + :evade, action_speed = action_speed + :action_speed WHERE user_id = :user_id");
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
									":user_id" => $queryUserId
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
								$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics_bonuses SET experience = experience + :experience, coins = coins + :coins, hit_rate = hit_rate + :hit_rate, physical_damage = physical_damage + :physical_damage, ranged_damage = ranged_damage + :ranged_damage, magic_damage = magic_damage + :magic_damage, physical_defense = physical_defense + :physical_defense, magic_defense = magic_defense + :magic_defense, critical_damage = critical_damage + :critical_damage, evade = evade + :evade, action_speed = action_speed + :action_speed WHERE user_id = :user_id");
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
									":user_id" => $queryUserId
								]);
							}
							removeItemFromInventory($pdo, $queryUserId, $rowConsumables["item_id"]);
							answerCallbackQuery($queryId, "🫡 (Consumable) activated successfully.");
						} catch (Exception $exception) {
							systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
							exit;
						}
					}
				} else {
					if ($rowConsumables["item_id"] == 1) {
						try {
							$selectUsersStatistics = $pdo->prepare("SELECT * FROM users_statistics WHERE user_id = :user_id");
							$selectUsersStatistics->execute([":user_id" => $queryUserId]);
							$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
						} catch (Exception $exception) {
							systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
							exit;
						}
						try {
							if ($rowUsersStatistics["energy_points"] < $rowUsersStatistics["max_energy_points"]) {
								$baseAttributes = json_decode($rowConsumables["base_attributes"], true);
								$baseAttributes["energy_points"] = isset($baseAttributes["energy_points"]) ? $baseAttributes["energy_points"] : 0;
								$formulaEnergyPoints = min($baseAttributes["energy_points"], $rowUsersStatistics["max_energy_points"] - $rowUsersStatistics["energy_points"]);
								$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET energy_points = energy_points + :energy_points WHERE user_id = :user_id");
								$updateUsersStatistics->execute([":energy_points" => $formulaEnergyPoints, ":user_id" => $queryUserId]);
								removeItemFromInventory($pdo, $queryUserId, $rowConsumables["item_id"]);
								answerCallbackQuery($queryId, "🫡 (Item) successfully consumed.");
							} else {
								answerCallbackQuery($queryId, "🔋 Your (Energy Points) is already full.");
							}
						} catch (Exception $exception) {
							systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
							exit;
						}
					} elseif ($rowConsumables["item_id"] == 3) {
						try {
							$selectUsersStatistics = $pdo->prepare("SELECT * FROM users_statistics WHERE user_id = :user_id");
							$selectUsersStatistics->execute([":user_id" => $queryUserId]);
							$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
						} catch (Exception $exception) {
							systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
							exit;
						}
						try {
							if ($rowUsersStatistics["health_points"] < $rowUsersStatistics["max_health_points"]) {
								$baseAttributes = json_decode($rowConsumables["base_attributes"], true);
								$baseAttributes["health_points"] = isset($baseAttributes["health_points"]) ? $baseAttributes["health_points"] : 0;
								$healthRestore = floor(($baseAttributes["health_points"] / 100) * $rowUsersStatistics["max_health_points"]);
								$formulaHealthPoints = min($healthRestore, $rowUsersStatistics["max_health_points"] - $rowUsersStatistics["health_points"]);
								$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET health_points = health_points + :health_points WHERE user_id = :user_id");
								$updateUsersStatistics->execute([":health_points" => $formulaHealthPoints, ":user_id" => $queryUserId]);
								removeItemFromInventory($pdo, $queryUserId, $rowConsumables["item_id"]);
								answerCallbackQuery($queryId, "🫡 (Item) successfully consumed.");
							} else {
								answerCallbackQuery($queryId, "❤️ Your (Health Points) is already full.");
							}
						} catch (Exception $exception) {
							systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
							exit;
						}
					} elseif ($rowConsumables["item_id"] == 20) {
						try {
							$selectUsersStatistics = $pdo->prepare("SELECT * FROM users_statistics WHERE user_id = :user_id");
							$selectUsersStatistics->execute([":user_id" => $queryUserId]);
							$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
						} catch (Exception $exception) {
							systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
							exit;
						}
						try {
							if ($rowUsersStatistics["health_points"] < $rowUsersStatistics["max_health_points"]) {
								$baseAttributes = json_decode($rowConsumables["base_attributes"], true);
								$baseAttributes["health_points"] = isset($baseAttributes["health_points"]) ? $baseAttributes["health_points"] : 0;
								$healthRestore = floor(($baseAttributes["health_points"] / 100) * $rowUsersStatistics["max_health_points"]);
								$formulaHealthPoints = min($baseAttributes["health_points"], $rowUsersStatistics["max_health_points"] - $rowUsersStatistics["health_points"]);
								$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET health_points = health_points + :health_points WHERE user_id = :user_id");
								$updateUsersStatistics->execute([":health_points" => $formulaHealthPoints, ":user_id" => $queryUserId]);
								removeItemFromInventory($pdo, $queryUserId, $rowConsumables["item_id"]);
								answerCallbackQuery($queryId, "🫡 (Item) successfully consumed.");
							} else {
								answerCallbackQuery($queryId, "❤️ Your (Health Points) is already full.");
							}
						} catch (Exception $exception) {
							systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
							exit;
						}
					} else {
						try {
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
								$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET hit_rate = hit_rate + :hit_rate, physical_damage = physical_damage + :physical_damage, ranged_damage = ranged_damage + :ranged_damage, magic_damage = magic_damage + :magic_damage, physical_defense = physical_defense + :physical_defense, magic_defense = magic_defense + :magic_defense, critical_damage = critical_damage + :critical_damage, evade = evade + :evade, action_speed = action_speed + :action_speed WHERE user_id = :user_id");
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
									":user_id" => $queryUserId
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
								$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics_bonuses SET experience = experience + :experience, coins = coins + :coins, hit_rate = hit_rate + :hit_rate, physical_damage = physical_damage + :physical_damage, ranged_damage = ranged_damage + :ranged_damage, magic_damage = magic_damage + :magic_damage, physical_defense = physical_defense + :physical_defense, magic_defense = magic_defense + :magic_defense, critical_damage = critical_damage + :critical_damage, evade = evade + :evade, action_speed = action_speed + :action_speed WHERE user_id = :user_id");
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
									":user_id" => $queryUserId
								]);
							}
							removeItemFromInventory($pdo, $queryUserId, $rowConsumables["item_id"]);
							answerCallbackQuery($queryId, "🫡 (Item) successfully consumed.");
						} catch (Exception $exception) {
							systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
							exit;
						}
					}
				}
			} else {
				try {
					answerCallbackQuery($queryId, "🤨 You do not have enough " . $rowItems["icon"] . " (" . $rowItems["name"] . ") to consume.");
				} catch (Exception $exception) {
					systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😅 The (item) was not found. Try again!");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Consumables) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}
