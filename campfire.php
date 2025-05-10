<?php
if ($text === "⛺ Campfire" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section IN ('Profile', 'Profile/Campfire', 'Profile/Attributes', 'Profile/Inventory', 'Shadow Clone') AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersCampfire = $pdo->prepare("SELECT * FROM users_campfire WHERE user_id = :user_id");
			$selectUsersCampfire->execute([":user_id" => $userId]);
			$rowUsersCampfire = $selectUsersCampfire->fetch(PDO::FETCH_ASSOC);
			$selectUsersProfiles = $pdo->prepare("SELECT level FROM users_profiles WHERE user_id = :user_id");
			$selectUsersProfiles->execute([":user_id" => $userId]);
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
			$selectUsersPerks = $pdo->prepare("SELECT endurance_level FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $userId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$selectUsersStatistics = $pdo->prepare("SELECT max_health_points FROM users_statistics WHERE user_id = :user_id");
			$selectUsersStatistics->execute([":user_id" => $userId]);
			$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
		$formulaExperience = $rowUsersCampfire["level"] * 0.50;
		$formulaCoins = $rowUsersCampfire["level"] * 0.25;
		$formulaDiamonds = round(0.70368 * pow($rowUsersCampfire["level"], 1.25));
		$formulaHealthPoints = $rowUsersCampfire["level"] * 125;
		$formulaRecoveryHealthPoints = round(($rowUsersCampfire["level"] * 0.003 + $rowUsersProfiles["level"] * 0.0015 + $rowUsersPerks["endurance_level"] * 0.0003) * $rowUsersStatistics["max_health_points"] / 100) + 1;
		$formulaRecoveryEnergyPoints = 1;
		$formulaManaPoints = max(0.10, $rowUsersStatistics["max_mana_points"] * (1.67 / 100));
		$restingIcon = $rowUsersCampfire["status"] ? "✔️" : "❌";
		try {
			if ($rowUsersCampfire["level"] < 100) {
				$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Profile/Campfire' WHERE user_id = :user_id");
				$updateUsersUtilities->execute([":user_id" => $userId]);
				sendMessage($chatId, "⛺ [ <code>CAMPFIRE</code> ] (Level <b>" . $rowUsersCampfire["level"] . "</b>)\n\n[ <code>RESTING</code> ] ({$restingIcon})\n🛏️ Resting at the Campfire allows you to recover +<b>" . number_format($formulaRecoveryHealthPoints) . "</b> (Health Points), +<b>" . number_format($formulaManaPoints, 2, ".", "") . "</b> (Mana Points) and +<b>1</b> (Energy Points) every 60 seconds.\n\n<i>Overall bonuses gained through all upgrades</i>:\n▪️ Experience (EXP): +<b>" . number_format($formulaExperience, 2, ".", "") . "%</b>\n▪️ Coins: +<b>" . number_format($formulaCoins, 2, ".", "") . "%</b>\n▪️ Health Points (HP): +<b>" . number_format($formulaHealthPoints) . "</b>\n\nℹ️ To upgrade the campfire to the next level, you need 💎 <b>" . number_format($formulaDiamonds) . "</b> (<i>Diamonds</i>).", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"⛺ (rest):","callback_data":"empty"},{"text":"✔️","callback_data":"campfire_enable"},{"text":"❌","callback_data":"campfire_disable"}],[{"text":"⏏️ Upgrade","callback_data":"campfire_upgrade"}]],"resize_keyboard":true}');
			} else {
				$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Profile/Campfire' WHERE user_id = :user_id");
				$updateUsersUtilities->execute([":user_id" => $userId]);
				sendMessage($chatId, "⛺ [ <code>CAMPFIRE</code> ] (Level <b>" . $rowUsersCampfire["level"] . "</b>)\n\n[ <code>RESTING</code> ] ({$restingIcon})\n🛏️ Resting at the Campfire allows you to recover +<b>" . number_format($formulaRecoveryHealthPoints) . "</b> (Health Points), +<b>" . number_format($formulaManaPoints, 2, ".", "") . "</b> (Mana Points) and +<b>1</b> (Energy Points) every 60 seconds.\n\n<i>Overall bonuses gained through all upgrades</i>:\n▪️ Experience (EXP): +<b>" . number_format($formulaExperience, 2, ".", "") . "%</b>\n▪️ Coins: +<b>" . number_format($formulaCoins, 2, ".", "") . "%</b>\n▪️ Health Points (HP): +<b>" . number_format($formulaHealthPoints) . "</b>", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"⛺ (rest):","callback_data":"empty"},{"text":"✔️","callback_data":"campfire_enable"},{"text":"❌","callback_data":"campfire_disable"}]],"resize_keyboard":true}');
			}
		}  catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Campfire) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($queryData === "campfire_enable") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Campfire' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersCampfire = $pdo->prepare("SELECT * FROM users_campfire WHERE user_id = :user_id");
			$selectUsersCampfire->execute([":user_id" => $queryUserId]);
			$rowUsersCampfire = $selectUsersCampfire->fetch(PDO::FETCH_ASSOC);
			$selectUsersProfiles = $pdo->prepare("SELECT level FROM users_profiles WHERE user_id = :user_id");
			$selectUsersProfiles->execute([":user_id" => $queryUserId]);
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
			$selectUsersPerks = $pdo->prepare("SELECT endurance_level FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $queryUserId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$selectUsersStatistics = $pdo->prepare("SELECT max_health_points FROM users_statistics WHERE user_id = :user_id");
			$selectUsersStatistics->execute([":user_id" => $queryUserId]);
			$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
		$formulaExperience = $rowUsersCampfire["level"] * 0.50;
		$formulaCoins = $rowUsersCampfire["level"] * 0.25;
		$formulaDiamonds = round(0.70368 * pow($rowUsersCampfire["level"], 1.25));
		$formulaHealthPoints = $rowUsersCampfire["level"] * 125;
		$formulaRecoveryHealthPoints = round(($rowUsersCampfire["level"] * 0.003 + $rowUsersProfiles["level"] * 0.0015 + $rowUsersPerks["endurance_level"] * 0.0003) * $rowUsersStatistics["max_health_points"] / 100) + 1;
		$formulaRecoveryEnergyPoints = 1;
		$formulaManaPoints = max(0.10, $rowUsersStatistics["max_mana_points"] * (1.67 / 100));
		if ($rowUsersStatistics["energy_points"] != $rowUsersStatistics["max_energy_points"] || $rowUsersStatistics["health_points"] != $rowUsersStatistics["max_health_points"] || $rowUsersStatistics["mana_points"] != $rowUsersStatistics["max_mana_points"]) {
			try {
				if ($rowUsersCampfire["level"] < 100) {
					$updateUsersShadowClone = $pdo->prepare("UPDATE users_shadow_clone SET status = 0 WHERE user_id = :user_id");
					$updateUsersShadowClone->execute([":user_id" => $queryUserId]);
					$updateUsersCampfire = $pdo->prepare("UPDATE users_campfire SET status = 1 WHERE user_id = :user_id");
					$updateUsersCampfire->execute([":user_id" => $queryUserId]);
					editMessageText($queryUserId, $queryMessageId, "⛺ [ <code>CAMPFIRE</code> ] (Level <b>" . $rowUsersCampfire["level"] . "</b>)\n\n[ <code>RESTING</code> ] (✔️)\n🛏️ Resting at the Campfire allows you to recover +<b>" . number_format($formulaRecoveryHealthPoints) . "</b> (Health Points), +<b>" . number_format($formulaManaPoints, 2, ".", "") . "</b> (Mana Points) and +<b>1</b> (Energy Points) every 60 seconds.\n\n<i>Overall bonuses gained through all upgrades</i>:\n▪️ Experience (EXP): +<b>" . number_format($formulaExperience, 2, ".", "") . "%</b>\n▪️ Coins: +<b>" . number_format($formulaCoins, 2, ".", "") . "%</b>\n▪️ Health Points (HP): +<b>" . number_format($formulaHealthPoints) . "</b>\n\nℹ️ To upgrade the campfire to the next level, you need 💎 <b>" . number_format($formulaDiamonds) . "</b> (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"⛺ (rest):","callback_data":"empty"},{"text":"✔️","callback_data":"campfire_enable"},{"text":"❌","callback_data":"campfire_disable"}],[{"text":"⏏️ Upgrade","callback_data":"campfire_upgrade"}]],"resize_keyboard":true}');
				} else {
					$updateUsersShadowClone = $pdo->prepare("UPDATE users_shadow_clone SET status = 0 WHERE user_id = :user_id");
					$updateUsersShadowClone->execute([":user_id" => $queryUserId]);
					$updateUsersCampfire = $pdo->prepare("UPDATE users_campfire SET status = 1 WHERE user_id = :user_id");
					$updateUsersCampfire->execute([":user_id" => $queryUserId]);
					editMessageText($queryUserId, $queryMessageId, "⛺ [ <code>CAMPFIRE</code> ] (Level <b>" . $rowUsersCampfire["level"] . "</b>)\n\n[ <code>RESTING</code> ] (✔️)\n🛏️ Resting at the Campfire allows you to recover +<b>" . number_format($formulaRecoveryHealthPoints) . "</b> (Health Points), +<b>" . number_format($formulaManaPoints, 2, ".", "") . "</b> (Mana Points) and +<b>1</b> (Energy Points) every 60 seconds.\n\n<i>Overall bonuses gained through all upgrades</i>:\n▪️ Experience (EXP): +<b>" . number_format($formulaExperience, 2, ".", "") . "%</b>\n▪️ Coins: +<b>" . number_format($formulaCoins, 2, ".", "") . "%</b>\n▪️ Health Points (HP): +<b>" . number_format($formulaHealthPoints) . "</b>",false, '&reply_markup={"inline_keyboard":[[{"text":"⛺ (rest):","callback_data":"empty"},{"text":"✔️","callback_data":"campfire_enable"},{"text":"❌","callback_data":"campfire_disable"}]],"resize_keyboard":true}');
				}
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
		} else {
			answerCallbackQuery($queryId, "😴 You don’t need to (rest) right now.");
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Campfire) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}	
}

if ($queryData === "campfire_disable") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Campfire' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersCampfire = $pdo->prepare("SELECT * FROM users_campfire WHERE user_id = :user_id");
			$selectUsersCampfire->execute([":user_id" => $queryUserId]);
			$rowUsersCampfire = $selectUsersCampfire->fetch(PDO::FETCH_ASSOC);
			$selectUsersProfiles = $pdo->prepare("SELECT level FROM users_profiles WHERE user_id = :user_id");
			$selectUsersProfiles->execute([":user_id" => $queryUserId]);
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
			$selectUsersPerks = $pdo->prepare("SELECT endurance_level FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $queryUserId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$selectUsersStatistics = $pdo->prepare("SELECT max_health_points FROM users_statistics WHERE user_id = :user_id");
			$selectUsersStatistics->execute([":user_id" => $queryUserId]);
			$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
		$formulaExperience = $rowUsersCampfire["level"] * 0.50;
		$formulaCoins = $rowUsersCampfire["level"] * 0.25;
		$formulaDiamonds = round(0.70368 * pow($rowUsersCampfire["level"], 1.25));
		$formulaHealthPoints = $rowUsersCampfire["level"] * 125;
		$formulaRecoveryHealthPoints = round(($rowUsersCampfire["level"] * 0.003 + $rowUsersProfiles["level"] * 0.0015 + $rowUsersPerks["endurance_level"] * 0.0003) * $rowUsersStatistics["max_health_points"] / 100) + 1;
		$formulaRecoveryEnergyPoints = 1;
		$formulaManaPoints = max(0.10, $rowUsersStatistics["max_mana_points"] * (1.67 / 100));
		try {
			if ($rowUsersCampfire["level"] < 100) {
				$updateUsersCampfire = $pdo->prepare("UPDATE users_campfire SET status = 0 WHERE user_id = :user_id");
				$updateUsersCampfire->execute([":user_id" => $queryUserId]);
				editMessageText($queryUserId, $queryMessageId, "⛺ [ <code>CAMPFIRE</code> ] (Level <b>" . $rowUsersCampfire["level"] . "</b>)\n\n[ <code>RESTING</code> ] (❌)\n🛏️ Resting at the Campfire allows you to recover +<b>" . number_format($formulaRecoveryHealthPoints) . "</b> (Health Points), +<b>" . number_format($formulaManaPoints, 2, ".", "") . "</b> (Mana Points) and +<b>1</b> (Energy Points) every 60 seconds.\n\n<i>Overall bonuses gained through all upgrades</i>:\n▪️ Experience (EXP): +<b>" . number_format($formulaExperience, 2, ".", "") . "%</b>\n▪️ Coins: +<b>" . number_format($formulaCoins, 2, ".", "") . "%</b>\n▪️ Health Points (HP): +<b>" . number_format($formulaHealthPoints) . "</b>\n\nℹ️ To upgrade the campfire to the next level, you need 💎 <b>" . number_format($formulaDiamonds) . "</b> (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"⛺ (rest):","callback_data":"empty"},{"text":"✔️","callback_data":"campfire_enable"},{"text":"❌","callback_data":"campfire_disable"}],[{"text":"⏏️ Upgrade","callback_data":"campfire_upgrade"}]],"resize_keyboard":true}');
			} else {
				$updateUsersCampfire = $pdo->prepare("UPDATE users_campfire SET status = 0 WHERE user_id = :user_id");
				$updateUsersCampfire->execute([":user_id" => $queryUserId]);
				editMessageText($queryUserId, $queryMessageId, "⛺ [ <code>CAMPFIRE</code> ] (Level <b>" . $rowUsersCampfire["level"] . "</b>)\n\n[ <code>RESTING</code> ] (❌)\n🛏️ Resting at the Campfire allows you to recover +<b>" . number_format($formulaRecoveryHealthPoints) . "</b> (Health Points), +<b>" . number_format($formulaManaPoints, 2, ".", "") . "</b> (Mana Points) and +<b>1</b> (Energy Points) every 60 seconds.\n\n<i>Overall bonuses gained through all upgrades</i>:\n▪️ Experience (EXP): +<b>" . number_format($formulaExperience, 2, ".", "") . "%</b>\n▪️ Coins: +<b>" . number_format($formulaCoins, 2, ".", "") . "%</b>\n▪️ Health Points (HP): +<b>" . number_format($formulaHealthPoints) . "</b>",false, '&reply_markup={"inline_keyboard":[[{"text":"⛺ (rest):","callback_data":"empty"},{"text":"✔️","callback_data":"campfire_enable"},{"text":"❌","callback_data":"campfire_disable"}]],"resize_keyboard":true}');
			}
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
			exit;
		}	
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Campfire) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}	
}

if ($queryData == "campfire_upgrade") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Campfire' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersCampfire = $pdo->prepare("SELECT * FROM users_campfire WHERE user_id = :user_id");
			$selectUsersCampfire->execute([":user_id" => $queryUserId]);
			$rowUsersCampfire = $selectUsersCampfire->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		try {
			if ($rowUsersCampfire["level"] < 100) {
				$formulaDiamonds = round(0.70368 * pow($rowUsersCampfire["level"], 1.25));
				editMessageText($queryUserId, $queryMessageId, "ℹ️ Are you sure you want to upgrade the <i>Campfire</i> to the next level? This action will cost you 💎 <b>" . number_format($formulaDiamonds) . "</b> (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"✔️","callback_data":"campfire_upgrade_confirm"},{"text":"❌","callback_data":"campfire_upgrade_cancel"}]],"resize_keyboard":true}');
			} else {
				try {
					$selectUsersCampfire = $pdo->prepare("SELECT * FROM users_campfire WHERE user_id = :user_id");
					$selectUsersCampfire->execute([":user_id" => $queryUserId]);
					$rowUsersCampfire = $selectUsersCampfire->fetch(PDO::FETCH_ASSOC);
					$selectUsersProfiles = $pdo->prepare("SELECT level FROM users_profiles WHERE user_id = :user_id");
					$selectUsersProfiles->execute([":user_id" => $queryUserId]);
					$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
					$selectUsersPerks = $pdo->prepare("SELECT endurance_level FROM users_perks WHERE user_id = :user_id");
					$selectUsersPerks->execute([":user_id" => $queryUserId]);
					$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
					$selectUsersStatistics = $pdo->prepare("SELECT max_health_points FROM users_statistics WHERE user_id = :user_id");
					$selectUsersStatistics->execute([":user_id" => $queryUserId]);
					$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
				} catch (Exception $exception) {
					systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
				$formulaExperience = $rowUsersCampfire["level"] * 0.50;
				$formulaCoins = $rowUsersCampfire["level"] * 0.25;
				$formulaDiamonds = round(0.70368 * pow($rowUsersCampfire["level"], 1.25));
				$formulaHealthPoints = $rowUsersCampfire["level"] * 125;
				$formulaRecoveryHealthPoints = round(($rowUsersCampfire["level"] * 0.003 + $rowUsersProfiles["level"] * 0.0015 + $rowUsersPerks["endurance_level"] * 0.0003) * $rowUsersStatistics["max_health_points"] / 100) + 1;
				$formulaRecoveryEnergyPoints = 1;
				$formulaManaPoints = max(0.10, $rowUsersStatistics["max_mana_points"] * (1.67 / 100));
				$restingIcon = $rowUsersCampfire["status"] ? "✔️" : "❌";
				try {
					answerCallbackQuery($queryId, "🥳 You have reached the maximum level of the (Campfire).");
					editMessageText($queryUserId, $queryMessageId, "⛺ [ <code>CAMPFIRE</code> ] (Level <b>" . $rowUsersCampfire["level"] . "</b>)\n\n[ <code>RESTING</code> ] ({$restingIcon})\n🛏️ Resting at the Campfire allows you to recover +<b>" . number_format($formulaRecoveryHealthPoints) . "</b> (Health Points), +<b>" . number_format($formulaManaPoints, 2, ".", "") . "</b> (Mana Points) and +<b>1</b> (Energy Points) every 60 seconds.\n\n<i>Overall bonuses gained through all upgrades</i>:\n▪️ Experience (EXP): +<b>" . number_format($formulaExperience, 2, ".", "") . "%</b>\n▪️ Coins: +<b>" . number_format($formulaCoins, 2, ".", "") . "%</b>\n▪️ Health Points (HP): +<b>" . number_format($formulaHealthPoints) . "</b>", false, '&reply_markup={"inline_keyboard":[[{"text":"⛺ (rest):","callback_data":"empty"},{"text":"✔️","callback_data":"campfire_enable"},{"text":"❌","callback_data":"campfire_disable"}]],"resize_keyboard":true}');
				} catch (Exception $exception) {
					systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			}
		}  catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Campfire) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "campfire_upgrade_confirm") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Campfire' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersCampfire = $pdo->prepare("SELECT * FROM users_campfire WHERE user_id = :user_id");
			$selectUsersCampfire->execute([":user_id" => $queryUserId]);
			$rowUsersCampfire = $selectUsersCampfire->fetch(PDO::FETCH_ASSOC);
			$formulaDiamonds = round(0.70368 * pow($rowUsersCampfire["level"], 1.25));
			$selectUsersProfiles = $pdo->prepare("SELECT level FROM users_profiles WHERE diamonds >= :diamonds AND user_id = :user_id");
			$selectUsersProfiles->execute([":diamonds" => $formulaDiamonds, ":user_id" => $queryUserId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		if ($rowUsersCampfire["level"] < 100) {
			if ($rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC)) {
				try {
					$pdo->beginTransaction();
					$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET diamonds = diamonds - :diamonds WHERE user_id = :user_id");
					$updateUsersProfiles->execute([":diamonds" => $formulaDiamonds, ":user_id" => $queryUserId]);
					$updateUsersCampfire = $pdo->prepare("UPDATE users_campfire SET level = level + 1 WHERE user_id = :user_id");
					$updateUsersCampfire->execute([":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET max_health_points = max_health_points + 125 WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":user_id" => $queryUserId]);
					$updateUsersStatisticsBonuses = $pdo->prepare("UPDATE users_statistics_bonuses SET experience = experience + 0.5, coins = coins + 0.25 WHERE user_id = :user_id");
					$updateUsersStatisticsBonuses->execute([":user_id" => $queryUserId]);
					$formulaExperience = ($rowUsersCampfire["level"] + 1) * 0.50;
					$formulaCoins = ($rowUsersCampfire["level"] + 1) * 0.25;
					$formulaDiamonds = round(0.70368 * pow(($rowUsersCampfire["level"] + 1), 1.25));
					$formulaHealthPoints = ($rowUsersCampfire["level"] + 1) * 125;
					$formulaRecoveryHealthPoints = round((($rowUsersCampfire["level"] + 1) * 0.003 + $rowUsersProfiles["level"] * 0.0015 + $rowUsersPerks["endurance_level"] * 0.0003) * ($rowUsersStatistics["max_health_points"] + $formulaHealthPoints) / 100) + 1;
					$formulaRecoveryEnergyPoints = 1;
					$formulaManaPoints = max(0.10, $rowUsersStatistics["max_mana_points"] * (1.67 / 100));
					$restingIcon = $rowUsersCampfire["status"] ? "✔️" : "❌";
					answerCallbackQuery($queryId, "😍 You have successfully upgraded the (Campfire) to the next level.");
					if (($rowUsersCampfire["level"] + 1) < 100) {
						editMessageText($queryUserId, $queryMessageId, "⛺ [ <code>CAMPFIRE</code> ] (Level <b>" . ($rowUsersCampfire["level"] + 1) . "</b>)\n\n[ <code>RESTING</code> ] ({$restingIcon})\n🛏️ Resting at the Campfire allows you to recover +<b>" . number_format($formulaRecoveryHealthPoints) . "</b> (Health Points), +<b>" . number_format($formulaManaPoints, 2, ".", "") . "</b> (Mana Points) and +<b>1</b> (Energy Points) every 60 seconds.\n\n<i>Overall bonuses gained through all upgrades</i>:\n▪️ Experience (EXP): +<b>" . number_format($formulaExperience, 2, ".", "") . "%</b>\n▪️ Coins: +<b>" . number_format($formulaCoins, 2, ".", "") . "%</b>\n▪️ Health Points (HP): +<b>" . number_format($formulaHealthPoints) . "</b>\n\nℹ️ To upgrade the campfire to the next level, you need 💎 <b>".number_format($formulaDiamonds)."</b> (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"⛺ (rest):","callback_data":"empty"},{"text":"✔️","callback_data":"campfire_enable"},{"text":"❌","callback_data":"campfire_disable"}],[{"text":"⏏️ Upgrade","callback_data":"campfire_upgrade"}]],"resize_keyboard":true}');
					} else {
						editMessageText($queryUserId, $queryMessageId, "⛺ [ <code>CAMPFIRE</code> ] (Level <b>" . ($rowUsersCampfire["level"] + 1) . "</b>)\n\n[ <code>RESTING</code> ] ({$restingIcon})\n🛏️ Resting at the Campfire allows you to recover +<b>" . number_format($formulaRecoveryHealthPoints) . "</b> (Health Points), +<b>" . number_format($formulaManaPoints, 2, ".", "") . "</b> (Mana Points) and +<b>1</b> (Energy Points) every 60 seconds.\n\n<i>Overall bonuses gained through all upgrades</i>:\n▪️ Experience (EXP): +<b>" . number_format($formulaExperience, 2, ".", "") . "%</b>\n▪️ Coins: +<b>" . number_format($formulaCoins, 2, ".", "") . "%</b>\n▪️ Health Points (HP): +<b>" . number_format($formulaHealthPoints) . "</b>", false, '&reply_markup={"inline_keyboard":[[{"text":"⛺ (rest):","callback_data":"empty"},{"text":"✔️","callback_data":"campfire_enable"},{"text":"❌","callback_data":"campfire_disable"}]],"resize_keyboard":true}');
					}
					systemLogs($pdo, $queryUserId, "INFO", "Campfire upgraded from level " . $rowUsersCampfire["level"] . " to " . ($rowUsersCampfire["level"] + 1) . ": +0.5% (Experience), +0.25% (Coins), +125 (Health Points) gained. Diamonds spent: " . number_format(round(0.70368 * pow($rowUsersCampfire["level"], 1.25))) . " (Diamonds).");
					$pdo->commit();
				} catch (Exception $exception) {
					$pdo->rollBack();
					systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			} else {
				try {
					$selectUsersCampfire = $pdo->prepare("SELECT * FROM users_campfire WHERE user_id = :user_id");
					$selectUsersCampfire->execute([":user_id" => $queryUserId]);
					$rowUsersCampfire = $selectUsersCampfire->fetch(PDO::FETCH_ASSOC);
					$selectUsersProfiles = $pdo->prepare("SELECT level FROM users_profiles WHERE user_id = :user_id");
					$selectUsersProfiles->execute([":user_id" => $queryUserId]);
					$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
					$selectUsersPerks = $pdo->prepare("SELECT endurance_level FROM users_perks WHERE user_id = :user_id");
					$selectUsersPerks->execute([":user_id" => $queryUserId]);
					$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
					$selectUsersStatistics = $pdo->prepare("SELECT max_health_points FROM users_statistics WHERE user_id = :user_id");
					$selectUsersStatistics->execute([":user_id" => $queryUserId]);
					$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
				} catch (Exception $exception) {
					systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
				$formulaExperience = $rowUsersCampfire["level"] * 0.50;
				$formulaCoins = $rowUsersCampfire["level"] * 0.25;
				$formulaDiamonds = round(0.70368 * pow($rowUsersCampfire["level"], 1.25));
				$formulaHealthPoints = $rowUsersCampfire["level"] * 125;
				$formulaRecoveryHealthPoints = round(($rowUsersCampfire["level"] * 0.003 + $rowUsersProfiles["level"] * 0.0015 + $rowUsersPerks["endurance_level"] * 0.0003) * $rowUsersStatistics["max_health_points"] / 100) + 1;
				$formulaRecoveryEnergyPoints = 1;
				$formulaManaPoints = max(0.10, $rowUsersStatistics["max_mana_points"] * (1.67 / 100));
				$restingIcon = $rowUsersCampfire["status"] ? "✔️" : "❌";
				try {
					answerCallbackQuery($queryId, "😨 You do not have enough 💎 (Diamonds) to upgrade the (Campfire) to the next level.");
					editMessageText($queryUserId, $queryMessageId, "⛺ [ <code>CAMPFIRE</code> ] (Level <b>" . $rowUsersCampfire["level"] . "</b>)\n\n[ <code>RESTING</code> ] ({$restingIcon})\n🛏️ Resting at the Campfire allows you to recover +<b>" . number_format($formulaRecoveryHealthPoints) . "</b> (Health Points), +<b>" . number_format($formulaManaPoints, 2, ".", "") . "</b> (Mana Points) and +<b>1</b> (Energy Points) every 60 seconds.\n\n<i>Overall bonuses gained through all upgrades</i>:\n▪️ Experience (EXP): +<b>" . number_format($formulaExperience, 2, ".", "") . "%</b>\n▪️ Coins: +<b>" . number_format($formulaCoins, 2, ".", "") . "%</b>\n▪️ Health Points (HP): +<b>" . number_format($formulaHealthPoints) . "</b>\n\nℹ️ To upgrade the campfire to the next level, you need 💎 <b>" . number_format($formulaDiamonds) . "</b> (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"⛺ (rest):","callback_data":"empty"},{"text":"✔️","callback_data":"campfire_enable"},{"text":"❌","callback_data":"campfire_disable"}],[{"text":"⏏️ Upgrade","callback_data":"campfire_upgrade"}]],"resize_keyboard":true}');
				} catch (Exception $exception) {
					systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			}
		} else {
			try {
				$selectUsersCampfire = $pdo->prepare("SELECT * FROM users_campfire WHERE user_id = :user_id");
				$selectUsersCampfire->execute([":user_id" => $queryUserId]);
				$rowUsersCampfire = $selectUsersCampfire->fetch(PDO::FETCH_ASSOC);
				$selectUsersProfiles = $pdo->prepare("SELECT level FROM users_profiles WHERE user_id = :user_id");
				$selectUsersProfiles->execute([":user_id" => $queryUserId]);
				$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
				$selectUsersPerks = $pdo->prepare("SELECT endurance_level FROM users_perks WHERE user_id = :user_id");
				$selectUsersPerks->execute([":user_id" => $queryUserId]);
				$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
				$selectUsersStatistics = $pdo->prepare("SELECT max_health_points FROM users_statistics WHERE user_id = :user_id");
				$selectUsersStatistics->execute([":user_id" => $queryUserId]);
				$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
			$formulaExperience = $rowUsersCampfire["level"] * 0.50;
			$formulaCoins = $rowUsersCampfire["level"] * 0.25;
			$formulaDiamonds = round(0.70368 * pow($rowUsersCampfire["level"], 1.25));
			$formulaHealthPoints = $rowUsersCampfire["level"] * 125;
			$formulaRecoveryHealthPoints = round(($rowUsersCampfire["level"] * 0.003 + $rowUsersProfiles["level"] * 0.0015 + $rowUsersPerks["endurance_level"] * 0.0003) * $rowUsersStatistics["max_health_points"] / 100) + 1;
			$formulaRecoveryEnergyPoints = 1;
			$formulaManaPoints = max(0.10, $rowUsersStatistics["max_mana_points"] * (1.67 / 100));
			$restingIcon = $rowUsersCampfire["status"] ? "✔️" : "❌";
			try {
				answerCallbackQuery($queryId, "🥳 You have reached the maximum level of the (Campfire).");
				editMessageText($queryUserId, $queryMessageId, "⛺ [ <code>CAMPFIRE</code> ] (Level <b>" . $rowUsersCampfire["level"] . "</b>)\n\n[ <code>RESTING</code> ] ({$restingIcon})\n🛏️ Resting at the Campfire allows you to recover +<b>" . number_format($formulaRecoveryHealthPoints) . "</b> (Health Points), +<b>" . number_format($formulaManaPoints, 2, ".", "") . "</b> (Mana Points) and +<b>1</b> (Energy Points) every 60 seconds.\n\n<i>Overall bonuses gained through all upgrades</i>:\n▪️ Experience (EXP): +<b>" . number_format($formulaExperience, 2, ".", "") . "%</b>\n▪️ Coins: +<b>" . number_format($formulaCoins, 2, ".", "") . "%</b>\n▪️ Health Points (HP): +<b>" . number_format($formulaHealthPoints) . "</b>", false, '&reply_markup={"inline_keyboard":[[{"text":"⛺ (rest):","callback_data":"empty"},{"text":"✔️","callback_data":"campfire_enable"},{"text":"❌","callback_data":"campfire_disable"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Campfire) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "campfire_upgrade_cancel") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Campfire' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersCampfire = $pdo->prepare("SELECT * FROM users_campfire WHERE user_id = :user_id");
			$selectUsersCampfire->execute([":user_id" => $queryUserId]);
			$rowUsersCampfire = $selectUsersCampfire->fetch(PDO::FETCH_ASSOC);
			$selectUsersProfiles = $pdo->prepare("SELECT level FROM users_profiles WHERE user_id = :user_id");
			$selectUsersProfiles->execute([":user_id" => $queryUserId]);
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
			$selectUsersPerks = $pdo->prepare("SELECT endurance_level FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $queryUserId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$selectUsersStatistics = $pdo->prepare("SELECT max_health_points FROM users_statistics WHERE user_id = :user_id");
			$selectUsersStatistics->execute([":user_id" => $queryUserId]);
			$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		$formulaExperience = $rowUsersCampfire["level"] * 0.50;
		$formulaCoins = $rowUsersCampfire["level"] * 0.25;
		$formulaDiamonds = round(0.70368 * pow($rowUsersCampfire["level"], 1.25));
		$formulaHealthPoints = $rowUsersCampfire["level"] * 125;
		$formulaRecoveryHealthPoints = round(($rowUsersCampfire["level"] * 0.003 + $rowUsersProfiles["level"] * 0.0015 + $rowUsersPerks["endurance_level"] * 0.0003) * $rowUsersStatistics["max_health_points"] / 100) + 1;
		$formulaRecoveryEnergyPoints = 1;
		$formulaManaPoints = max(0.10, $rowUsersStatistics["max_mana_points"] * (1.67 / 100));
		$restingIcon = $rowUsersCampfire["status"] ? "✔️" : "❌";
		try {
			if ($rowUsersCampfire["level"] < 100) {
				editMessageText($queryUserId, $queryMessageId, "⛺ [ <code>CAMPFIRE</code> ] (Level <b>" . $rowUsersCampfire["level"] . "</b>)\n\n[ <code>RESTING</code> ] ({$restingIcon})\n🛏️ Resting at the Campfire allows you to recover +<b>" . number_format($formulaRecoveryHealthPoints) . "</b> (Health Points), +<b>" . number_format($formulaManaPoints, 2, ".", "") . "</b> (Mana Points) and +<b>1</b> (Energy Points) every 60 seconds.\n\n<i>Overall bonuses gained through all upgrades</i>:\n▪️ Experience (EXP): +<b>" . number_format($formulaExperience, 2, ".", "") . "%</b>\n▪️ Coins: +<b>" . number_format($formulaCoins, 2, ".", "") . "%</b>\n▪️ Health Points (HP): +<b>" . number_format($formulaHealthPoints) . "</b>\n\nℹ️ To upgrade the campfire to the next level, you need 💎 <b>" . number_format($formulaDiamonds) . "</b> (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"⛺ (rest):","callback_data":"empty"},{"text":"✔️","callback_data":"campfire_enable"},{"text":"❌","callback_data":"campfire_disable"}],[{"text":"⏏️ Upgrade","callback_data":"campfire_upgrade"}]],"resize_keyboard":true}');
			} else {
				editMessageText($queryUserId, $queryMessageId, "⛺ [ <code>CAMPFIRE</code> ] (Level <b>" . $rowUsersCampfire["level"] . "</b>)\n\n[ <code>RESTING</code> ] ({$restingIcon})\n🛏️ Resting at the Campfire allows you to recover +<b>" . number_format($formulaRecoveryHealthPoints) . "</b> (Health Points), +<b>" . number_format($formulaManaPoints, 2, ".", "") . "</b> (Mana Points) and +<b>1</b> (Energy Points) every 60 seconds.\n\n<i>Overall bonuses gained through all upgrades</i>:\n▪️ Experience (EXP): +<b>" . number_format($formulaExperience, 2, ".", "") . "%</b>\n▪️ Coins: +<b>" . number_format($formulaCoins, 2, ".", "") . "%</b>\n▪️ Health Points (HP): +<b>" . number_format($formulaHealthPoints) . "</b>", false, '&reply_markup={"inline_keyboard":[[{"text":"⛺ (rest):","callback_data":"empty"},{"text":"✔️","callback_data":"campfire_enable"},{"text":"❌","callback_data":"campfire_disable"}]],"resize_keyboard":true}');
			}
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Campfire) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}
