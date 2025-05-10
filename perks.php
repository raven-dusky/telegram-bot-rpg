<?php
if ($text === "🔺 Perks" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section IN ('Main Menu', 'Dark Wanderer', 'Expeditions') AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersPerks = $pdo->prepare("SELECT * FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $userId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
		try {
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Perks', result = NULL WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			sendMessage($chatId, "🔺 [ <code>PERKS</code> ]\nPerks are special abilities can provide a variety of benefits, such as increased ❤️ (<i>Health Points</i>), increase your ⚔️ (<i>Damage</i>) or boost your 🛡 (<i>Defense</i>).", false, false, false, '&reply_markup={"keyboard":[["🥊 Strength", "🧠 Intelligence"],["🛡 Endurance"],["📖 Education", "🎲 Luck"],["🌀 Specializations"],["🔙 Go Back"]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text === "🥊 Strength" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersPerks = $pdo->prepare("SELECT strength_level, strength_experience FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $userId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$formulaExperience = round(pow($rowUsersPerks["strength_level"], 1.25));
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET result = NULL WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			sendMessage($chatId, "🥊 [ <code>STRENGTH</code> ] (Level <b>" . $rowUsersPerks["strength_level"] . "</b>)\nEnhances your ability to deal damage, making it a crucial attribute for both melee and ranged combat.\n\nℹ️ The experience required for the next level is " . number_format($rowUsersPerks["strength_experience"]) . "/<b>" . number_format($formulaExperience) . "</b> [<b>" . number_format(($rowUsersPerks["strength_experience"] / $formulaExperience) * 100, 2, ".", "") . "%</b>] (EXP).", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"1% (Coins)","callback_data":"perks_strength_one"},{"text":"5% (Coins)","callback_data":"perks_strength_five"},{"text":"25% (Coins)","callback_data":"perks_strength_twentyfive"}],[{"text":"MAX (Coins)","callback_data":"perks_strength_max"}]],"resize_keyboard":true}');
		}  catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if (strpos($queryData, "perks_strength") === 0 && !in_array($queryData, ["perks_strength_confirm", "perks_strength_cancel"])) {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$percentageString = str_replace("perks_strength_", "", $queryData);
		$percentageMap = ["one" => 1, "five" => 5, "twentyfive" => 25, "max" => 100];
		$percentage = isset($percentageMap[$percentageString]) ? $percentageMap[$percentageString] : (int)$percentageString;
		try {
			$selectUsersPerks = $pdo->prepare("SELECT strength_level FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $queryUserId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$selectUsersProfiles = $pdo->prepare("SELECT coins FROM users_profiles WHERE user_id = :user_id");
			$selectUsersProfiles->execute([":user_id" => $queryUserId]);
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		if ($rowUsersPerks["strength_level"] < 999) {
			try {
				for ($requiredExperience = 0; $rowUsersPerks["strength_level"] < 999; $rowUsersPerks["strength_level"]++) {
					$nextLevelExperience = round(pow($rowUsersPerks["strength_level"], 1.25));
					$requiredExperience += max(0, $nextLevelExperience - $rowUsersPerks["strength_experience"]);
					$rowUsersPerks["strength_experience"] = max(0, $rowUsersPerks["strength_experience"] - $nextLevelExperience);
				}
				$formulaCoins = floor(($percentage / 100) * $rowUsersProfiles["coins"] * 10) / 10;
				$formulaCoins = max(0.10, min($formulaCoins, floor(($requiredExperience * 0.10) * 10) / 10));
				if ($rowUsersProfiles["coins"] < $formulaCoins) {
					answerCallbackQuery($queryId, "🤨 You do not have enough (Coins).");
				} else {
					$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET result = :result WHERE user_id = :user_id");
					$updateUsersUtilities->execute([":result" => $percentage, ":user_id" => $queryUserId]);
					editMessageText($queryUserId, $queryMessageId, "ℹ️ Are you sure you want to spend <b>" . $percentage . "%</b> (<i>" . number_format($formulaCoins, 2, ".", "") . " Coins</i>) to upgrade <i>Strength</i>?", false, '&reply_markup={"inline_keyboard":[[{"text":"✔️","callback_data":"perks_strength_confirm"},{"text":"❌","callback_data":"perks_strength_cancel"}]],"resize_keyboard":true}');
				}
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "🥳 Congratulations! You have reached the maximum (Strength) level.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData === "perks_strength_confirm") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT result FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersPerks = $pdo->prepare("SELECT strength_level, strength_experience FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $queryUserId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$selectUsersProfiles = $pdo->prepare("SELECT coins FROM users_profiles WHERE user_id = :user_id");
			$selectUsersProfiles->execute([":user_id" => $queryUserId]);
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		$requiredExperience = 0;
		$simulatedLevel = $rowUsersPerks["strength_level"];
		$simulatedExperience = $rowUsersPerks["strength_experience"];
		for (; $simulatedLevel < 999; $simulatedLevel++) {
			$nextLevelExperience = round(pow($simulatedLevel, 1.25));
			$requiredExperience += max(0, $nextLevelExperience - $simulatedExperience);
			$simulatedExperience = max(0, $simulatedExperience - $nextLevelExperience);
		}
		$formulaCoins = floor(($rowUsersUtilities["result"] / 100) * $rowUsersProfiles["coins"] * 10) / 10;
		$formulaCoins = max(0.10, min($formulaCoins, floor(($requiredExperience * 0.10) * 10) / 10));
		$formulaConversion = floor($formulaCoins / 0.10);
		if ($rowUsersProfiles["coins"] < $formulaCoins) {
			try {
				answerCallbackQuery($queryId, "🤨 You do not have enough (Coins).");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			$rowUsersPerks["strength_experience"] += $formulaConversion;
			for ($levelsGained = 0; $rowUsersPerks["strength_experience"] >= round(pow($rowUsersPerks["strength_level"], 1.25)) && $rowUsersPerks["strength_level"] < 999; $levelsGained++) {
				$nextLevelExperience = round(pow($rowUsersPerks["strength_level"], 1.25));
				$rowUsersPerks["strength_experience"] -= $nextLevelExperience;
				$rowUsersPerks["strength_level"]++;
				$formulaPhysicalDamage += max(1, round(0.3 * pow($rowUsersPerks["strength_level"], 1.005)));
				$formulaRangedDamage += max(1, round(0.3 * pow($rowUsersPerks["strength_level"], 1.005)));
				$formulaCriticalDamage += max(0.01, round((0.0015 * pow($rowUsersPerks["strength_level"], 1.002)), 2));
			}
			try {
				$pdo->beginTransaction();
				$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET coins = coins - :coins WHERE user_id = :user_id");
				$updateUsersProfiles->execute([":coins" => $formulaCoins, ":user_id" => $queryUserId]);
				$updateUsersPerks = $pdo->prepare("UPDATE users_perks SET strength_experience = :strength_experience WHERE user_id = :user_id");
				$updateUsersPerks->execute([":strength_experience" => $rowUsersPerks["strength_experience"], ":user_id" => $queryUserId]);
				if ($levelsGained > 0) {
					$updateUsersPerks = $pdo->prepare("UPDATE users_perks SET strength_level = strength_level + :strength_level WHERE user_id = :user_id");
					$updateUsersPerks->execute([":strength_level" => $levelsGained, ":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET physical_damage = physical_damage + :physical_damage, ranged_damage = ranged_damage + :ranged_damage, critical_damage = critical_damage + :critical_damage WHERE user_id = :user_id");
					$updateUsersStatistics->execute([
						":physical_damage" => $formulaPhysicalDamage,
						":ranged_damage" => $formulaRangedDamage,
						":critical_damage" => $formulaCriticalDamage,
						":user_id" => $queryUserId
					]);
				}
				$pdo->commit();
				if ($levelsGained > 0) {
					systemLogs($pdo, $queryUserId, "INFO", "(Strength) perk improved: +" . number_format($formulaConversion) . " (EXP), +" . number_format($formulaPhysicalDamage) . " (Physical Damage), +" . number_format($formulaRangedDamage) . " (Ranged Damage), +" . number_format($formulaCriticalDamage, 2, ".", "") . "% (Critical Damage), and spent " . number_format($formulaCoins, 2, ".", "") . " (Coins).");
					answerCallbackQuery($queryId, "🥊 You improve your (Strength) perk and gain +" . number_format($formulaConversion) . " (EXP), +" . number_format($formulaPhysicalDamage) . " (Physical Damage), +" . number_format($formulaRangedDamage) . " (Ranged Damage) and +" . number_format($formulaCriticalDamage, 2, ".", "") . "% (Critical Damage).");
				} else {
					systemLogs($pdo, $queryUserId, "INFO", "(Strength) perk improved: +" . number_format($formulaConversion) . " (EXP), and spent " . number_format($formulaCoins, 2, ".", "") . " (Coins).");
					answerCallbackQuery($queryId, "🥊 You improve your (Strength) perk and gain +" . number_format($formulaConversion) . " (EXP).");
				}
				$selectUsersPerks = $pdo->prepare("SELECT strength_level, strength_experience FROM users_perks WHERE user_id = :user_id");
				$selectUsersPerks->execute([":user_id" => $userId]);
				$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
				$formulaExperience = round(pow($rowUsersPerks["strength_level"], 1.25));
				editMessageText($queryUserId, $queryMessageId, "🥊 [ <code>STRENGTH</code> ] (Level <b>" . $rowUsersPerks["strength_level"] . "</b>)\nEnhances your ability to deal damage, making it a crucial attribute for both melee and ranged combat.\n\nℹ️ The experience required for the next level is " . number_format($rowUsersPerks["strength_experience"]) . "/<b>" . number_format($formulaExperience) . "</b> [<b>" . number_format(($rowUsersPerks["strength_experience"] / $formulaExperience) * 100, 2, ".", "") . "%</b>] (EXP).", false, '&reply_markup={"inline_keyboard":[[{"text":"1% (Coins)","callback_data":"perks_strength_one"},{"text":"5% (Coins)","callback_data":"perks_strength_five"},{"text":"25% (Coins)","callback_data":"perks_strength_twentyfive"}],[{"text":"MAX (Coins)","callback_data":"perks_strength_max"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData === "perks_strength_cancel") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersPerks = $pdo->prepare("SELECT strength_level, strength_experience FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $queryUserId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$formulaExperience = round(pow($rowUsersPerks["strength_level"], 1.25));
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET result = NULL WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $queryUserId]);
			editMessageText($queryUserId, $queryMessageId, "🥊 [ <code>STRENGTH</code> ] (Level <b>" . $rowUsersPerks["strength_level"] . "</b>)\nEnhances your ability to deal damage, making it a crucial attribute for both melee and ranged combat.\n\nℹ️ The experience required for the next level is " . number_format($rowUsersPerks["strength_experience"]) . "/<b>" . number_format($formulaExperience) . "</b> [<b>" . number_format(($rowUsersPerks["strength_experience"] / $formulaExperience) * 100, 2, ".", "") . "%</b>] (EXP).", false, '&reply_markup={"inline_keyboard":[[{"text":"1% (Coins)","callback_data":"perks_strength_one"},{"text":"5% (Coins)","callback_data":"perks_strength_five"},{"text":"25% (Coins)","callback_data":"perks_strength_twentyfive"}],[{"text":"MAX (Coins)","callback_data":"perks_strength_max"}]],"resize_keyboard":true}');
		}  catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($text === "🧠 Intelligence" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersPerks = $pdo->prepare("SELECT intelligence_level, intelligence_experience FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $userId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$formulaExperience = round(pow($rowUsersPerks["intelligence_level"], 1.25));
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET result = NULL WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			sendMessage($chatId, "🧠 [ <code>INTELLIGENCE</code> ] (Level <b>" . $rowUsersPerks["intelligence_level"] . "</b>)\nBoosts your magical attack power, making it vital for maximizing the effectiveness of spells and magical abilities.\n\nℹ️ The experience required for the next level is " . number_format($rowUsersPerks["intelligence_experience"]) . "/<b>" . number_format($formulaExperience) . "</b> [<b>" . number_format(($rowUsersPerks["intelligence_experience"] / $formulaExperience) * 100, 2, ".", "") . "%</b>] (EXP).", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"1% (Coins)","callback_data":"perks_intelligence_one"},{"text":"5% (Coins)","callback_data":"perks_intelligence_five"},{"text":"25% (Coins)","callback_data":"perks_intelligence_twentyfive"}],[{"text":"MAX (Coins)","callback_data":"perks_intelligence_max"}]],"resize_keyboard":true}');
		}  catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if (strpos($queryData, "perks_intelligence") === 0 && !in_array($queryData, ["perks_intelligence_confirm", "perks_intelligence_cancel"])) {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$percentageString = str_replace("perks_intelligence_", "", $queryData);
		$percentageMap = ["one" => 1, "five" => 5, "twentyfive" => 25, "max" => 100];
		$percentage = isset($percentageMap[$percentageString]) ? $percentageMap[$percentageString] : (int)$percentageString;
		try {
			$selectUsersPerks = $pdo->prepare("SELECT intelligence_level FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $queryUserId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$selectUsersProfiles = $pdo->prepare("SELECT coins FROM users_profiles WHERE user_id = :user_id");
			$selectUsersProfiles->execute([":user_id" => $queryUserId]);
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		if ($rowUsersPerks["intelligence_level"] < 999) {
			try {
				for ($requiredExperience = 0; $rowUsersPerks["intelligence_level"] < 999; $rowUsersPerks["intelligence_level"]++) {
					$nextLevelExperience = round(pow($rowUsersPerks["intelligence_level"], 1.25));
					$requiredExperience += max(0, $nextLevelExperience - $rowUsersPerks["intelligence_experience"]);
					$rowUsersPerks["intelligence_experience"] = max(0, $rowUsersPerks["intelligence_experience"] - $nextLevelExperience);
				}
				$formulaCoins = floor(($percentage / 100) * $rowUsersProfiles["coins"] * 10) / 10;
				$formulaCoins = max(0.10, min($formulaCoins, floor(($requiredExperience * 0.10) * 10) / 10));
				if ($rowUsersProfiles["coins"] < $formulaCoins) {
					answerCallbackQuery($queryId, "🤨 You do not have enough (Coins).");
				} else {
					$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET result = :result WHERE user_id = :user_id");
					$updateUsersUtilities->execute([":result" => $percentage, ":user_id" => $queryUserId]);
					editMessageText($queryUserId, $queryMessageId, "ℹ️ Are you sure you want to spend <b>" . $percentage . "%</b> (<i>" . number_format($formulaCoins, 2, ".", "") . " Coins</i>) to upgrade <i>Intelligence</i>?", false, '&reply_markup={"inline_keyboard":[[{"text":"✔️","callback_data":"perks_intelligence_confirm"},{"text":"❌","callback_data":"perks_intelligence_cancel"}]],"resize_keyboard":true}');
				}
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "🥳 Congratulations! You have reached the maximum (Intelligence) level.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData === "perks_intelligence_confirm") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT result FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersPerks = $pdo->prepare("SELECT intelligence_level, intelligence_experience FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $queryUserId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$selectUsersProfiles = $pdo->prepare("SELECT coins FROM users_profiles WHERE user_id = :user_id");
			$selectUsersProfiles->execute([":user_id" => $queryUserId]);
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		$requiredExperience = 0;
		$simulatedLevel = $rowUsersPerks["intelligence_level"];
		$simulatedExperience = $rowUsersPerks["intelligence_experience"];
		for (; $simulatedLevel < 999; $simulatedLevel++) {
			$nextLevelExperience = round(pow($simulatedLevel, 1.25));
			$requiredExperience += max(0, $nextLevelExperience - $simulatedExperience);
			$simulatedExperience = max(0, $simulatedExperience - $nextLevelExperience);
		}
		$formulaCoins = floor(($rowUsersUtilities["result"] / 100) * $rowUsersProfiles["coins"] * 10) / 10;
		$formulaCoins = max(0.10, min($formulaCoins, floor(($requiredExperience * 0.10) * 10) / 10));
		$formulaConversion = floor($formulaCoins / 0.10);
		if ($rowUsersProfiles["coins"] < $formulaCoins) {
			try {
				answerCallbackQuery($queryId, "🤨 You do not have enough (Coins).");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			$rowUsersPerks["intelligence_experience"] += $formulaConversion;
			for ($levelsGained = 0; $rowUsersPerks["intelligence_experience"] >= round(pow($rowUsersPerks["intelligence_level"], 1.25)) && $rowUsersPerks["intelligence_level"] < 999; $levelsGained++) {
				$nextLevelExperience = round(pow($rowUsersPerks["intelligence_level"], 1.25));
				$rowUsersPerks["intelligence_experience"] -= $nextLevelExperience;
				$rowUsersPerks["intelligence_level"]++;
				$formulaMagicDamage += max(1, round(0.3 * pow($rowUsersPerks["intelligence_level"], 1.005)));
				$formulaManaPoints = max(1, round(2500 / 999));
			}
			try {
				$pdo->beginTransaction();
				$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET coins = coins - :coins WHERE user_id = :user_id");
				$updateUsersProfiles->execute([":coins" => $formulaCoins, ":user_id" => $queryUserId]);
				$updateUsersPerks = $pdo->prepare("UPDATE users_perks SET intelligence_experience = :intelligence_experience WHERE user_id = :user_id");
				$updateUsersPerks->execute([":intelligence_experience" => $rowUsersPerks["intelligence_experience"], ":user_id" => $queryUserId]);
				if ($levelsGained > 0) {
					$updateUsersPerks = $pdo->prepare("UPDATE users_perks SET intelligence_level = intelligence_level + :intelligence_level WHERE user_id = :user_id");
					$updateUsersPerks->execute([":intelligence_level" => $levelsGained, ":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET max_mana_points = max_mana_points + :max_mana_points, magic_damage = magic_damage + :magic_damage WHERE user_id = :user_id");
					$updateUsersStatistics->execute([
						":max_mana_points" => $formulaManaPoints,
						":magic_damage" => $formulaMagicDamage,
						":user_id" => $queryUserId
					]);
				}
				$pdo->commit();
				if ($levelsGained > 0) {
					systemLogs($pdo, $queryUserId, "INFO", "(Intelligence) perk improved: +" . number_format($formulaConversion) . " (EXP), +" . number_format($formulaMagicDamage) . " (Magic Damage), and spent " . number_format($formulaCoins, 2, ".", "") . " (Coins).");
					answerCallbackQuery($queryId, "🧠 You improve your (Intelligence) perk and gain +" . number_format($formulaConversion) . " (EXP), +" . number_format($formulaMagicDamage) . " (Magic Damage)");
				} else {
					systemLogs($pdo, $queryUserId, "INFO", "(Intelligence) perk improved: +" . number_format($formulaConversion) . " (EXP), and spent " . number_format($formulaCoins, 2, ".", "") . " (Coins).");
					answerCallbackQuery($queryId, "🧠 You improve your (Intelligence) perk and gain +" . number_format($formulaConversion) . " (EXP).");
				}
				$selectUsersPerks = $pdo->prepare("SELECT intelligence_level, intelligence_experience FROM users_perks WHERE user_id = :user_id");
				$selectUsersPerks->execute([":user_id" => $userId]);
				$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
				$formulaExperience = round(pow($rowUsersPerks["intelligence_level"], 1.25));
				editMessageText($queryUserId, $queryMessageId, "🧠 [ <code>INTELLIGENCE</code> ] (Level <b>" . $rowUsersPerks["intelligence_level"] . "</b>)\nBoosts your magical attack power, making it vital for maximizing the effectiveness of spells and magical abilities.\n\nℹ️ The experience required for the next level is " . number_format($rowUsersPerks["intelligence_experience"]) . "/<b>" . number_format($formulaExperience) . "</b> [<b>" . number_format(($rowUsersPerks["intelligence_experience"] / $formulaExperience) * 100, 2, ".", "") . "%</b>] (EXP).", false, '&reply_markup={"inline_keyboard":[[{"text":"1% (Coins)","callback_data":"perks_intelligence_one"},{"text":"5% (Coins)","callback_data":"perks_intelligence_five"},{"text":"25% (Coins)","callback_data":"perks_intelligence_twentyfive"}],[{"text":"MAX (Coins)","callback_data":"perks_intelligence_max"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData === "perks_intelligence_cancel") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersPerks = $pdo->prepare("SELECT intelligence_level, intelligence_experience FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $queryUserId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$formulaExperience = round(pow($rowUsersPerks["intelligence_level"], 1.25));
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET result = NULL WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $queryUserId]);
			editMessageText($queryUserId, $queryMessageId, "🧠 [ <code>INTELLIGENCE</code> ] (Level <b>" . $rowUsersPerks["intelligence_level"] . "</b>)\nBoosts your magical attack power, making it vital for maximizing the effectiveness of spells and magical abilities.\n\nℹ️ The experience required for the next level is " . number_format($rowUsersPerks["intelligence_experience"]) . "/<b>" . number_format($formulaExperience) . "</b> [<b>" . number_format(($rowUsersPerks["intelligence_experience"] / $formulaExperience) * 100, 2, ".", "") . "%</b>] (EXP).", false, '&reply_markup={"inline_keyboard":[[{"text":"1% (Coins)","callback_data":"perks_intelligence_one"},{"text":"5% (Coins)","callback_data":"perks_intelligence_five"},{"text":"25% (Coins)","callback_data":"perks_intelligence_twentyfive"}],[{"text":"MAX (Coins)","callback_data":"perks_intelligence_max"}]],"resize_keyboard":true}');
		}  catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($text === "🛡 Endurance" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersPerks = $pdo->prepare("SELECT endurance_level, endurance_experience FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $userId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$formulaExperience = round(pow($rowUsersPerks["endurance_level"], 1.25));
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET result = NULL WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			sendMessage($chatId, "🛡 [ <code>ENDURANCE</code> ] (Level <b>" . $rowUsersPerks["endurance_level"] . "</b>)\nIncreases your physical and magical defense, making it a crucial attribute for survival in battle.\n\nℹ️ The experience required for the next level is " . number_format($rowUsersPerks["endurance_experience"]) . "/<b>" . number_format($formulaExperience) . "</b> [<b>" . number_format(($rowUsersPerks["endurance_experience"] / $formulaExperience) * 100, 2, ".", "") . "%</b>] (EXP).", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"1% (Coins)","callback_data":"perks_endurance_one"},{"text":"5% (Coins)","callback_data":"perks_endurance_five"},{"text":"25% (Coins)","callback_data":"perks_endurance_twentyfive"}],[{"text":"MAX (Coins)","callback_data":"perks_endurance_max"}]],"resize_keyboard":true}');
		}  catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if (strpos($queryData, "perks_endurance") === 0 && !in_array($queryData, ["perks_endurance_confirm", "perks_endurance_cancel"])) {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$percentageString = str_replace("perks_endurance_", "", $queryData);
		$percentageMap = ["one" => 1, "five" => 5, "twentyfive" => 25, "max" => 100];
		$percentage = isset($percentageMap[$percentageString]) ? $percentageMap[$percentageString] : (int)$percentageString;
		try {
			$selectUsersPerks = $pdo->prepare("SELECT endurance_level FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $queryUserId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$selectUsersProfiles = $pdo->prepare("SELECT coins FROM users_profiles WHERE user_id = :user_id");
			$selectUsersProfiles->execute([":user_id" => $queryUserId]);
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		if ($rowUsersPerks["endurance_level"] < 999) {
			try {
				for ($requiredExperience = 0; $rowUsersPerks["endurance_level"] < 999; $rowUsersPerks["endurance_level"]++) {
					$nextLevelExperience = round(pow($rowUsersPerks["endurance_level"], 1.25));
					$requiredExperience += max(0, $nextLevelExperience - $rowUsersPerks["endurance_experience"]);
					$rowUsersPerks["endurance_experience"] = max(0, $rowUsersPerks["endurance_experience"] - $nextLevelExperience);
				}
				$formulaCoins = floor(($percentage / 100) * $rowUsersProfiles["coins"] * 10) / 10;
				$formulaCoins = max(0.10, min($formulaCoins, floor(($requiredExperience * 0.10) * 10) / 10));
				if ($rowUsersProfiles["coins"] < $formulaCoins) {
					answerCallbackQuery($queryId, "🤨 You do not have enough (Coins).");
				} else {
					$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET result = :result WHERE user_id = :user_id");
					$updateUsersUtilities->execute([":result" => $percentage, ":user_id" => $queryUserId]);
					editMessageText($queryUserId, $queryMessageId, "ℹ️ Are you sure you want to spend <b>" . $percentage . "%</b> (<i>" . number_format($formulaCoins, 2, ".", "") . " Coins</i>) to upgrade <i>Endurance</i>?", false, '&reply_markup={"inline_keyboard":[[{"text":"✔️","callback_data":"perks_endurance_confirm"},{"text":"❌","callback_data":"perks_endurance_cancel"}]],"resize_keyboard":true}');
				}
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "🥳 Congratulations! You have reached the maximum (Endurance) level.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData === "perks_endurance_confirm") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT result FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersPerks = $pdo->prepare("SELECT endurance_level, endurance_experience FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $queryUserId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$selectUsersProfiles = $pdo->prepare("SELECT coins FROM users_profiles WHERE user_id = :user_id");
			$selectUsersProfiles->execute([":user_id" => $queryUserId]);
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		$requiredExperience = 0;
		$simulatedLevel = $rowUsersPerks["endurance_level"];
		$simulatedExperience = $rowUsersPerks["endurance_experience"];
		for (; $simulatedLevel < 999; $simulatedLevel++) {
			$nextLevelExperience = round(pow($simulatedLevel, 1.25));
			$requiredExperience += max(0, $nextLevelExperience - $simulatedExperience);
			$simulatedExperience = max(0, $simulatedExperience - $nextLevelExperience);
		}
		$formulaCoins = floor(($rowUsersUtilities["result"] / 100) * $rowUsersProfiles["coins"] * 10) / 10;
		$formulaCoins = max(0.10, min($formulaCoins, floor(($requiredExperience * 0.10) * 10) / 10));
		$formulaConversion = floor($formulaCoins / 0.10);
		if ($rowUsersProfiles["coins"] < $formulaCoins) {
			try {
				answerCallbackQuery($queryId, "🤨 You do not have enough (Coins).");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			$rowUsersPerks["endurance_experience"] += $formulaConversion;
			for ($levelsGained = 0; $rowUsersPerks["endurance_experience"] >= round(pow($rowUsersPerks["endurance_level"], 1.25)) && $rowUsersPerks["endurance_level"] < 999; $levelsGained++) {
				$nextLevelExperience = round(pow($rowUsersPerks["endurance_level"], 1.25));
				$rowUsersPerks["endurance_experience"] -= $nextLevelExperience;
				$rowUsersPerks["endurance_level"]++;
				$formulaHealthPoints += max(1, round(0.005 + pow($rowUsersPerks["endurance_level"], 0.95)));
				$formulaPhysicalDefense += max(1, round(0.09 * pow($rowUsersPerks["endurance_level"], 1.009)));
				$formulaMagicDefense += max(1, round(0.09 * pow($rowUsersPerks["endurance_level"], 1.009)));
			}
			try {
				$pdo->beginTransaction();
				$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET coins = coins - :coins WHERE user_id = :user_id");
				$updateUsersProfiles->execute([":coins" => $formulaCoins, ":user_id" => $queryUserId]);
				$updateUsersPerks = $pdo->prepare("UPDATE users_perks SET endurance_experience = :endurance_experience WHERE user_id = :user_id");
				$updateUsersPerks->execute([":endurance_experience" => $rowUsersPerks["endurance_experience"], ":user_id" => $queryUserId]);
				if ($levelsGained > 0) {
					$updateUsersPerks = $pdo->prepare("UPDATE users_perks SET endurance_level = endurance_level + :endurance_level WHERE user_id = :user_id");
					$updateUsersPerks->execute([":endurance_level" => $levelsGained, ":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET max_health_points = max_health_points + :max_health_points, physical_defense = physical_defense + :physical_defense, magic_defense = magic_defense + :magic_defense WHERE user_id = :user_id");
					$updateUsersStatistics->execute([
						":max_health_points" => $formulaHealthPoints,
						":physical_defense" => $formulaPhysicalDefense,
						":magic_defense" => $formulaMagicDefense,
						":user_id" => $queryUserId
					]);
				}
				$pdo->commit();
				if ($levelsGained > 0) {
					systemLogs($pdo, $queryUserId, "INFO", "(Endurance) perk improved: +" . number_format($formulaConversion) . " (EXP), +" . number_format($formulaHealthPoints) . " (Health Points), +" . number_format($formulaPhysicalDefense) . " (Physical Defense), and +" . number_format($formulaMagicDefense) . " (Magic Defense), and spent " . number_format($formulaCoins, 2, ".", "") . " (Coins).");
					answerCallbackQuery($queryId, "🛡 You improve your (Endurance) perk and gain +" . number_format($formulaConversion) . " (EXP), +" . number_format($formulaHealthPoints) . " (Health Points), +" . number_format($formulaPhysicalDefense) . " (Physical Defense), and +" . number_format($formulaMagicDefense) . " (Magic Defense).");
				} else {
					systemLogs($pdo, $queryUserId, "INFO", "(Endurance) perk improved: +" . number_format($formulaConversion) . " (EXP), and spent " . number_format($formulaCoins, 2, ".", "") . " (Coins).");
					answerCallbackQuery($queryId, "🛡 You improve your (Endurance) perk and gain +" . number_format($formulaConversion) . " (EXP).");
				}
				$selectUsersPerks = $pdo->prepare("SELECT endurance_level, endurance_experience FROM users_perks WHERE user_id = :user_id");
				$selectUsersPerks->execute([":user_id" => $userId]);
				$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
				$formulaExperience = round(pow($rowUsersPerks["endurance_level"], 1.25));
				editMessageText($queryUserId, $queryMessageId, "🛡 [ <code>ENDURANCE</code> ] (Level <b>" . $rowUsersPerks["endurance_level"] . "</b>)\nIncreases your physical and magical defense, making it a crucial attribute for survival in battle.\n\nℹ️ The experience required for the next level is " . number_format($rowUsersPerks["endurance_experience"]) . "/<b>" . number_format($formulaExperience) . "</b> [<b>" . number_format(($rowUsersPerks["endurance_experience"] / $formulaExperience) * 100, 2, ".", "") . "%</b>] (EXP).", false, '&reply_markup={"inline_keyboard":[[{"text":"1% (Coins)","callback_data":"perks_endurance_one"},{"text":"5% (Coins)","callback_data":"perks_endurance_five"},{"text":"25% (Coins)","callback_data":"perks_endurance_twentyfive"}],[{"text":"MAX (Coins)","callback_data":"perks_endurance_max"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData === "perks_endurance_cancel") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersPerks = $pdo->prepare("SELECT endurance_level, endurance_experience FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $queryUserId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$formulaExperience = round(pow($rowUsersPerks["endurance_level"], 1.25));
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET result = NULL WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $queryUserId]);
			editMessageText($queryUserId, $queryMessageId, "🛡 [ <code>ENDURANCE</code> ] (Level <b>" . $rowUsersPerks["endurance_level"] . "</b>)\nIncreases your physical and magical defense, making it a crucial attribute for survival in battle.\n\nℹ️ The experience required for the next level is " . number_format($rowUsersPerks["endurance_experience"]) . "/<b>" . number_format($formulaExperience) . "</b> [<b>" . number_format(($rowUsersPerks["endurance_experience"] / $formulaExperience) * 100, 2, ".", "") . "%</b>] (EXP).", false, '&reply_markup={"inline_keyboard":[[{"text":"1% (Coins)","callback_data":"perks_endurance_one"},{"text":"5% (Coins)","callback_data":"perks_endurance_five"},{"text":"25% (Coins)","callback_data":"perks_endurance_twentyfive"}],[{"text":"MAX (Coins)","callback_data":"perks_endurance_max"}]],"resize_keyboard":true}');
		}  catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($text === "📖 Education" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersPerks = $pdo->prepare("SELECT education_level, education_experience FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $userId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$formulaExperience = round(pow($rowUsersPerks["education_level"], 1.25));
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET result = NULL WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			sendMessage($chatId, "📖 [ <code>EDUCATION</code> ] (Level <b>" . $rowUsersPerks["education_level"] . "</b>)\nBoosts experience acquisition, making it essential for faster progression.\n\nℹ️ The experience required for the next level is " . number_format($rowUsersPerks["education_experience"]) . "/<b>" . number_format($formulaExperience) . "</b> [<b>" . number_format(($rowUsersPerks["education_experience"] / $formulaExperience) * 100, 2, ".", "") . "%</b>] (EXP).", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"1% (Coins)","callback_data":"perks_education_one"},{"text":"5% (Coins)","callback_data":"perks_education_five"},{"text":"25% (Coins)","callback_data":"perks_education_twentyfive"}],[{"text":"MAX (Coins)","callback_data":"perks_education_max"}]],"resize_keyboard":true}');
		}  catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if (strpos($queryData, "perks_education") === 0 && !in_array($queryData, ["perks_education_confirm", "perks_education_cancel"])) {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$percentageString = str_replace("perks_education_", "", $queryData);
		$percentageMap = ["one" => 1, "five" => 5, "twentyfive" => 25, "max" => 100];
		$percentage = isset($percentageMap[$percentageString]) ? $percentageMap[$percentageString] : (int)$percentageString;
		try {
			$selectUsersPerks = $pdo->prepare("SELECT education_level FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $queryUserId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$selectUsersProfiles = $pdo->prepare("SELECT coins FROM users_profiles WHERE user_id = :user_id");
			$selectUsersProfiles->execute([":user_id" => $queryUserId]);
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		if ($rowUsersPerks["education_level"] < 999) {
			try {
				for ($requiredExperience = 0; $rowUsersPerks["education_level"] < 999; $rowUsersPerks["education_level"]++) {
					$nextLevelExperience = round(pow($rowUsersPerks["education_level"], 1.25));
					$requiredExperience += max(0, $nextLevelExperience - $rowUsersPerks["education_experience"]);
					$rowUsersPerks["education_experience"] = max(0, $rowUsersPerks["education_experience"] - $nextLevelExperience);
				}
				$formulaCoins = floor(($percentage / 100) * $rowUsersProfiles["coins"] * 10) / 10;
				$formulaCoins = max(0.10, min($formulaCoins, floor(($requiredExperience * 0.10) * 10) / 10));
				if ($rowUsersProfiles["coins"] < $formulaCoins) {
					answerCallbackQuery($queryId, "🤨 You do not have enough (Coins).");
				} else {
					$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET result = :result WHERE user_id = :user_id");
					$updateUsersUtilities->execute([":result" => $percentage, ":user_id" => $queryUserId]);
					editMessageText($queryUserId, $queryMessageId, "ℹ️ Are you sure you want to spend <b>" . $percentage . "%</b> (<i>" . number_format($formulaCoins, 2, ".", "") . " Coins</i>) to upgrade <i>Education</i>?", false, '&reply_markup={"inline_keyboard":[[{"text":"✔️","callback_data":"perks_education_confirm"},{"text":"❌","callback_data":"perks_education_cancel"}]],"resize_keyboard":true}');
				}
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "🥳 Congratulations! You have reached the maximum (Education) level.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData === "perks_education_confirm") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT result FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersPerks = $pdo->prepare("SELECT education_level, education_experience FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $queryUserId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$selectUsersProfiles = $pdo->prepare("SELECT coins FROM users_profiles WHERE user_id = :user_id");
			$selectUsersProfiles->execute([":user_id" => $queryUserId]);
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		$requiredExperience = 0;
		$simulatedLevel = $rowUsersPerks["education_level"];
		$simulatedExperience = $rowUsersPerks["education_experience"];
		for (; $simulatedLevel < 999; $simulatedLevel++) {
			$nextLevelExperience = round(pow($simulatedLevel, 1.25));
			$requiredExperience += max(0, $nextLevelExperience - $simulatedExperience);
			$simulatedExperience = max(0, $simulatedExperience - $nextLevelExperience);
		}
		$formulaCoins = floor(($rowUsersUtilities["result"] / 100) * $rowUsersProfiles["coins"] * 10) / 10;
		$formulaCoins = max(0.10, min($formulaCoins, floor(($requiredExperience * 0.10) * 10) / 10));
		$formulaConversion = floor($formulaCoins / 0.10);
		if ($rowUsersProfiles["coins"] < $formulaCoins) {
			try {
				answerCallbackQuery($queryId, "🤨 You do not have enough (Coins).");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			$rowUsersPerks["education_experience"] += $formulaConversion;
			for ($levelsGained = 0; $rowUsersPerks["education_experience"] >= round(pow($rowUsersPerks["education_level"], 1.25)) && $rowUsersPerks["education_level"] < 999; $levelsGained++) {
				$nextLevelExperience = round(pow($rowUsersPerks["education_level"], 1.25));
				$rowUsersPerks["education_experience"] -= $nextLevelExperience;
				$rowUsersPerks["education_level"]++;
				$formulaUserExperience += max(0.01, round(0.036 * pow($rowUsersPerks["education_level"], 0.8), 2));
			}
			try {
				$pdo->beginTransaction();
				$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET coins = coins - :coins WHERE user_id = :user_id");
				$updateUsersProfiles->execute([":coins" => $formulaCoins, ":user_id" => $queryUserId]);
				$updateUsersPerks = $pdo->prepare("UPDATE users_perks SET education_experience = :education_experience WHERE user_id = :user_id");
				$updateUsersPerks->execute([":education_experience" => $rowUsersPerks["education_experience"], ":user_id" => $queryUserId]);
				if ($levelsGained > 0) {
					$updateUsersPerks = $pdo->prepare("UPDATE users_perks SET education_level = education_level + :education_level WHERE user_id = :user_id");
					$updateUsersPerks->execute([":education_level" => $levelsGained, ":user_id" => $queryUserId]);
					$updateUsersStatisticsBonuses = $pdo->prepare("UPDATE users_statistics_bonuses SET experience = experience + :experience WHERE user_id = :user_id");
					$updateUsersStatisticsBonuses->execute([
						":experience" => $formulaUserExperience,
						":user_id" => $queryUserId
					]);
				}
				$pdo->commit();
				if ($levelsGained > 0) {
					systemLogs($pdo, $queryUserId, "INFO", "(Education) perk improved: +" . number_format($formulaConversion) . " (EXP) and +" . number_format($formulaUserExperience, 2, ".", "") . "% (Experience), and spent " . number_format($formulaCoins, 2, ".", "") . " (Coins).");
					answerCallbackQuery($queryId, "📖 You improve your (Education) perk and gain +" . number_format($formulaConversion) . " (EXP) and +" . number_format($formulaUserExperience, 2, ".", "") . "% (Experience).");
				} else {
					systemLogs($pdo, $queryUserId, "INFO", "(Education) perk improved: +" . number_format($formulaConversion) . " (EXP), and spent " . number_format($formulaCoins, 2, ".", "") . " (Coins).");
					answerCallbackQuery($queryId, "📖 You improve your (Education) perk and gain +" . number_format($formulaConversion) . " (EXP).");
				}
				$selectUsersPerks = $pdo->prepare("SELECT education_level, education_experience FROM users_perks WHERE user_id = :user_id");
				$selectUsersPerks->execute([":user_id" => $userId]);
				$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
				$formulaExperience = round(pow($rowUsersPerks["education_level"], 1.25));
				editMessageText($queryUserId, $queryMessageId, "📖 [ <code>EDUCATION</code> ] (Level <b>" . $rowUsersPerks["education_level"] . "</b>)\nBoosts experience acquisition, making it essential for faster progression.\n\nℹ️ The experience required for the next level is " . number_format($rowUsersPerks["education_experience"]) . "/<b>" . number_format($formulaExperience) . "</b> [<b>" . number_format(($rowUsersPerks["education_experience"] / $formulaExperience) * 100, 2, ".", "") . "%</b>] (EXP).", false, '&reply_markup={"inline_keyboard":[[{"text":"1% (Coins)","callback_data":"perks_education_one"},{"text":"5% (Coins)","callback_data":"perks_education_five"},{"text":"25% (Coins)","callback_data":"perks_education_twentyfive"}],[{"text":"MAX (Coins)","callback_data":"perks_education_max"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData === "perks_education_cancel") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersPerks = $pdo->prepare("SELECT education_level, education_experience FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $queryUserId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$formulaExperience = round(pow($rowUsersPerks["education_level"], 1.25));
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET result = NULL WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $queryUserId]);
			editMessageText($queryUserId, $queryMessageId, "📖 [ <code>EDUCATION</code> ] (Level <b>" . $rowUsersPerks["education_level"] . "</b>)\nBoosts experience acquisition, making it essential for faster progression.\n\nℹ️ The experience required for the next level is " . number_format($rowUsersPerks["education_experience"]) . "/<b>" . number_format($formulaExperience) . "</b> [<b>" . number_format(($rowUsersPerks["education_experience"] / $formulaExperience) * 100, 2, ".", "") . "%</b>] (EXP).", false, '&reply_markup={"inline_keyboard":[[{"text":"1% (Coins)","callback_data":"perks_education_one"},{"text":"5% (Coins)","callback_data":"perks_education_five"},{"text":"25% (Coins)","callback_data":"perks_education_twentyfive"}],[{"text":"MAX (Coins)","callback_data":"perks_education_max"}]],"resize_keyboard":true}');
		}  catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($text === "🎲 Luck" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersPerks = $pdo->prepare("SELECT luck_level, luck_experience FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $userId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$formulaExperience = round(pow($rowUsersPerks["luck_level"], 2.00));
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET result = NULL WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			sendMessage($chatId, "🎲 [ <code>LUCK</code> ] (Level <b>" . $rowUsersPerks["luck_level"] . "</b>)\nEnhances your ability to deal damage, making it a crucial attribute for both melee and ranged combat.\n\nℹ️ The experience required for the next level is " . number_format($rowUsersPerks["luck_experience"]) . "/<b>" . number_format($formulaExperience) . "</b> [<b>" . number_format(($rowUsersPerks["luck_experience"] / $formulaExperience) * 100, 2, ".", "") . "%</b>] (EXP).", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"1% (Coins)","callback_data":"perks_luck_one"},{"text":"5% (Coins)","callback_data":"perks_luck_five"},{"text":"25% (Coins)","callback_data":"perks_luck_twentyfive"}],[{"text":"MAX (Coins)","callback_data":"perks_luck_max"}]],"resize_keyboard":true}');
		}  catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if (strpos($queryData, "perks_luck") === 0 && !in_array($queryData, ["perks_luck_confirm", "perks_luck_cancel"])) {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$percentageString = str_replace("perks_luck_", "", $queryData);
		$percentageMap = ["one" => 1, "five" => 5, "twentyfive" => 25, "max" => 100];
		$percentage = isset($percentageMap[$percentageString]) ? $percentageMap[$percentageString] : (int)$percentageString;
		try {
			$selectUsersPerks = $pdo->prepare("SELECT luck_level FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $queryUserId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$selectUsersProfiles = $pdo->prepare("SELECT coins FROM users_profiles WHERE user_id = :user_id");
			$selectUsersProfiles->execute([":user_id" => $queryUserId]);
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		if ($rowUsersPerks["luck_level"] < 100) {
			try {
				for ($requiredExperience = 0; $rowUsersPerks["luck_level"] < 100; $rowUsersPerks["luck_level"]++) {
					$nextLevelExperience = round(pow($rowUsersPerks["luck_level"], 2.00));
					$requiredExperience += max(0, $nextLevelExperience - $rowUsersPerks["luck_experience"]);
					$rowUsersPerks["luck_experience"] = max(0, $rowUsersPerks["luck_experience"] - $nextLevelExperience);
				}
				$formulaCoins = floor(($percentage / 100) * $rowUsersProfiles["coins"] * 10) / 10;
				$formulaCoins = max(0.10, min($formulaCoins, floor(($requiredExperience * 0.10) * 10) / 10));
				if ($rowUsersProfiles["coins"] < $formulaCoins) {
					answerCallbackQuery($queryId, "🤨 You do not have enough (Coins).");
				} else {
					$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET result = :result WHERE user_id = :user_id");
					$updateUsersUtilities->execute([":result" => $percentage, ":user_id" => $queryUserId]);
					editMessageText($queryUserId, $queryMessageId, "ℹ️ Are you sure you want to spend <b>" . $percentage . "%</b> (<i>" . number_format($formulaCoins, 2, ".", "") . " Coins</i>) to upgrade <i>Luck</i>?", false, '&reply_markup={"inline_keyboard":[[{"text":"✔️","callback_data":"perks_luck_confirm"},{"text":"❌","callback_data":"perks_luck_cancel"}]],"resize_keyboard":true}');
				}
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "🥳 Congratulations! You have reached the maximum (Luck) level.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData === "perks_luck_confirm") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT result FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersPerks = $pdo->prepare("SELECT luck_level, luck_experience FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $queryUserId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$selectUsersProfiles = $pdo->prepare("SELECT coins FROM users_profiles WHERE user_id = :user_id");
			$selectUsersProfiles->execute([":user_id" => $queryUserId]);
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		$requiredExperience = 0;
		$simulatedLevel = $rowUsersPerks["luck_level"];
		$simulatedExperience = $rowUsersPerks["luck_experience"];
		for (; $simulatedLevel < 100; $simulatedLevel++) {
			$nextLevelExperience = round(pow($simulatedLevel, 2.00));
			$requiredExperience += max(0, $nextLevelExperience - $simulatedExperience);
			$simulatedExperience = max(0, $simulatedExperience - $nextLevelExperience);
		}
		$formulaCoins = floor(($rowUsersUtilities["result"] / 100) * $rowUsersProfiles["coins"] * 10) / 10;
		$formulaCoins = max(0.10, min($formulaCoins, floor(($requiredExperience * 0.10) * 10) / 10));
		$formulaConversion = floor($formulaCoins / 0.10);
		if ($rowUsersProfiles["coins"] < $formulaCoins) {
			try {
				answerCallbackQuery($queryId, "🤨 You do not have enough (Coins).");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			$rowUsersPerks["luck_experience"] += $formulaConversion;
			for ($levelsGained = 0; $rowUsersPerks["luck_experience"] >= round(pow($rowUsersPerks["luck_level"], 2.00)) && $rowUsersPerks["luck_level"] < 100; $levelsGained++) {
				$nextLevelExperience = round(pow($rowUsersPerks["luck_level"], 2.00));
				$rowUsersPerks["luck_experience"] -= $nextLevelExperience;
				$rowUsersPerks["luck_level"]++;
			}
			try {
				$pdo->beginTransaction();
				$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET coins = coins - :coins WHERE user_id = :user_id");
				$updateUsersProfiles->execute([":coins" => $formulaCoins, ":user_id" => $queryUserId]);
				$updateUsersPerks = $pdo->prepare("UPDATE users_perks SET luck_experience = :luck_experience WHERE user_id = :user_id");
				$updateUsersPerks->execute([":luck_experience" => $rowUsersPerks["luck_experience"], ":user_id" => $queryUserId]);
				if ($levelsGained > 0) {
					$updateUsersPerks = $pdo->prepare("UPDATE users_perks SET luck_level = luck_level + :luck_level WHERE user_id = :user_id");
					$updateUsersPerks->execute([":luck_level" => $levelsGained, ":user_id" => $queryUserId]);
				}
				$pdo->commit();
				if ($levelsGained > 0) {
					systemLogs($pdo, $queryUserId, "INFO", "(Luck) perk improved: +" . number_format($formulaConversion) . " (EXP), +" . number_format($levelsGained) . " (Level), and spent " . number_format($formulaCoins, 2, ".", "") . " (Coins).");
					answerCallbackQuery($queryId, "🎲 You improve your (Luck) perk and gain +" . number_format($formulaConversion) . " (EXP) and +" . number_format($levelsGained) . " (Level).");
				} else {
					systemLogs($pdo, $queryUserId, "INFO", "(Luck) perk improved: +" . number_format($formulaConversion) . " (EXP), and spent " . number_format($formulaCoins, 2, ".", "") . " (Coins).");
					answerCallbackQuery($queryId, "🎲 You improve your (Luck) perk and gain +" . number_format($formulaConversion) . " (EXP).");
				}
				$selectUsersPerks = $pdo->prepare("SELECT luck_level, luck_experience FROM users_perks WHERE user_id = :user_id");
				$selectUsersPerks->execute([":user_id" => $userId]);
				$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
				$formulaExperience = round(pow($rowUsersPerks["luck_level"], 2.00));
				editMessageText($queryUserId, $queryMessageId, "🎲 [ <code>LUCK</code> ] (Level <b>" . $rowUsersPerks["luck_level"] . "</b>)\nEnhances your ability to deal damage, making it a crucial attribute for both melee and ranged combat.\n\nℹ️ The experience required for the next level is " . number_format($rowUsersPerks["luck_experience"]) . "/<b>" . number_format($formulaExperience) . "</b> [<b>" . number_format(($rowUsersPerks["luck_experience"] / $formulaExperience) * 100, 2, ".", "") . "%</b>] (EXP).", false, '&reply_markup={"inline_keyboard":[[{"text":"1% (Coins)","callback_data":"perks_luck_one"},{"text":"5% (Coins)","callback_data":"perks_luck_five"},{"text":"25% (Coins)","callback_data":"perks_luck_twentyfive"}],[{"text":"MAX (Coins)","callback_data":"perks_luck_max"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData === "perks_luck_cancel") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersPerks = $pdo->prepare("SELECT luck_level, luck_experience FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $queryUserId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$formulaExperience = round(pow($rowUsersPerks["luck_level"], 2.00));
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET result = NULL WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $queryUserId]);
			editMessageText($queryUserId, $queryMessageId, "🎲 [ <code>LUCK</code> ] (Level <b>" . $rowUsersPerks["luck_level"] . "</b>)\nEnhances your ability to deal damage, making it a crucial attribute for both melee and ranged combat.\n\nℹ️ The experience required for the next level is " . number_format($rowUsersPerks["luck_experience"]) . "/<b>" . number_format($formulaExperience) . "</b> [<b>" . number_format(($rowUsersPerks["luck_experience"] / $formulaExperience) * 100, 2, ".", "") . "%</b>] (EXP).", false, '&reply_markup={"inline_keyboard":[[{"text":"1% (Coins)","callback_data":"perks_luck_one"},{"text":"5% (Coins)","callback_data":"perks_luck_five"},{"text":"25% (Coins)","callback_data":"perks_luck_twentyfive"}],[{"text":"MAX (Coins)","callback_data":"perks_luck_max"}]],"resize_keyboard":true}');
		}  catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Perks) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}
