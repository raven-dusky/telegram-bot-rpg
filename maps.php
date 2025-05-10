<?php
/*
function executeMapCombat($pdo, $userStatistics, $userPerks, $mobStatistics, $shadowCloneLevel = 0) {
	$combatLog = "";
	$userHealthPoints = $userStatistics["health_points"];
	$mobHealthPoints = $mobStatistics["health_points"];
	$turnCount = 0;
	$userStats = ["hits" => 0, "evades" => 0, "criticals" => 0, "damage_dealt" => 0, "damage_blocked" => 0];
	$mobStats = ["hits" => 0, "evades" => 0, "criticals" => 0, "damage_dealt" => 0, "damage_blocked" => 0];
	$shadowCloneStats = ["hits" => 0, "damage_dealt" => 0];
	$shadowCloneDamagePercentage = (13 / 99) * $shadowCloneLevel;
	try {
		while ($userHealthPoints > 0 && $mobHealthPoints > 0 && $turnCount < 100) {
			$userActionSpeed = floor($userStatistics["action_speed"]);
			if (mt_rand() / mt_getrandmax() < ($userStatistics["action_speed"] - $userActionSpeed)) {
				$userActionSpeed++;
			}
			for ($i = 0; $i < $userActionSpeed && $mobHealthPoints > 0; $i++) {
				if ($userStatistics["hit_rate"] > 0 && $mobStatistics["evade"] > 0) {
					$userHit = $userStatistics["hit_rate"] / ($userStatistics["hit_rate"] + $mobStatistics["evade"]) > mt_rand() / mt_getrandmax();
					if ($userHit) {
						$isCritical = $userPerks["luck"] >= rand(1, 125);
						$userDamage = $userStatistics["physical_damage"] * ($isCritical ? (1 + $userStatistics["critical_damage"] / 100) : 1);
						$damageBlocked = min($userDamage, $mobStatistics["physical_defense"]);
						$userDamage = max($userDamage - $mobStatistics["physical_defense"], 0);
						$mobHealthPoints -= $userDamage;
						$userStats["hits"]++;
						$userStats["damage_dealt"] += $userDamage;
						$mobStats["damage_blocked"] += $damageBlocked;
						if ($isCritical) $userStats["criticals"]++;
					} else {
						$mobStats["evades"]++;
					}
				}
			}
			if ($shadowCloneLevel > 0 && $mobHealthPoints > 0) {
				$shadowCloneActionSpeedBase = 1 + (($shadowCloneLevel - 1) * (14 / 99));
				$shadowCloneActionSpeed = floor($shadowCloneActionSpeedBase);
				if (mt_rand() / mt_getrandmax() < ($shadowCloneActionSpeedBase - $shadowCloneActionSpeed)) {
					$shadowCloneActionSpeed++;
				}
				for ($i = 0; $i < $shadowCloneActionSpeed && $mobHealthPoints > 0; $i++) {
					$shadowCloneDamage = max(1, $userStatistics["physical_damage"] * ($shadowCloneDamagePercentage / 100));
					$mobHealthPoints -= $shadowCloneDamage;
					$shadowCloneStats["hits"]++;
					$shadowCloneStats["damage_dealt"] += $shadowCloneDamage;
				}
			}
			if ($mobHealthPoints <= 0) break;
			$mobActionSpeed = floor($mobStatistics["action_speed"]);
			if (mt_rand() / mt_getrandmax() < ($mobStatistics["action_speed"] - $mobActionSpeed)) {
				$mobActionSpeed++;
			}
			for ($i = 0; $i < $mobActionSpeed && $userHealthPoints > 0; $i++) {
				if ($mobStatistics["hit_rate"] > 0 && $userStatistics["evade"] > 0) {
					$mobHit = $mobStatistics["hit_rate"] / ($mobStatistics["hit_rate"] + $userStatistics["evade"]) > mt_rand() / mt_getrandmax();
					if ($mobHit) {
						$isCritical = $mobStatistics["luck"] >= rand(1, 100);
						$mobDamage = $mobStatistics["physical_damage"] * ($isCritical ? (1 + $mobStatistics["critical_damage"] / 100) : 1);
						$damageBlocked = min($mobDamage, $userStatistics["physical_defense"]);
						$mobDamage = max($mobDamage - $userStatistics["physical_defense"], 0);
						$userHealthPoints -= $mobDamage;
						$mobStats["hits"]++;
						$mobStats["damage_dealt"] += $mobDamage;
						$userStats["damage_blocked"] += $damageBlocked;
						if ($isCritical) $mobStats["criticals"]++;
					} else {
						$userStats["evades"]++;
					}
				}
			}
			$turnCount++;
		}
		$userTotalAttacks = $userStats["hits"] + $mobStats["evades"];
		$mobTotalAttacks = $mobStats["hits"] + $userStats["evades"];
		$combatLog .= "[ YOU ] ➔ Hits: {$userStats["hits"]} ({" . round(($userStats["hits"] / max($userTotalAttacks, 1)) * 100) . "%}), Evades: {$mobStats["evades"]}, Crits: {$userStats["criticals"]}, Damage: {$userStats["damage_dealt"]}, Blocked: {$userStats["damage_blocked"]}\n";
		if ($shadowCloneLevel > 0) {
			$combatLog .= "[ SHADOW CLONE ] ➔ Hits: {$shadowCloneStats["hits"]}, Damage: {$shadowCloneStats["damage_dealt"]}\n";
		}
		$combatLog .= "[ MOBs ] ➔ Hits: {$mobStats["hits"]} ({" . round(($mobStats["hits"] / max($mobTotalAttacks, 1)) * 100) . "%}), Evades: {$userStats["evades"]}, Crits: {$mobStats["criticals"]}, Damage: {$mobStats["damage_dealt"]}, Blocked: {$mobStats["damage_blocked"]}\n";
	} catch (Exception $exception) {
		throw $exception;
	}
	return [
		"combat_log" => $combatLog,
		"user_health_points" => max($userHealthPoints, 0),
		"mob_health_points" => max($mobHealthPoints, 0)
	];
}
*/

function executeMapCombat($pdo, $userStatistics, $userPerks, $mobStatistics, $shadowCloneLevel = 0) {
	$combatLog = "";
	$userHealthPoints = $userStatistics["health_points"];
	$mobHealthPoints = $mobStatistics["health_points"];
	$turnCount = 0;
	$userStats = ["hits" => 0, "evades" => 0, "criticals" => 0, "damage_dealt" => 0, "damage_blocked" => 0];
	$mobStats = ["hits" => 0, "evades" => 0, "criticals" => 0, "damage_dealt" => 0, "damage_blocked" => 0];
	$shadowCloneStats = ["hits" => 0, "damage_dealt" => 0];
	$shadowCloneDamagePercentage = (13 / 99) * $shadowCloneLevel;

	try {
		// Determina il tipo di arma del giocatore
		$queryWeapon = $pdo->prepare("SELECT weapon FROM users_equipment WHERE user_id = :user_id");
		$queryWeapon->execute([":user_id" => $userStatistics["user_id"]]);
		$rowWeapon = $queryWeapon->fetch(PDO::FETCH_ASSOC);
		$userWeaponType = "Melee"; // Default se non ha arma equipaggiata

		if (!empty($rowWeapon["weapon"])) {
			$queryWeaponType = $pdo->prepare("SELECT type FROM blacksmith WHERE item_id = :item_id");
			$queryWeaponType->execute([":item_id" => $rowWeapon["weapon"]]);
			$weaponData = $queryWeaponType->fetch(PDO::FETCH_ASSOC);
			if ($weaponData) {
				$userWeaponType = $weaponData["type"];
			}
		}

		// Determina il tipo di attacco del mostro
		$mobAttackType = $mobStatistics["category"] ?? "Melee"; // Default se non specificato

		while ($userHealthPoints > 0 && $mobHealthPoints > 0 && $turnCount < 100) {
			$userActionSpeed = floor($userStatistics["action_speed"]);
			if (mt_rand() / mt_getrandmax() < ($userStatistics["action_speed"] - $userActionSpeed)) {
				$userActionSpeed++;
			}

			for ($i = 0; $i < $userActionSpeed && $mobHealthPoints > 0; $i++) {
				if ($userStatistics["hit_rate"] > 0 && $mobStatistics["evade"] > 0) {
					$userHit = $userStatistics["hit_rate"] / ($userStatistics["hit_rate"] + $mobStatistics["evade"]) > mt_rand() / mt_getrandmax();
					if ($userHit) {
						$isCritical = $userPerks["luck"] >= rand(1, 125);

						// Determina il danno del giocatore in base al tipo di arma
						$userDamage = 0;
						$damageBlocked = 0;
						if ($userWeaponType == "Melee") {
							$userDamage = $userStatistics["physical_damage"] * ($isCritical ? (1 + $userStatistics["critical_damage"] / 100) : 1);
							$damageBlocked = min($userDamage, $mobStatistics["physical_defense"]);
							$userDamage = max($userDamage - $mobStatistics["physical_defense"], 0);
						} elseif ($userWeaponType == "Ranged") {
							$userDamage = $userStatistics["ranged_damage"];
							$damageBlocked = min($userDamage, $mobStatistics["physical_defense"]);
							$userDamage = max($userDamage - $mobStatistics["physical_defense"], 0);
						} elseif ($userWeaponType == "Magic") {
							$userDamage = $userStatistics["magic_damage"];
							$damageBlocked = min($userDamage, $mobStatistics["magic_defense"]);
							$userDamage = max($userDamage - $mobStatistics["magic_defense"], 0);
						}

						$mobHealthPoints -= $userDamage;
						$userStats["hits"]++;
						$userStats["damage_dealt"] += $userDamage;
						$mobStats["damage_blocked"] += $damageBlocked;
						if ($isCritical) $userStats["criticals"]++;
					} else {
						$mobStats["evades"]++;
					}
				}
			}

			if ($mobHealthPoints <= 0) break;

			$mobActionSpeed = floor($mobStatistics["action_speed"]);
			if (mt_rand() / mt_getrandmax() < ($mobStatistics["action_speed"] - $mobActionSpeed)) {
				$mobActionSpeed++;
			}

			for ($i = 0; $i < $mobActionSpeed && $userHealthPoints > 0; $i++) {
				if ($mobStatistics["hit_rate"] > 0 && $userStatistics["evade"] > 0) {
					$mobHit = $mobStatistics["hit_rate"] / ($mobStatistics["hit_rate"] + $userStatistics["evade"]) > mt_rand() / mt_getrandmax();
					if ($mobHit) {
						$isCritical = $mobStatistics["luck"] >= rand(1, 100);

						// Determina il danno del mostro in base alla categoria
						$mobDamage = 0;
						$damageBlocked = 0;
						if ($mobAttackType == "Melee") {
							$mobDamage = $mobStatistics["physical_damage"] * ($isCritical ? (1 + $mobStatistics["critical_damage"] / 100) : 1);
							$damageBlocked = min($mobDamage, $userStatistics["physical_defense"]);
							$mobDamage = max($mobDamage - $userStatistics["physical_defense"], 0);
						} elseif ($mobAttackType == "Ranged") {
							$mobDamage = $mobStatistics["ranged_damage"];
							$damageBlocked = min($mobDamage, $userStatistics["physical_defense"]);
							$mobDamage = max($mobDamage - $userStatistics["physical_defense"], 0);
						} elseif ($mobAttackType == "Magic") {
							$mobDamage = $mobStatistics["magic_damage"];
							$damageBlocked = min($mobDamage, $userStatistics["magic_defense"]);
							$mobDamage = max($mobDamage - $userStatistics["magic_defense"], 0);
						}

						$userHealthPoints -= $mobDamage;
						$mobStats["hits"]++;
						$mobStats["damage_dealt"] += $mobDamage;
						$userStats["damage_blocked"] += $damageBlocked;
						if ($isCritical) $mobStats["criticals"]++;
					} else {
						$userStats["evades"]++;
					}
				}
			}

			$turnCount++;
		}

		// Logica per calcolare le statistiche di combattimento rimane invariata
	} catch (Exception $exception) {
		throw $exception;
	}

	return [
		"combat_log" => $combatLog,
		"user_health_points" => max($userHealthPoints, 0),
		"mob_health_points" => max($mobHealthPoints, 0)
	];
}

function dropItemsDuringCombat($pdo, $userId) {
	$lootString = "";
	$maxDrops = rand(1, 5); // Limite massimo di oggetti per combattimento
	$droppedItems = [];
	$droppedItemIds = []; // Tiene traccia degli ID degli oggetti già droppati

	try {
		// Recupera tutti gli oggetti di tipo Maps dalla tabella loot
		$queryLoot = $pdo->prepare("SELECT * FROM loot WHERE type = 'Maps'");
		$queryLoot->execute();
		$lootItems = $queryLoot->fetchAll(PDO::FETCH_ASSOC);

		// Verifica se ci sono oggetti disponibili
		if (!empty($lootItems)) {
			for ($i = 0; $i < $maxDrops; $i++) {
				// Seleziona un oggetto casuale dalla lista
				$randomLoot = $lootItems[array_rand($lootItems)];
				
				// Salta se l'oggetto è già stato droppato
				if (in_array($randomLoot['item_id'], $droppedItemIds)) {
					continue; // Passa al prossimo ciclo
				}

				// Calcola il drop basandosi sulla probabilità
				$dropRate = $randomLoot['drop_rate'] / 100; // Converte drop_rate in un valore 0-1
				if (mt_rand() / mt_getrandmax() <= $dropRate) {
					$rewardValue = rand($randomLoot['reward_value_min'], $randomLoot['reward_value_max']);

					// Recupera i dettagli dell'oggetto dalla tabella items
					$queryItem = $pdo->prepare("SELECT * FROM items WHERE id = :item_id");
					$queryItem->execute([":item_id" => $randomLoot['item_id']]);
					$itemDetails = $queryItem->fetch(PDO::FETCH_ASSOC);

					if ($itemDetails) {
						if ($randomLoot['item_id'] != 22) {
							// Aggiungi i dettagli dell'oggetto droppato
							$droppedItems[] = $itemDetails['icon'] . " x" . $rewardValue . " (" . $itemDetails['name'] . ")";
							$droppedItemIds[] = $randomLoot['item_id']; // Registra l'ID droppato
							// Aggiungi l'oggetto all'inventario dell'utente
							addItemToInventory($pdo, $userId, $randomLoot['item_id'], $rewardValue);
						} else {
							$rewardValue = mt_rand($randomLoot['reward_value_min'] * 100, $randomLoot['reward_value_max'] * 100) / 100;
							$droppedItems[] = $itemDetails['icon'] . " " . number_format($rewardValue, 2, ".", "") . " (" . $itemDetails['name'] . ")";
							$droppedItemIds[] = $randomLoot['item_id']; // Registra l'ID droppato
							$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET coins = coins + :coins WHERE user_id = :user_id");
							$updateUsersProfiles->execute([
								":coins" => $rewardValue,
								":user_id" => $userId
							]);
						}
					}
				}
			}
		}

		// Crea la stringa finale degli oggetti droppati
		if (!empty($droppedItems)) {
			$lootString = ", " . implode(", ", array_slice($droppedItems, 0, -1)); // Aggiunge la virgola iniziale e unisce tutti tranne l'ultimo
			if (count($droppedItems) > 1) {
				$lootString .= " and " . end($droppedItems); // Aggiunge "and" prima dell'ultimo oggetto
			} else {
				$lootString = ", " . $droppedItems[0]; // Caso di un solo oggetto
			}
		} else {
			$lootString = "";
		}
	} catch (Exception) {
		exit;
	}

	return $lootString;
}

if ($text === "📌 Maps" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section IN ('Battles', 'Battles/Maps') AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersStatistics = $pdo->prepare("SELECT * FROM users_statistics WHERE user_id = :user_id");
			$selectUsersStatistics->execute([":user_id" => $userId]);
			$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
			$selectUsersMaps = $pdo->prepare("SELECT * FROM users_maps WHERE user_id = :user_id");
			$selectUsersMaps->execute([":user_id" => $userId]);
			$rowUsersMaps = $selectUsersMaps->fetch(PDO::FETCH_ASSOC);
			$selectMaps = $pdo->prepare("SELECT * FROM maps WHERE id = :id");
			$selectMaps->execute([":id" => $rowUsersMaps["map_id"]]);
			$rowMaps = $selectMaps->fetch(PDO::FETCH_ASSOC);
			$selectMapsMobs = $pdo->prepare("SELECT * FROM maps_mobs WHERE map_id = :map_id AND map_stage = :map_stage");
			$selectMapsMobs->execute([":map_id" => $rowUsersMaps["map_id"], ":map_stage" => $rowUsersMaps["current_stage"]]);
			$rowMapsMobs = $selectMapsMobs->fetch(PDO::FETCH_ASSOC);
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Battles/Maps' WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			$iconEnergyPoints = ((($rowUsersStatistics["energy_points"] > 93) && ($rowUsersStatistics["max_energy_points"] == 125)) || ($rowUsersStatistics["energy_points"] > 375 && $rowUsersStatistics["max_energy_points"] == 500)) ? "🔋" : "🪫";
			$iconHealthPoints = ($rowUsersStatistics["health_points"] / $rowUsersStatistics["max_health_points"]) * 100;
			$iconHealth = ($iconHealthPoints > 90) ? "❤️" : (($iconHealthPoints > 50) ? "❤️‍🩹" : "💔");
			sendMessage($chatId, "📌 [ <code>" . strtoupper($rowMaps["name"]) . " - Stage " . $rowUsersMaps["current_stage"] . "</code> ]\n" . $rowMaps["description"] . "\n\n" . "➔ <u>" . $rowMapsMobs["name"] . "</u>\n" . "❤️ " . number_format($rowMapsMobs["health_points"]) . " (Health Points), " . "🎯 " . number_format($rowMapsMobs["hit_rate"]) . " (Hit Rate)\n" . "⚔️ " . number_format($rowMapsMobs["physical_damage"]) . " (P-Damage), " . "🏹 " . number_format($rowMapsMobs["ranged_damage"]) . " (R-Damage), " . "🔮 " . number_format($rowMapsMobs["magic_damage"]) . " (M-Damage)\n" . "🛡 " . number_format($rowMapsMobs["physical_defense"]) . " (P-Defense), " . "🛡 " . number_format($rowMapsMobs["magic_defense"]) . " (M-Defense)\n" . "💥 " . number_format($rowMapsMobs["critical_damage"], 2, ".", "") . "% (Critical Damage)\n" . "🥾 " . number_format($rowMapsMobs["evade"]) . " (Evade)\n" . "⚡ " . number_format($rowMapsMobs["action_speed"], 2, ".", "") . " (Action Speed)\n" . "🎲 " . $rowMapsMobs["luck"] . " (Luck)\n\n" . "<i>Prepare yourself for a challenging battle ahead. Choose wisely and fight bravely!</i>", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"⚔️ Fight","callback_data":"maps_fight"},{"text":"🧭 Time Rewind","callback_data":"maps_time_rewind"}],[{"text":"' . $iconEnergyPoints . ' (EP)","callback_data":"maps_energy"},{"text":"' . $iconHealth . ' (HP)","callback_data":"maps_health_points"}],[{"text":"📑 Logs","callback_data":"maps_logs"}]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($queryData == "maps_fight") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Battles/Maps' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$selectUsersCampfire = $pdo->prepare("SELECT 1 FROM users_campfire WHERE status = 1 AND user_id = :user_id");
		$selectUsersCampfire->execute([":user_id" => $queryUserId]);
		if (!$rowUsersCampfire = $selectUsersCampfire->fetch(PDO::FETCH_ASSOC)) {
			try {
				$selectUsersStatistics = $pdo->prepare("SELECT * FROM users_statistics WHERE user_id = :user_id");
				$selectUsersStatistics->execute([":user_id" => $queryUserId]);
				$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
				$selectUsersPerks = $pdo->prepare("SELECT * FROM users_perks WHERE user_id = :user_id");
				$selectUsersPerks->execute([":user_id" => $queryUserId]);
				$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
				$selectUsersProfiles = $pdo->prepare("SELECT * FROM users_profiles WHERE user_id = :user_id");
				$selectUsersProfiles->execute([":user_id" => $queryUserId]);
				$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
			$formulaEnergyPoints = 15 - ($rowUsersPerks["endurance_level"] >= 50 ? 3 : 0) - ($rowUsersProfiles["level"] >= 30 ? 2 : 0);
			$iconEnergyPoints = ((($rowUsersStatistics["energy_points"] > 93) && ($rowUsersStatistics["max_energy_points"] == 125)) || ($rowUsersStatistics["energy_points"] > 375 && $rowUsersStatistics["max_energy_points"] == 500)) ? "🔋" : "🪫";
			$iconHealthPoints = ($rowUsersStatistics["health_points"] / $rowUsersStatistics["max_health_points"]) * 100;
			$iconHealth = ($iconHealthPoints > 90) ? "❤️" : (($iconHealthPoints > 50) ? "❤️‍🩹" : "💔");
			if ($rowUsersStatistics["energy_points"] >= $formulaEnergyPoints) {
				if ($rowUsersStatistics["health_points"] > 0) {
					try {
						$selectUsersStatisticsBonuses = $pdo->prepare("SELECT * FROM users_statistics_bonuses WHERE user_id = :user_id");
						$selectUsersStatisticsBonuses->execute([":user_id" => $queryUserId]);
						$rowUsersStatisticsBonuses = $selectUsersStatisticsBonuses->fetch(PDO::FETCH_ASSOC);
						$selectUsersMaps = $pdo->prepare("SELECT * FROM users_maps WHERE user_id = :user_id");
						$selectUsersMaps->execute([":user_id" => $queryUserId]);
						$rowUsersMaps = $selectUsersMaps->fetch(PDO::FETCH_ASSOC);
						$selectMaps = $pdo->prepare("SELECT * FROM maps WHERE id = :id");
						$selectMaps->execute([":id" => $rowUsersMaps["map_id"]]);
						$rowMaps = $selectMaps->fetch(PDO::FETCH_ASSOC);
						$selectMapsMobs = $pdo->prepare("SELECT * FROM maps_mobs WHERE map_id = :map_id AND map_stage = :map_stage");
						$selectMapsMobs->execute([":map_id" => $rowUsersMaps["map_id"], ":map_stage" => $rowUsersMaps["current_stage"]]);
						$rowMapsMobs = $selectMapsMobs->fetch(PDO::FETCH_ASSOC);
					} catch (Exception $exception) {
						systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
						exit;
					}
					$rowUsersStatistics["hit_rate"] = round($rowUsersStatistics["hit_rate"] * (1 + ($rowUsersStatisticsBonuses["hit_rate"] / 100)));
					$rowUsersStatistics["physical_damage"] = round($rowUsersStatistics["physical_damage"] * (1 + ($rowUsersStatisticsBonuses["physical_damage"] / 100)));
					$rowUsersStatistics["ranged_damage"] = round($rowUsersStatistics["ranged_damage"] * (1 + ($rowUsersStatisticsBonuses["ranged_damage"] / 100)));
					$rowUsersStatistics["magic_damage"] = round($rowUsersStatistics["magic_damage"] * (1 + ($rowUsersStatisticsBonuses["magic_damage"] / 100)));
					$rowUsersStatistics["physical_defense"] = round($rowUsersStatistics["physical_defense"] * (1 + ($rowUsersStatisticsBonuses["physical_defense"] / 100)));
					$rowUsersStatistics["magic_defense"] = round($rowUsersStatistics["magic_defense"] * (1 + ($rowUsersStatisticsBonuses["magic_defense"] / 100)));
					$rowUsersStatistics["critical_damage"] = round($rowUsersStatistics["critical_damage"] * (1 + ($rowUsersStatisticsBonuses["critical_damage"] / 100)));
					$rowUsersStatistics["evade"] = round($rowUsersStatistics["evade"] * (1 + ($rowUsersStatisticsBonuses["evade"] / 100)));
					$rowUsersStatistics["action_speed"] = round($rowUsersStatistics["action_speed"] * (1 + ($rowUsersStatisticsBonuses["action_speed"] / 100)));
					if ($rowUsersMaps["current_stage"] < $rowMaps["total_stages"]) {
						$selectUsersShadowClone = $pdo->prepare("SELECT * FROM users_shadow_clone WHERE status = 1 AND user_id = :user_id");
						$selectUsersShadowClone->execute([":user_id" => $queryUserId]);
						if ($rowUsersShadowClone = $selectUsersShadowClone->fetch(PDO::FETCH_ASSOC)) {
							$combatResult = executeMapCombat($pdo, $rowUsersStatistics, $rowUsersPerks, $rowMapsMobs, $rowUsersShadowClone["level"]);
						} else {
							$combatResult = executeMapCombat($pdo, $rowUsersStatistics, $rowUsersPerks, $rowMapsMobs);
						}
						if ($combatResult["user_health_points"] > 0) {
							try {
								$selectMaps = $pdo->prepare("SELECT * FROM maps WHERE id = :id");
								$selectMaps->execute([":id" => $rowUsersMaps["map_id"]]);
								$rowMaps = $selectMaps->fetch(PDO::FETCH_ASSOC);
								$selectMapsMobs = $pdo->prepare("SELECT * FROM maps_mobs WHERE map_id = :map_id AND map_stage = :map_stage");
								$selectMapsMobs->execute([":map_id" => $rowUsersMaps["map_id"], ":map_stage" => ($rowUsersMaps["current_stage"] + 1)]);
								$rowMapsMobs = $selectMapsMobs->fetch(PDO::FETCH_ASSOC);
								$formulaExperience = round(5 * sqrt($rowUsersMaps["current_stage"]) * (1 + ($rowUsersMaps["map_id"] / 50)) * (1 + ($rowUsersProfiles["level"] / 200)) * (1 + $rowUsersStatisticsBonuses["experience"] / 100) + rand(1, 5));
								$formulaExperience = max(1, $formulaExperience);
							} catch (Exception $exception) {
								systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
								exit;
							}
							$formulaExperience = round(5 * sqrt($rowUsersMaps["current_stage"]) * (1 + ($rowUsersMaps["map_id"] / 50)) * (1 + ($rowUsersProfiles["level"] / 200)) * (1 + $rowUsersStatisticsBonuses["experience"] / 100) + rand(1, 5));
							$formulaExperience = max(1, $formulaExperience);
							$formulaCoins = round(0.01 + (3.5 - 0.01) * (($rowUsersMaps["map_id"] - 1) / (21 - 1)), 4) * (1 + $rowUsersStatisticsBonuses["coins"] / 100);
							$formulaCoins = max(0.01, $formulaCoins);
							try {
								$pdo->beginTransaction();
								$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET health_points = :health_points, energy_points = energy_points - :energy_points WHERE user_id = :user_id");
								$updateUsersStatistics->execute([
									":health_points" => $combatResult["user_health_points"],
									":energy_points" => $formulaEnergyPoints,
									":user_id" => $queryUserId
								]);
								$updateUsersMaps = $pdo->prepare("UPDATE users_maps SET current_stage = current_stage + 1 WHERE user_id = :user_id");
								$updateUsersMaps->execute([":user_id" => $queryUserId]);
								$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE status = 1 AND user_id = :user_id");
								$selectUsersSpecializations->execute([":user_id" => $queryUserId]);
								if ($rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC)) {
									$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET coins = coins + :coins WHERE user_id = :user_id");
									$updateUsersProfiles->execute([
										":coins" => $formulaCoins,
										":user_id" => $queryUserId
									]);
									$updateUsersProfiles = $pdo->prepare("UPDATE users_specializations SET experience = experience + :experience WHERE user_id = :user_id");
									$updateUsersProfiles->execute([":experience" => $formulaExperience, ":user_id" => $queryUserId]);
								} else {
									$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET experience = experience + :experience, coins = coins + :coins WHERE user_id = :user_id");
									$updateUsersProfiles->execute([
										":experience" => $formulaExperience,
										":coins" => $formulaCoins,
										":user_id" => $queryUserId
									]);
								}
								$pdo->commit();
								systemLogs($pdo, $queryUserId, "INFO (MOBs)", $combatResult["combat_log"]);
								$lootString = dropItemsDuringCombat($pdo, $queryUserId);
								if ($lootString) {
									answerCallbackQuery($queryId, "⚔️ You have won the battle, gain ♦️ " . number_format($formulaExperience) . " (EXP), 🪙 " . number_format($formulaCoins, 2, ".", "") . " (Coins)$lootString. Advanced to stage [" . ($rowUsersMaps["current_stage"] + 1) . "].");
								} else {
									answerCallbackQuery($queryId, "⚔️ You have won the battle, gain ♦️ " . number_format($formulaExperience) . " (EXP) and 🪙 " . number_format($formulaCoins, 2, ".", "") . " (Coins). Advanced to stage [" . ($rowUsersMaps["current_stage"] + 1) . "].");
								}
								editMessageText($queryUserId, $queryMessageId, "📌 [ <code>" . strtoupper($rowMaps["name"]) . " - Stage " . ($rowUsersMaps["current_stage"] + 1) . "</code> ]\n" . $rowMaps["description"] . "\n\n" . "➔ <u>" . $rowMapsMobs["name"] . "</u>\n" . "❤️ " . number_format($rowMapsMobs["health_points"]) . " (Health Points), " . "🎯 " . number_format($rowMapsMobs["hit_rate"]) . " (Hit Rate)\n" . "⚔️ " . number_format($rowMapsMobs["physical_damage"]) . " (P-Damage), " . "🏹 " . number_format($rowMapsMobs["ranged_damage"]) . " (R-Damage), " . "🔮 " . number_format($rowMapsMobs["magic_damage"]) . " (M-Damage)\n" . "🛡 " . number_format($rowMapsMobs["physical_defense"]) . " (P-Defense), " . "🛡 " . number_format($rowMapsMobs["magic_defense"]) . " (M-Defense)\n" . "💥 " . number_format($rowMapsMobs["critical_damage"], 2, ".", "") . "% (Critical Damage)\n" . "🥾 " . number_format($rowMapsMobs["evade"]) . " (Evade)\n" . "⚡ " . number_format($rowMapsMobs["action_speed"], 2, ".", "") . " (Action Speed)\n" . "🎲 " . $rowMapsMobs["luck"] . " (Luck)\n\n" . "<i>Prepare yourself for a challenging battle ahead. Choose wisely and fight bravely!</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"⚔️ Fight","callback_data":"maps_fight"},{"text":"🧭 Time Rewind","callback_data":"maps_time_rewind"}],[{"text":"' . $iconEnergyPoints . ' (EP)","callback_data":"maps_energy"},{"text":"' . $iconHealth . ' (HP)","callback_data":"maps_health_points"}],[{"text":"📑 Logs","callback_data":"maps_logs"}]],"resize_keyboard":true}');
							} catch (Exception $exception) {
								$pdo->rollBack();
								systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
								exit;
							}
						} else {
							try {
								$selectMaps = $pdo->prepare("SELECT * FROM maps WHERE id = :id");
								$selectMaps->execute([":id" => $rowUsersMaps["map_id"]]);
								$rowMaps = $selectMaps->fetch(PDO::FETCH_ASSOC);
								$selectMapsMobs = $pdo->prepare("SELECT * FROM maps_mobs WHERE map_id = :map_id AND map_stage = :map_stage");
								$selectMapsMobs->execute([
									":map_id" => $rowUsersMaps["map_id"],
									":map_stage" => $rowUsersMaps["current_stage"]
								]);
								$rowMapsMobs = $selectMapsMobs->fetch(PDO::FETCH_ASSOC);
							} catch (Exception $exception) {
								systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
								exit;
							}
							$formulaExperience = round(5 * sqrt($rowUsersMaps["current_stage"]) * (1 + ($rowUsersMaps["map_id"] / 50)) * (1 + ($rowUsersProfiles["level"] / 200)) * (1 + $rowUsersStatisticsBonuses["experience"] / 100) + rand(1, 5));
							$formulaExperience = round($formulaExperience * 0.75);
							$formulaExperience = max(1, $formulaExperience);
							$formulaCoins = round(0.01 + (3.5 - 0.01) * (($rowUsersMaps["map_id"] - 1) / (21 - 1)), 4) * (1 + $rowUsersStatisticsBonuses["coins"] / 100);
							$formulaCoins = max(0.01, $formulaCoins);
							try {
								$pdo->beginTransaction();
								$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET health_points = :health_points, energy_points = energy_points - :energy_points WHERE user_id = :user_id");
								$updateUsersStatistics->execute([
									":health_points" => $combatResult["user_health_points"],
									":energy_points" => $formulaEnergyPoints,
									":user_id" => $queryUserId
								]);
								$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE status = 1 AND user_id = :user_id");
								$selectUsersSpecializations->execute([":user_id" => $queryUserId]);
								if ($rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC)) {
									$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET coins = coins + :coins WHERE user_id = :user_id");
									$updateUsersProfiles->execute([
										":coins" => $formulaCoins,
										":user_id" => $queryUserId
									]);
									$updateUsersProfiles = $pdo->prepare("UPDATE users_specializations SET experience = experience + :experience WHERE user_id = :user_id");
									$updateUsersProfiles->execute([":experience" => $formulaExperience, ":user_id" => $queryUserId]);
								} else {
									$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET experience = experience + :experience, coins = coins + :coins WHERE user_id = :user_id");
									$updateUsersProfiles->execute([
										":experience" => $formulaExperience,
										":coins" => $formulaCoins,
										":user_id" => $queryUserId
									]);
								}
								$pdo->commit();
								systemLogs($pdo, $queryUserId, "INFO (MOBs)", $combatResult["combat_log"]);
								$lootString = dropItemsDuringCombat($pdo, $queryUserId);
								if ($lootString) {
									answerCallbackQuery($queryId, "💀 You have lost the battle, gain ♦️ " . number_format($formulaExperience) . " (EXP), 🪙 " . number_format($formulaCoins, 2, ".", "") . " (Coins)$lootString.");
								} else {
									answerCallbackQuery($queryId, "💀 You have lost the battle, gain ♦️ " . number_format($formulaExperience) . " (EXP) and 🪙 " . number_format($formulaCoins, 2, ".", "") . " (Coins).");
								}
								editMessageText($queryUserId, $queryMessageId, "📌 [ <code>" . strtoupper($rowMaps["name"]) . " - Stage " . $rowUsersMaps["current_stage"] . "</code> ]\n" . $rowMaps["description"] . "\n\n" . "➔ <u>" . $rowMapsMobs["name"] . "</u>\n" . "❤️ " . number_format($rowMapsMobs["health_points"]) . " (Health Points), " . "🎯 " . number_format($rowMapsMobs["hit_rate"]) . " (Hit Rate)\n" . "⚔️ " . number_format($rowMapsMobs["physical_damage"]) . " (P-Damage), " . "🏹 " . number_format($rowMapsMobs["ranged_damage"]) . " (R-Damage), " . "🔮 " . number_format($rowMapsMobs["magic_damage"]) . " (M-Damage)\n" . "🛡 " . number_format($rowMapsMobs["physical_defense"]) . " (P-Defense), " . "🛡 " . number_format($rowMapsMobs["magic_defense"]) . " (M-Defense)\n" . "💥 " . number_format($rowMapsMobs["critical_damage"], 2, ".", "") . "% (Critical Damage)\n" . "🥾 " . number_format($rowMapsMobs["evade"]) . " (Evade)\n" . "⚡ " . number_format($rowMapsMobs["action_speed"], 2, ".", "") . " (Action Speed)\n" . "🎲 " . $rowMapsMobs["luck"] . " (Luck)\n\n" . "<i>Prepare yourself for a challenging battle ahead. Choose wisely and fight bravely!</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"⚔️ Fight","callback_data":"maps_fight"},{"text":"🧭 Time Rewind","callback_data":"maps_time_rewind"}],[{"text":"' . $iconEnergyPoints . ' (EP)","callback_data":"maps_energy"},{"text":"' . $iconHealth . ' (HP)","callback_data":"maps_health_points"}],[{"text":"📑 Logs","callback_data":"maps_logs"}]],"resize_keyboard":true}');
							} catch (Exception $exception) {
								$pdo->rollBack();
								systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
								exit;
							}
						}
					} else {
						$selectUsersShadowClone = $pdo->prepare("SELECT * FROM users_shadow_clone WHERE status = 1 AND user_id = :user_id");
						$selectUsersShadowClone->execute([":user_id" => $queryUserId]);
						if ($rowUsersShadowClone = $selectUsersShadowClone->fetch(PDO::FETCH_ASSOC)) {
							$combatResult = executeMapCombat($pdo, $rowUsersStatistics, $rowUsersPerks, $rowMapsMobs, $rowUsersShadowClone["level"]);
						} else {
							$combatResult = executeMapCombat($pdo, $rowUsersStatistics, $rowUsersPerks, $rowMapsMobs);
						}
						if ($combatResult["user_health_points"] > 0) {
							try {
								$selectMaps = $pdo->prepare("SELECT * FROM maps WHERE id = :id");
								$selectMaps->execute([":id" => ($rowUsersMaps["map_id"] + 1)]);
							} catch (Exception $exception) {
								systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
								exit;
							}
							if ($rowMaps = $selectMaps->fetch(PDO::FETCH_ASSOC)) {
								try {
									$selectUsersMaps = $pdo->prepare("SELECT * FROM users_maps WHERE user_id = :user_id");
									$selectUsersMaps->execute([":user_id" => $queryUserId]);
									$rowUsersMaps = $selectUsersMaps->fetch(PDO::FETCH_ASSOC);
								} catch (Exception $exception) {
									systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
									exit;
								}
								$formulaExperience = round(5 * sqrt($rowUsersMaps["current_stage"]) * (1 + ($rowUsersMaps["map_id"] / 50)) * (1 + ($rowUsersProfiles["level"] / 200)) * (1 + $rowUsersStatisticsBonuses["experience"] / 100) + rand(1, 5));
								$formulaExperience = max(1, $formulaExperience);
								$formulaCoins = round(0.01 + (3.5 - 0.01) * (($rowUsersMaps["map_id"] - 1) / (21 - 1)), 4) * (1 + $rowUsersStatisticsBonuses["coins"] / 100);
								$formulaCoins = max(0.01, $formulaCoins);
								try {
									$pdo->beginTransaction();
									$updateUsersMaps = $pdo->prepare("UPDATE users_maps SET map_id = map_id + 1, current_stage = 1 WHERE user_id = :user_id");
									$updateUsersMaps->execute([":user_id" => $queryUserId]);
									$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET health_points = :health_points, energy_points = energy_points - :energy_points WHERE user_id = :user_id");
									$updateUsersStatistics->execute([
										":health_points" => $combatResult["user_health_points"],
										":energy_points" => $formulaEnergyPoints,
										":user_id" => $queryUserId
									]);
									$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE status = 1 AND user_id = :user_id");
									$selectUsersSpecializations->execute([":user_id" => $queryUserId]);
									if ($rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC)) {
										$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET coins = coins + :coins WHERE user_id = :user_id");
										$updateUsersProfiles->execute([
											":coins" => $formulaCoins,
											":user_id" => $queryUserId
										]);
										$updateUsersProfiles = $pdo->prepare("UPDATE users_specializations SET experience = experience + :experience WHERE user_id = :user_id");
										$updateUsersProfiles->execute([":experience" => $formulaExperience, ":user_id" => $queryUserId]);
									} else {
										$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET experience = experience + :experience, coins = coins + :coins WHERE user_id = :user_id");
										$updateUsersProfiles->execute([
											":experience" => $formulaExperience,
											":coins" => $formulaCoins,
											":user_id" => $queryUserId
										]);
									}
									$pdo->commit();
									systemLogs($pdo, $queryUserId, "INFO (MOBs)", $combatResult["combat_log"]);
									$selectUsersMaps = $pdo->prepare("SELECT * FROM users_maps WHERE user_id = :user_id");
									$selectUsersMaps->execute(["user_id" => $queryUserId]);
									$rowUsersMaps = $selectUsersMaps->fetch(PDO::FETCH_ASSOC);
									$selectMapsMobs = $pdo->prepare("SELECT * FROM maps_mobs WHERE map_id = :map_id AND map_stage = 1");
									$selectMapsMobs->execute(["map_id" => $rowUsersMaps["map_id"]]);
									$rowMapsMobs = $selectMapsMobs->fetch(PDO::FETCH_ASSOC);
									$lootString = dropItemsDuringCombat($pdo, $queryUserId);
									if ($lootString) {
										answerCallbackQuery($queryId, "⚔️ You have won the battle, gain ♦️ " . number_format($formulaExperience) . " (EXP), 🪙 " . number_format($formulaCoins, 2, ".", "") . " (Coins)$lootString. Advanced to [" . $rowMaps["name"] . "].");
									} else {
										answerCallbackQuery($queryId, "⚔️ You have won the battle, gain ♦️ " . number_format($formulaExperience) . " (EXP) and 🪙 " . number_format($formulaCoins, 2, ".", "") . " (Coins). Advanced to [" . $rowMaps["name"] . "].");
									}
									editMessageText($queryUserId, $queryMessageId, "📌 [ <code>" . strtoupper($rowMaps["name"]) . " - Stage " . $rowUsersMaps["current_stage"] . "</code> ]\n" . $rowMaps["description"] . "\n\n" . "➔ <u>" . $rowMapsMobs["name"] . "</u>\n" . "❤️ " . number_format($rowMapsMobs["health_points"]) . " (Health Points), " . "🎯 " . number_format($rowMapsMobs["hit_rate"]) . " (Hit Rate)\n" . "⚔️ " . number_format($rowMapsMobs["physical_damage"]) . " (P-Damage), " . "🏹 " . number_format($rowMapsMobs["ranged_damage"]) . " (R-Damage), " . "🔮 " . number_format($rowMapsMobs["magic_damage"]) . " (M-Damage)\n" . "🛡 " . number_format($rowMapsMobs["physical_defense"]) . " (P-Defense), " . "🛡 " . number_format($rowMapsMobs["magic_defense"]) . " (M-Defense)\n" . "💥 " . number_format($rowMapsMobs["critical_damage"], 2, ".", "") . "% (Critical Damage)\n" . "🥾 " . number_format($rowMapsMobs["evade"]) . " (Evade)\n" . "⚡ " . number_format($rowMapsMobs["action_speed"], 2, ".", "") . " (Action Speed)\n" . "🎲 " . $rowMapsMobs["luck"] . " (Luck)\n\n" . "<i>Prepare yourself for a challenging battle ahead. Choose wisely and fight bravely!</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"⚔️ Fight","callback_data":"maps_fight"},{"text":"🧭 Time Rewind","callback_data":"maps_time_rewind"}],[{"text":"' . $iconEnergyPoints . ' (EP)","callback_data":"maps_energy"},{"text":"' . $iconHealth . ' (HP)","callback_data":"maps_health_points"}],[{"text":"📑 Logs","callback_data":"maps_logs"}]],"resize_keyboard":true}');
								} catch (Exception $exception) {
									$pdo->rollBack();
									systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
									exit;
								}
							} else {
								try {
									answerCallbackQuery($queryId, "🚧 [ WORK IN PROGRESS ]");
								} catch (Exception $exception) {
									systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
									exit;
								}
							}
						} else {
							try {
								$selectMaps = $pdo->prepare("SELECT * FROM maps WHERE id = :id");
								$selectMaps->execute([":id" => $rowUsersMaps["map_id"]]);
								$rowMaps = $selectMaps->fetch(PDO::FETCH_ASSOC);
								$selectMapsMobs = $pdo->prepare("SELECT * FROM maps_mobs WHERE map_id = :map_id AND map_stage = :map_stage");
								$selectMapsMobs->execute([":map_id" => $rowUsersMaps["map_id"], ":map_stage" => $rowUsersMaps["current_stage"]]);
								$rowMapsMobs = $selectMapsMobs->fetch(PDO::FETCH_ASSOC);
							} catch (Exception $exception) {
								systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
								exit;
							}
							$formulaExperience = round(5 * sqrt($rowUsersMaps["current_stage"]) * (1 + ($rowUsersMaps["map_id"] / 50)) * (1 + ($rowUsersProfiles["level"] / 200)) * (1 + $rowUsersStatisticsBonuses["experience"] / 100) + rand(1, 5));
							$formulaExperience = round($formulaExperience * 0.75);
							$formulaExperience = max(1, $formulaExperience);
							$formulaCoins = round(0.01 + (3.5 - 0.01) * (($rowUsersMaps["map_id"] - 1) / (21 - 1)), 4) * (1 + $rowUsersStatisticsBonuses["coins"] / 100);
							$formulaCoins = max(0.01, $formulaCoins);
							try {
								$pdo->beginTransaction();
								$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET health_points = :health_points, energy_points = energy_points - :energy_points WHERE user_id = :user_id");
								$updateUsersStatistics->execute([
									":health_points" => $combatResult["user_health_points"],
									":energy_points" => $formulaEnergyPoints,
									":user_id" => $queryUserId
								]);
								$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE status = 1 AND user_id = :user_id");
								$selectUsersSpecializations->execute([":user_id" => $queryUserId]);
								if ($rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC)) {
									$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET coins = coins + :coins WHERE user_id = :user_id");
									$updateUsersProfiles->execute([
										":coins" => $formulaCoins,
										":user_id" => $queryUserId
									]);
									$updateUsersProfiles = $pdo->prepare("UPDATE users_specializations SET experience = experience + :experience WHERE user_id = :user_id");
									$updateUsersProfiles->execute([":experience" => $formulaExperience, ":user_id" => $queryUserId]);
								} else {
									$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET experience = experience + :experience, coins = coins + :coins WHERE user_id = :user_id");
									$updateUsersProfiles->execute([
										":experience" => $formulaExperience,
										":coins" => $formulaCoins,
										":user_id" => $queryUserId
									]);
								}
								$pdo->commit();
								systemLogs($pdo, $queryUserId, "INFO (MOBs)", $combatResult["combat_log"]);
								$lootString = dropItemsDuringCombat($pdo, $queryUserId);
								if ($lootString) {
									answerCallbackQuery($queryId, "💀 You have lost the battle, gain ♦️ " . number_format($formulaExperience) . " (EXP), 🪙 " . number_format($formulaCoins, 2, ".", "") . " (Coins)$lootString.");
								} else {
									answerCallbackQuery($queryId, "💀 You have lost the battle, gain ♦️ " . number_format($formulaExperience) . " (EXP) and 🪙 " . number_format($formulaCoins, 2, ".", "") . " (Coins).");
								}
								editMessageText($queryUserId, $queryMessageId, "📌 [ <code>" . strtoupper($rowMaps["name"]) . " - Stage " . $rowUsersMaps["current_stage"] . "</code> ]\n" . $rowMaps["description"] . "\n\n" . "➔ <u>" . $rowMapsMobs["name"] . "</u>\n" . "❤️ " . number_format($rowMapsMobs["health_points"]) . " (Health Points), " . "🎯 " . number_format($rowMapsMobs["hit_rate"]) . " (Hit Rate)\n" . "⚔️ " . number_format($rowMapsMobs["physical_damage"]) . " (P-Damage), " . "🏹 " . number_format($rowMapsMobs["ranged_damage"]) . " (R-Damage), " . "🔮 " . number_format($rowMapsMobs["magic_damage"]) . " (M-Damage)\n" . "🛡 " . number_format($rowMapsMobs["physical_defense"]) . " (P-Defense), " . "🛡 " . number_format($rowMapsMobs["magic_defense"]) . " (M-Defense)\n" . "💥 " . number_format($rowMapsMobs["critical_damage"], 2, ".", "") . "% (Critical Damage)\n" . "🥾 " . number_format($rowMapsMobs["evade"]) . " (Evade)\n" . "⚡ " . number_format($rowMapsMobs["action_speed"], 2, ".", "") . " (Action Speed)\n" . "🎲 " . $rowMapsMobs["luck"] . " (Luck)\n\n" . "<i>Prepare yourself for a challenging battle ahead. Choose wisely and fight bravely!</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"⚔️ Fight","callback_data":"maps_fight"},{"text":"🧭 Time Rewind","callback_data":"maps_time_rewind"}],[{"text":"' . $iconEnergyPoints . ' (EP)","callback_data":"maps_energy"},{"text":"' . $iconHealth . ' (HP)","callback_data":"maps_health_points"}],[{"text":"📑 Logs","callback_data":"maps_logs"}]],"resize_keyboard":true}');
							} catch (Exception $exception) {
								$pdo->rollBack();
								systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
								exit;
							}
						}
					}
				} else {
					try {
						answerCallbackQuery($queryId, "❤️‍🩹 You do not have enough (Health Points) to fight the monster.");
					} catch (Exception $exception) {
						systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
						exit;
					}
				}
			} else {
				try {
					answerCallbackQuery($queryId, "🪫 You do not have enough (Energy Points) to fight the monster.");
				} catch (Exception $exception) {
					systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😴 You cannot perform this action while resting at the (Campfire).");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Maps) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}	
}

try {
	if ($queryData == "maps_time_rewind") {
		try {
			$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Battles/Maps' AND user_id = :user_id");
			$selectUsersUtilities->execute([":user_id" => $queryUserId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
			$selectUsersCampfire = $pdo->prepare("SELECT 1 FROM users_campfire WHERE status = 1 AND user_id = :user_id");
			$selectUsersCampfire->execute([":user_id" => $queryUserId]);
			if (!$rowUsersCampfire = $selectUsersCampfire->fetch(PDO::FETCH_ASSOC)) {
				try {
					$selectUsersStatistics = $pdo->prepare("SELECT * FROM users_statistics WHERE user_id = :user_id");
					$selectUsersStatistics->execute([":user_id" => $queryUserId]);
					$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
					$selectUsersPerks = $pdo->prepare("SELECT * FROM users_perks WHERE user_id = :user_id");
					$selectUsersPerks->execute([":user_id" => $queryUserId]);
					$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
					$selectUsersProfiles = $pdo->prepare("SELECT * FROM users_profiles WHERE user_id = :user_id");
					$selectUsersProfiles->execute([":user_id" => $queryUserId]);
					$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
					$selectUsersMaps = $pdo->prepare("SELECT * FROM users_maps WHERE user_id = :user_id");
					$selectUsersMaps->execute([":user_id" => $queryUserId]);
					$rowUsersMaps = $selectUsersMaps->fetch(PDO::FETCH_ASSOC);
					$selectMaps = $pdo->prepare("SELECT * FROM maps WHERE id = :id");
					$selectMaps->execute([":id" => $rowUsersMaps["map_id"]]);
					$rowMaps = $selectMaps->fetch(PDO::FETCH_ASSOC);
				} catch (Exception $exception) {
					systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
				if ($rowUsersMaps["map_id"] == 1 && $rowUsersMaps["current_stage"] == 1) {
					answerCallbackQuery($queryId, "🥱 You must complete at least one (stage) to use this option.");
					exit;
				} elseif ($rowUsersMaps["current_stage"] > 1 && $rowUsersMaps["current_stage"] <= $rowMaps["total_stages"]) {
					$rowUsersMaps["current_stage"] = $rowUsersMaps["current_stage"] - 1;
				} elseif ($rowUsersMaps["current_stage"] == 1 && $rowUsersMaps["map_id"] > 1 && $rowUsersMaps["map_id"] < 21) {
					$rowUsersMaps["current_stage"] = $rowMaps["total_stages"];
					$rowUsersMaps["map_id"] -= 1;
				} else {
					answerCallbackQuery($queryId, "🚧 [ WORK IN PROGRESS ]");
					exit;
				}
				$formulaEnergyPoints = 15 - ($rowUsersPerks["endurance_level"] >= 50 ? 3 : 0) - ($rowUsersProfiles["level"] >= 30 ? 2 : 0);
				if ($rowUsersStatistics["energy_points"] >= $formulaEnergyPoints) {
					if ($rowUsersStatistics["health_points"] > 0) {
						$totalExperience = $totalCoins = 0;
						$count = 0;
						$totalLoot = [];
						while ($rowUsersStatistics["energy_points"] >= $formulaEnergyPoints) {
							$formulaExperience = round(5 * sqrt($rowUsersMaps["current_stage"]) * (1 + ($rowUsersMaps["map_id"] / 50)) * (1 + ($rowUsersProfiles["level"] / 200)) * (1 + $rowUsersStatisticsBonuses["experience"] / 100) + rand(1, 5));
							$formulaExperience = max(1, $formulaExperience * 0.65);
							$formulaCoins = round(0.01 + (3.5 - 0.01) * (($rowUsersMaps["map_id"] - 1) / (21 - 1)), 4) * (1 + $rowUsersStatisticsBonuses["coins"] / 100);
							$formulaCoins = max(0.01, $formulaCoins * 0.65);
							$totalExperience += $formulaExperience;
							$totalCoins += $formulaCoins;
							try {
								$pdo->beginTransaction();
								$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET energy_points = energy_points - :energy_points WHERE user_id = :user_id");
								$updateUsersStatistics->execute([
									":energy_points" => $formulaEnergyPoints,
									":user_id" => $queryUserId
								]);
								$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE status = 1 AND user_id = :user_id");
								$selectUsersSpecializations->execute([":user_id" => $queryUserId]);
								if ($rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC)) {
									$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET coins = coins + :coins WHERE user_id = :user_id");
									$updateUsersProfiles->execute([
										":coins" => $formulaCoins,
										":user_id" => $queryUserId
									]);
									$updateUsersProfiles = $pdo->prepare("UPDATE users_specializations SET experience = experience + :experience WHERE user_id = :user_id");
									$updateUsersProfiles->execute([":experience" => $formulaExperience, ":user_id" => $queryUserId]);
								} else {
									$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET experience = experience + :experience, coins = coins + :coins WHERE user_id = :user_id");
									$updateUsersProfiles->execute([
										":experience" => $formulaExperience,
										":coins" => $formulaCoins,
										":user_id" => $queryUserId
									]);
								}
								$pdo->commit();
							} catch (Exception $exception) {
								$pdo->rollBack();
								systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
								exit;
							}
							$rowUsersStatistics["energy_points"] -= $formulaEnergyPoints;
							$count++;
						}
						$i = 0;
						while ($i <= $count) {
							$loot = dropItemsDuringCombat($pdo, $userId);
							$i++;
						}
						answerCallbackQuery($queryId, "⚔️ You have gained ♦️ " . number_format($totalExperience) . " (EXP) and 🪙 " . number_format($totalCoins, 2, ".", "") . " (Coins).");
						try {
							$selectUsersStatistics = $pdo->prepare("SELECT * FROM users_statistics WHERE user_id = :user_id");
							$selectUsersStatistics->execute([":user_id" => $queryUserId]);
							$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
							$iconEnergyPoints = ((($rowUsersStatistics["energy_points"] > 93) && ($rowUsersStatistics["max_energy_points"] == 125)) || ($rowUsersStatistics["energy_points"] > 375 && $rowUsersStatistics["max_energy_points"] == 500)) ? "🔋" : "🪫";
							$iconHealthPoints = ($rowUsersStatistics["health_points"] / $rowUsersStatistics["max_health_points"]) * 100;
							$iconHealth = ($iconHealthPoints > 90) ? "❤️" : (($iconHealthPoints > 50) ? "❤️‍🩹" : "💔");
							if ($lootString) {
								answerCallbackQuery($queryId, "⚔️ You have gain ♦️ " . number_format($totalExperience) . " (EXP) and 🪙 " . number_format($totalCoins, 2, ".", "") . " (Coins)$lootString.");
							} else {
								answerCallbackQuery($queryId, "⚔️ You have gain ♦️ " . number_format($totalExperience) . " (EXP) and 🪙 " . number_format($totalCoins, 2, ".", "") . " (Coins).");
							}
							editMessageReplyMarkup($queryUserId, $queryMessageId, '&reply_markup={"inline_keyboard":[[{"text":"⚔️ Fight","callback_data":"maps_fight"},{"text":"🧭 Time Rewind","callback_data":"maps_time_rewind"}],[{"text":"' . $iconEnergyPoints . ' (EP)","callback_data":"maps_energy"},{"text":"' . $iconHealth . ' (HP)","callback_data":"maps_health_points"}],[{"text":"📑 Logs","callback_data":"maps_logs"}]],"resize_keyboard":true}');
						} catch (Exception $exception) {
							exit;
						}
					} else {
						try {
							answerCallbackQuery($queryId, "❤️‍🩹 You do not have enough (Health Points) to fight the monster.");
						} catch (Exception $exception) {
							systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
							exit;
						}
					}
				} else {
					try {
						answerCallbackQuery($queryId, "🪫 You do not have enough (Energy Points) to fight the monster.");
					} catch (Exception $exception) {
						systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
						exit;
					}
				}
			} else {
				try {
					answerCallbackQuery($queryId, "😴 You cannot perform this action while resting at the (Campfire).");
				} catch (Exception $exception) {
					systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			}
		} else {
			try {
				answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Maps) to access those options.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}	
	}
} catch (Exception) {
	exit;
}

try {
	if ($queryData == "maps_energy") {
		try {
			$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Battles/Maps' AND user_id = :user_id");
			$selectUsersUtilities->execute([":user_id" => $queryUserId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
			try {
				$selectUsersStatistics = $pdo->prepare("SELECT * FROM users_statistics WHERE energy_points < max_energy_points AND user_id = :user_id");
				$selectUsersStatistics->execute([":user_id" => $queryUserId]);
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
			if ($rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC)) {
				try {
					$selectUsersInventory = $pdo->prepare("SELECT * FROM users_inventory WHERE item_id = 1 AND quantity >= 1 AND user_id = :user_id");
					$selectUsersInventory->execute([":user_id" => $queryUserId]);
				} catch (Exception $exception) {
					systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
				if ($rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC)) {
					$formulaEnergyPoints = min(75, $rowUsersStatistics["max_energy_points"] - $rowUsersStatistics["energy_points"]);
					try {
						$pdo->beginTransaction();
						$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET energy_points = energy_points + :energy_points WHERE user_id = :user_id");
						$updateUsersStatistics->execute([":energy_points" => $formulaEnergyPoints, ":user_id" => $queryUserId]);
						removeItemFromInventory($pdo, $queryUserId, 1);
						$pdo->commit();
						answerCallbackQuery($queryId, "🫡 Item successfully consumed.");
						$selectUsersStatistics = $pdo->prepare("SELECT * FROM users_statistics WHERE user_id = :user_id");
						$selectUsersStatistics->execute([":user_id" => $queryUserId]);
						$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
						$iconEnergyPoints = ((($rowUsersStatistics["energy_points"] > 93) && ($rowUsersStatistics["max_energy_points"] == 125)) || ($rowUsersStatistics["energy_points"] > 375 && $rowUsersStatistics["max_energy_points"] == 500)) ? "🔋" : "🪫";
						$iconHealthPoints = ($rowUsersStatistics["health_points"] / $rowUsersStatistics["max_health_points"]) * 100;
						$iconHealth = ($iconHealthPoints > 90) ? "❤️" : (($iconHealthPoints > 50) ? "❤️‍🩹" : "💔");
						editMessageReplyMarkup($queryUserId, $queryMessageId, '&reply_markup={"inline_keyboard":[[{"text":"⚔️ Fight","callback_data":"maps_fight"},{"text":"🧭 Time Rewind","callback_data":"maps_time_rewind"}],[{"text":"' . $iconEnergyPoints . ' (EP)","callback_data":"maps_energy"},{"text":"' . $iconHealth . ' (HP)","callback_data":"maps_health_points"}],[{"text":"📑 Logs","callback_data":"maps_logs"}]],"resize_keyboard":true}');
					} catch (Exception $exception) {
						$pdo->rollBack();
						exit;
					}
				} else {
					try {
						answerCallbackQuery($queryId, "🤨 You do not have enough 🍫 (Energy Bar) to consume.");
					} catch (Exception $exception) {
						systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
						exit;
					}
				}
			} else {
				try {
					answerCallbackQuery($queryId, "🔋 Your (Energy Points) is already full.");
				} catch (Exception $exception) {
					systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			}
		} else {
			try {
				answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Maps) to access those options.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	}
} catch (Exception) {
	exit;
}

try {
	if ($queryData == "maps_health_points") {
		try {
			$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Battles/Maps' AND user_id = :user_id");
			$selectUsersUtilities->execute([":user_id" => $queryUserId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
			try {
				$selectUsersStatistics = $pdo->prepare("SELECT * FROM users_statistics WHERE health_points < max_health_points AND user_id = :user_id");
				$selectUsersStatistics->execute([":user_id" => $queryUserId]);
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
			if ($rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC)) {
				try {
					$selectUsersInventory = $pdo->prepare("SELECT * FROM users_inventory WHERE item_id = 20 AND quantity >= 1 AND user_id = :user_id");
					$selectUsersInventory->execute([":user_id" => $queryUserId]);
				} catch (Exception $exception) {
					systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
				if ($rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC)) {
					try {
						$pdo->beginTransaction();
						while ($rowUsersStatistics["health_points"] < $rowUsersStatistics["max_health_points"]) {
							$formulaHealthPoints = min(250, $rowUsersStatistics["max_health_points"] - $rowUsersStatistics["health_points"]);
							$selectUsersInventory = $pdo->prepare("SELECT * FROM users_inventory WHERE item_id = 20 AND quantity >= 1 AND user_id = :user_id");
							$selectUsersInventory->execute([":user_id" => $queryUserId]);
							if ($rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC)) {
								try {
									$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET health_points = health_points + :health_points WHERE user_id = :user_id");
									$updateUsersStatistics->execute([
										":health_points" => $formulaHealthPoints,
										":user_id" => $queryUserId
									]);
									removeItemFromInventory($pdo, $queryUserId, 20);
									$selectUsersStatistics = $pdo->prepare("SELECT * FROM users_statistics WHERE user_id = :user_id");
									$selectUsersStatistics->execute([":user_id" => $queryUserId]);
									$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
								} catch (Exception $exception) {
									systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
									exit;
								}
							} else {
								break;
							}
						}
						$pdo->commit();
						answerCallbackQuery($queryId, "🫡 Item successfully consumed.");
						$selectUsersStatistics = $pdo->prepare("SELECT * FROM users_statistics WHERE user_id = :user_id");
						$selectUsersStatistics->execute([":user_id" => $queryUserId]);
						$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
						$iconEnergyPoints = ((($rowUsersStatistics["energy_points"] > 93) && ($rowUsersStatistics["max_energy_points"] == 125)) || ($rowUsersStatistics["energy_points"] > 375 && $rowUsersStatistics["max_energy_points"] == 500)) ? "🔋" : "🪫";
						$iconHealthPoints = ($rowUsersStatistics["health_points"] / $rowUsersStatistics["max_health_points"]) * 100;
						$iconHealth = ($iconHealthPoints > 90) ? "❤️" : (($iconHealthPoints > 50) ? "❤️‍🩹" : "💔");
						editMessageReplyMarkup($queryUserId, $queryMessageId, '&reply_markup={"inline_keyboard":[[{"text":"⚔️ Fight","callback_data":"maps_fight"},{"text":"🧭 Time Rewind","callback_data":"maps_time_rewind"}],[{"text":"' . $iconEnergyPoints . ' (EP)","callback_data":"maps_energy"},{"text":"' . $iconHealth . ' (HP)","callback_data":"maps_health_points"}],[{"text":"📑 Logs","callback_data":"maps_logs"}]],"resize_keyboard":true}');
					} catch (Exception $exception) {
						$pdo->rollBack();
						exit;
					}
				} else {
					try {
						answerCallbackQuery($queryId, "🤨 You do not have enough (items) to consume.");
					} catch (Exception $exception) {
						systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
						exit;
					}
				}
			} else {
				try {
					answerCallbackQuery($queryId, "❤️ Your (Health Points) is already full.");
				} catch (Exception $exception) {
					systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			}
		} else {
			try {
				answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Maps) to access those options.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	}
} catch (Exception) {
	exit;
}

if ($queryData == "maps_logs") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Battles/Maps' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectSystemLogs = $pdo->prepare("SELECT * FROM system_logs WHERE user_id = :user_id AND level = 'INFO (MOBs)' ORDER BY system_logs.id DESC LIMIT 1");
			$selectSystemLogs->execute([":user_id" => $queryUserId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		if ($rowSystemLogs = $selectSystemLogs->fetch(PDO::FETCH_ASSOC)) {
			try {
				answerCallbackQuery($queryId, $rowSystemLogs["message"]);
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "📑 There are no combat (Logs) available.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Maps) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}
