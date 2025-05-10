<?php
include_once("/var/www/html/inventory.php");

if ($text === "🦺 Equipment" && $chatType == "private") {
	// Recupera gli item_id dalla tabella users_equipment
	$selectUsersEquipment = $pdo->prepare("SELECT * FROM users_equipment WHERE user_id = :user_id");
	$selectUsersEquipment->execute([":user_id" => $userId]);
	$rowUsersEquipments = $selectUsersEquipment->fetch(PDO::FETCH_ASSOC);

	// Funzione per recuperare il nome dell'item
	function getItemName($pdo, $itemId) {
		if ($itemId === null) {
			return "<i>NULL</i>";
		}
		$selectItemName = $pdo->prepare("SELECT name FROM items WHERE id = :id");
		$selectItemName->execute([":id" => $itemId]);
		$rowItem = $selectItemName->fetch(PDO::FETCH_ASSOC);
		return $rowItem ? "<b>" . htmlspecialchars($rowItem["name"]) . "</b>" : "<i>NULL</i>";
	}

	// Recupera i nomi degli oggetti equipaggiati
	$weaponName = getItemName($pdo, $rowUsersEquipments["weapon"]);
	$headName = getItemName($pdo, $rowUsersEquipments["head"]);
	$bodyName = getItemName($pdo, $rowUsersEquipments["body"]);
	$handsName = getItemName($pdo, $rowUsersEquipments["hands"]);
	$legsName = getItemName($pdo, $rowUsersEquipments["legs"]);
	$feetName = getItemName($pdo, $rowUsersEquipments["feet"]);

	// Invia il messaggio con i nomi degli oggetti
	sendMessage($chatId, "🦺 [ <code>EQUIPMENT</code> ]\n<u>All the weapons and armors you have equipped will be shown here</u>:\n" .
		"(🗡): Weapon ➔ [ $weaponName ]\n" .
		"(🧢): Head ➔ [ $headName ]\n" .
		"(👔): Body ➔ [ $bodyName ]\n" .
		"(🧤): Hands ➔ [ $handsName ]\n" .
		"(👖): Legs ➔ [ $legsName ]\n" .
		"(🥾): Feet ➔ [ $feetName ]");
}

try {
	if ($queryData == "equipment") {
		preg_match("/\[([^\]]+)\]/", $queryText, $matches);
		$selectItems = $pdo->prepare("SELECT * FROM items WHERE name LIKE :name");
		$selectItems->execute([":name" => trim($matches[1])]);
		$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
		$selectUsersEquipment = $pdo->prepare("SELECT * FROM users_equipment WHERE user_id = :user_id");
		$selectUsersEquipment->execute([":user_id" => $queryUserId]);
		$rowUsersEquipment = $selectUsersEquipment->fetch(PDO::FETCH_ASSOC);
		$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
		$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
		$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
		if ($rowBlacksmith["type"] == "Melee" || $rowBlacksmith["type"] == "Ranged" || $rowBlacksmith["type"] == "Magic") {
			if ($rowUsersEquipment["weapon"] != null) {
				if ($rowUsersEquipment["weapon"] == $rowItems["id"]) {
					$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
					$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
					$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
					##addItemToInventory($pdo, $queryUserId, $rowItems["id"]);
					if ($rowBlacksmith["base_attributes"] !== null) {
						$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					if ($rowBlacksmith["bonus_attributes"] !== null) {
						$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					$updateUsersEquipment = $pdo->prepare("UPDATE users_equipment SET weapon = NULL WHERE user_id = :user_id");
					$updateUsersEquipment->execute([":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "[❌] You removed " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
				} else {
					$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
					$selectBlacksmith->execute([":item_id" => $rowUsersEquipment["weapon"]]);
					$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
					#addItemToInventory($pdo, $queryUserId, $rowUsersEquipment["weapon"]);
					if ($rowBlacksmith["base_attributes"] !== null) {
						$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					if ($rowBlacksmith["bonus_attributes"] !== null) {
						$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					#removeItemFromInventory($pdo, $queryUserId, $rowItems["id"]);
					$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
					$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
					$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
					if ($rowBlacksmith["base_attributes"] !== null) {
						$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
					if ($rowBlacksmith["bonus_attributes"] !== null) {
						$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
					$updateUsersEquipment = $pdo->prepare("UPDATE users_equipment SET weapon = :item_id WHERE user_id = :user_id");
					$updateUsersEquipment->execute([":item_id" => $rowItems["id"], ":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "[✔️] You equiped " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
				}
			} else {
				$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
				$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
				$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
				#removeItemFromInventory($pdo, $queryUserId, $rowItems["id"]);
				if ($rowBlacksmith["base_attributes"] !== null) {
					$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
				if ($rowBlacksmith["bonus_attributes"] !== null) {
					$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
				$updateUsersEquipment = $pdo->prepare("UPDATE users_equipment SET weapon = :item_id WHERE user_id = :user_id");
				$updateUsersEquipment->execute([":item_id" => $rowItems["id"], ":user_id" => $queryUserId]);
				answerCallbackQuery($queryId, "[✔️] You equiped " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
			}
		} elseif ($rowBlacksmith["type"] == "Head") {
			if ($rowUsersEquipment["head"] != null) {
				if ($rowUsersEquipment["head"] == $rowItems["id"]) {
					$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
					$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
					$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
					##addItemToInventory($pdo, $queryUserId, $rowItems["id"]);
					if ($rowBlacksmith["base_attributes"] !== null) {
						$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					if ($rowBlacksmith["bonus_attributes"] !== null) {
						$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					$updateUsersEquipment = $pdo->prepare("UPDATE users_equipment SET head = NULL WHERE user_id = :user_id");
					$updateUsersEquipment->execute([":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "[❌] You removed " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
				} else {
					$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
					$selectBlacksmith->execute([":item_id" => $rowUsersEquipment["head"]]);
					$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
					#addItemToInventory($pdo, $queryUserId, $rowUsersEquipment["head"]);
					if ($rowBlacksmith["base_attributes"] !== null) {
						$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					if ($rowBlacksmith["bonus_attributes"] !== null) {
						$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					#removeItemFromInventory($pdo, $queryUserId, $rowItems["id"]);
					$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
					$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
					$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
					if ($rowBlacksmith["base_attributes"] !== null) {
						$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
					if ($rowBlacksmith["bonus_attributes"] !== null) {
						$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
					$updateUsersEquipment = $pdo->prepare("UPDATE users_equipment SET head = :item_id WHERE user_id = :user_id");
					$updateUsersEquipment->execute([":item_id" => $rowItems["id"], ":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "[✔️] You equiped " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
				}
			} else {
				$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
				$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
				$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
				#removeItemFromInventory($pdo, $queryUserId, $rowItems["id"]);
				if ($rowBlacksmith["base_attributes"] !== null) {
					$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
				if ($rowBlacksmith["bonus_attributes"] !== null) {
					$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
				$updateUsersEquipment = $pdo->prepare("UPDATE users_equipment SET head = :item_id WHERE user_id = :user_id");
				$updateUsersEquipment->execute([":item_id" => $rowItems["id"], ":user_id" => $queryUserId]);
				answerCallbackQuery($queryId, "[✔️] You equiped " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
			}
		} elseif ($rowBlacksmith["type"] == "Body") {
			if ($rowUsersEquipment["body"] != null) {
				if ($rowUsersEquipment["body"] == $rowItems["id"]) {
					$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
					$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
					$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
					##addItemToInventory($pdo, $queryUserId, $rowItems["id"]);
					if ($rowBlacksmith["base_attributes"] !== null) {
						$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					if ($rowBlacksmith["bonus_attributes"] !== null) {
						$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					$updateUsersEquipment = $pdo->prepare("UPDATE users_equipment SET body = NULL WHERE user_id = :user_id");
					$updateUsersEquipment->execute([":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "[❌] You removed " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
				} else {
					$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
					$selectBlacksmith->execute([":item_id" => $rowUsersEquipment["body"]]);
					$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
					#addItemToInventory($pdo, $queryUserId, $rowUsersEquipment["body"]);
					if ($rowBlacksmith["base_attributes"] !== null) {
						$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					if ($rowBlacksmith["bonus_attributes"] !== null) {
						$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					#removeItemFromInventory($pdo, $queryUserId, $rowItems["id"]);
					$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
					$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
					$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
					if ($rowBlacksmith["base_attributes"] !== null) {
						$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
					if ($rowBlacksmith["bonus_attributes"] !== null) {
						$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
					$updateUsersEquipment = $pdo->prepare("UPDATE users_equipment SET body = :item_id WHERE user_id = :user_id");
					$updateUsersEquipment->execute([":item_id" => $rowItems["id"], ":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "[✔️] You equiped " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
				}
			} else {
				$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
				$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
				$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
				#removeItemFromInventory($pdo, $queryUserId, $rowItems["id"]);
				if ($rowBlacksmith["base_attributes"] !== null) {
					$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
				if ($rowBlacksmith["bonus_attributes"] !== null) {
					$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
				$updateUsersEquipment = $pdo->prepare("UPDATE users_equipment SET body = :item_id WHERE user_id = :user_id");
				$updateUsersEquipment->execute([":item_id" => $rowItems["id"], ":user_id" => $queryUserId]);
				answerCallbackQuery($queryId, "[✔️] You equiped " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
			}
		} elseif ($rowBlacksmith["type"] == "Feet") {
			if ($rowUsersEquipment["feet"] != null) {
				if ($rowUsersEquipment["feet"] == $rowItems["id"]) {
					$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
					$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
					$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
					##addItemToInventory($pdo, $queryUserId, $rowItems["id"]);
					if ($rowBlacksmith["base_attributes"] !== null) {
						$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					if ($rowBlacksmith["bonus_attributes"] !== null) {
						$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					$updateUsersEquipment = $pdo->prepare("UPDATE users_equipment SET feet = NULL WHERE user_id = :user_id");
					$updateUsersEquipment->execute([":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "[❌] You removed " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
				} else {
					$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
					$selectBlacksmith->execute([":item_id" => $rowUsersEquipment["feet"]]);
					$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
					#addItemToInventory($pdo, $queryUserId, $rowUsersEquipment["feet"]);
					if ($rowBlacksmith["base_attributes"] !== null) {
						$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					if ($rowBlacksmith["bonus_attributes"] !== null) {
						$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					#removeItemFromInventory($pdo, $queryUserId, $rowItems["id"]);
					$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
					$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
					$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
					if ($rowBlacksmith["base_attributes"] !== null) {
						$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
					if ($rowBlacksmith["bonus_attributes"] !== null) {
						$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
					$updateUsersEquipment = $pdo->prepare("UPDATE users_equipment SET feet = :item_id WHERE user_id = :user_id");
					$updateUsersEquipment->execute([":item_id" => $rowItems["id"], ":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "[✔️] You equiped " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
				}
			} else {
				$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
				$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
				$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
				#removeItemFromInventory($pdo, $queryUserId, $rowItems["id"]);
				if ($rowBlacksmith["base_attributes"] !== null) {
					$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
				if ($rowBlacksmith["bonus_attributes"] !== null) {
					$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
				$updateUsersEquipment = $pdo->prepare("UPDATE users_equipment SET feet = :item_id WHERE user_id = :user_id");
				$updateUsersEquipment->execute([":item_id" => $rowItems["id"], ":user_id" => $queryUserId]);
				answerCallbackQuery($queryId, "[✔️] You equiped " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
			}
		} elseif ($rowBlacksmith["type"] == "Hands") {
			if ($rowUsersEquipment["hands"] != null) {
				if ($rowUsersEquipment["hands"] == $rowItems["id"]) {
					$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
					$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
					$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
					##addItemToInventory($pdo, $queryUserId, $rowItems["id"]);
					if ($rowBlacksmith["base_attributes"] !== null) {
						$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					if ($rowBlacksmith["bonus_attributes"] !== null) {
						$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					$updateUsersEquipment = $pdo->prepare("UPDATE users_equipment SET hands = NULL WHERE user_id = :user_id");
					$updateUsersEquipment->execute([":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "[❌] You removed " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
				} else {
					$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
					$selectBlacksmith->execute([":item_id" => $rowUsersEquipment["hands"]]);
					$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
					#addItemToInventory($pdo, $queryUserId, $rowUsersEquipment["hands"]);
					if ($rowBlacksmith["base_attributes"] !== null) {
						$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					if ($rowBlacksmith["bonus_attributes"] !== null) {
						$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					#removeItemFromInventory($pdo, $queryUserId, $rowItems["id"]);
					$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
					$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
					$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
					if ($rowBlacksmith["base_attributes"] !== null) {
						$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
					if ($rowBlacksmith["bonus_attributes"] !== null) {
						$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
					$updateUsersEquipment = $pdo->prepare("UPDATE users_equipment SET hands = :item_id WHERE user_id = :user_id");
					$updateUsersEquipment->execute([":item_id" => $rowItems["id"], ":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "[✔️] You equiped " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
				}
			} else {
				$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
				$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
				$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
				#removeItemFromInventory($pdo, $queryUserId, $rowItems["id"]);
				if ($rowBlacksmith["base_attributes"] !== null) {
					$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
				if ($rowBlacksmith["bonus_attributes"] !== null) {
					$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
				$updateUsersEquipment = $pdo->prepare("UPDATE users_equipment SET hands = :item_id WHERE user_id = :user_id");
				$updateUsersEquipment->execute([":item_id" => $rowItems["id"], ":user_id" => $queryUserId]);
				answerCallbackQuery($queryId, "[✔️] You equiped " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
			}
		} elseif ($rowBlacksmith["type"] == "Legs") {
			if ($rowUsersEquipment["legs"] != null) {
				if ($rowUsersEquipment["legs"] == $rowItems["id"]) {
					$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
					$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
					$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
					##addItemToInventory($pdo, $queryUserId, $rowItems["id"]);
					if ($rowBlacksmith["base_attributes"] !== null) {
						$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					if ($rowBlacksmith["bonus_attributes"] !== null) {
						$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					$updateUsersEquipment = $pdo->prepare("UPDATE users_equipment SET legs = NULL WHERE user_id = :user_id");
					$updateUsersEquipment->execute([":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "[❌] You removed " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
				} else {
					$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
					$selectBlacksmith->execute([":item_id" => $rowUsersEquipment["legs"]]);
					$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
					#addItemToInventory($pdo, $queryUserId, $rowUsersEquipment["legs"]);
					if ($rowBlacksmith["base_attributes"] !== null) {
						$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					if ($rowBlacksmith["bonus_attributes"] !== null) {
						$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
							":user_id" => $queryUserId
						]);
					}
					#removeItemFromInventory($pdo, $queryUserId, $rowItems["id"]);
					$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
					$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
					$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
					if ($rowBlacksmith["base_attributes"] !== null) {
						$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
					if ($rowBlacksmith["bonus_attributes"] !== null) {
						$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
					$updateUsersEquipment = $pdo->prepare("UPDATE users_equipment SET legs = :item_id WHERE user_id = :user_id");
					$updateUsersEquipment->execute([":item_id" => $rowItems["id"], ":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "[✔️] You equiped " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
				}
			} else {
				$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
				$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
				$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
				#removeItemFromInventory($pdo, $queryUserId, $rowItems["id"]);
				if ($rowBlacksmith["base_attributes"] !== null) {
					$baseAttributes = json_decode($rowBlacksmith["base_attributes"], true);
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
				if ($rowBlacksmith["bonus_attributes"] !== null) {
					$bonusAttributes = json_decode($rowBlacksmith["bonus_attributes"], true);
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
				$updateUsersEquipment = $pdo->prepare("UPDATE users_equipment SET legs = :item_id WHERE user_id = :user_id");
				$updateUsersEquipment->execute([":item_id" => $rowItems["id"], ":user_id" => $queryUserId]);
				answerCallbackQuery($queryId, "[✔️] You equiped " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
			}
		}
	}
} catch (Exception $exception) {
	systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
	exit;
}
