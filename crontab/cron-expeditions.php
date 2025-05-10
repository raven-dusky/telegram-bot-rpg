<?php
require_once("/var/www/html/index.php");
require_once("/var/www/html/inventory.php");

try {
	$selectExpeditions = $pdo->prepare("SELECT * FROM expeditions 
		WHERE expiration_datetime <= NOW()
		AND user_id NOT IN (SELECT user_id FROM system_bans)");
	$selectExpeditions->execute();
} catch (Exception $exception) {
	exit;
}

while ($rowExpeditions = $selectExpeditions->fetch(PDO::FETCH_ASSOC)) {
	try {
		$selectItems = $pdo->prepare("SELECT * FROM items WHERE id LIKE :id");
		$selectItems->execute([":id" => $rowExpeditions["item_id"]]);
		$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
		$selectLoot = $pdo->prepare("SELECT * FROM loot WHERE item_id = :item_id");
		$selectLoot->execute([":item_id" => $rowExpeditions["item_id"]]);
		$rowLoot = $selectLoot->fetch(PDO::FETCH_ASSOC);
		$selectUsersExpeditions = $pdo->prepare("SELECT * FROM users_expeditions WHERE user_id = :user_id");
		$selectUsersExpeditions->execute([":user_id" => $rowExpeditions["user_id"]]);
		$rowUsersExpeditions = $selectUsersExpeditions->fetch(PDO::FETCH_ASSOC);
		$selectUsersPerks = $pdo->prepare("SELECT education_level, luck_level FROM users_perks WHERE user_id = :user_id");
		$selectUsersPerks->execute([":user_id" => $rowExpeditions["user_id"]]);
		$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
	} catch (Exception $exception) {
		exit;
	}
	$experienceRequired = pow($rowUsersExpeditions["level"], 2.5);
	$experienceRequired = $experienceRequired - $rowUsersExpeditions["experience"];
	$formulaDropRate = $rowLoot["drop_rate"] 
		+ ($rowUsersExpeditions["level"] * 0.19)
		+ (log($rowUsersPerks["education_level"] + 1, 10) * 0.7)
		+ ($rowUsersPerks["luck_level"] * 0.05);
	$formulaDropRate = min($formulaDropRate, 100);
	$rewardValue = $rowLoot["reward_value_min"] 
		+ ($rowUsersExpeditions["level"] * 0.3)
		+ (log($rowUsersPerks["education_level"] + 1, 10) * 0.2);
	$rewardValue = max(1, floor($rewardValue));
	$isObtained = mt_rand(1, 100) <= $formulaDropRate;
	$expGained = $isObtained
		? ($rowUsersExpeditions["level"] * 0.8) + (log($rowUsersPerks["education_level"] + 1, 10) * 0.3)
		: ($rowUsersExpeditions["level"] * 0.4) + (log($rowUsersPerks["education_level"] + 1, 10) * 0.2);
	$expGained = max(1, floor($expGained));
	try {
		$updateExpeditions = $pdo->prepare("UPDATE users_expeditions SET experience = experience + :experience WHERE user_id = :user_id");
		$updateExpeditions->execute([
			":experience" => $expGained,
			":user_id" => $rowExpeditions["user_id"]
		]);
		$selectUsersExpeditions = $pdo->prepare("SELECT * FROM users_expeditions WHERE user_id = :user_id");
		$selectUsersExpeditions->execute([":user_id" => $rowExpeditions["user_id"]]);
		$rowUsersExpeditions = $selectUsersExpeditions->fetch(PDO::FETCH_ASSOC);
		if ($rowUsersExpeditions["experience"] >= $experienceRequired) {
			$updateUsersExpeditions = $pdo->prepare("UPDATE users_expeditions SET level = level + 1, experience = 0 WHERE user_id = :user_id");
			$updateUsersExpeditions->execute([":user_id" => $rowExpeditions["user_id"]]);
		}
		if ($isObtained) {
			addItemToInventory($pdo, $rowExpeditions["user_id"], $rowExpeditions["item_id"], $rewardValue);
			sendMessage($rowExpeditions["user_id"], "🫡 You have returned from an expedition and successfully collected: x<b>$rewardValue</b> " . $rowItems["icon"] . " (<i>" . $rowItems["name"] . "</i>), and gained <i>" . number_format($expGained) . "</i> (Experience).");
		} else {
			sendMessage($rowExpeditions["user_id"], "🤔 You have returned from an expedition and successfully collected: (<i>Nothing</i>) and gained <i>" . number_format($expGained) . "</i> (Experience).");
		}
		$deleteExpedition = $pdo->prepare("DELETE FROM expeditions WHERE id = :id");
		$deleteExpedition->execute([":id" => $rowExpeditions["id"]]);
	} catch (Exception $exception) {
		exit;
	}
}
