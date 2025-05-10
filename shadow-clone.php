<?php
if ($text === "🥷🏻 Shadow Clone" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section IN ('Profile', 'Profile/Campfire', 'Profile/Attributes', 'Profile/Inventory', 'Shadow Clone') AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$selectUsersShadowClone = $pdo->prepare("SELECT * FROM users_shadow_clone WHERE user_id = :user_id");
		$selectUsersShadowClone->execute([":user_id" => $userId]);
		$rowUsersShadowClone = $selectUsersShadowClone->fetch(PDO::FETCH_ASSOC);
		$statusIcon = $rowUsersShadowClone["status"] ? "✔️" : "❌";
		$selectPayments = $pdo->prepare("SELECT 1 FROM payments WHERE product_name = 'Darkness Pass (DP)' AND product_datetime >= NOW() OR product_datetime IS NULL AND user_id = :user_id ORDER BY payments.id DESC LIMIT 1");
		$selectPayments->execute([":user_id" => $userId]);
		$rowPayments = $selectPayments->fetch(PDO::FETCH_ASSOC);
		$formulaManaPoints = max(1, 25 - (($rowUsersShadowClone["level"] - 1) * (24 / 98)));
		$darknessPass = $rowPayments ? 0.09 : 0;
		$formulaExperience = pow($rowUsersShadowClone["level"], 1.52) * 1 * (1 + $darknessPass);
		$formulaCoins = floor(3.5 * pow($rowUsersShadowClone["level"], 1.5));
		sendMessage($chatId, "🥷🏻 [ <code>SHADOW CLONE</code> ] (Level <b>" . $rowUsersShadowClone["level"] . "</b>)\nThe <i>Shadow Clone</i>, a fragment of lost light, is a spectral ally forged from the three suns’ remnants, gathering strength in the shadows to battle the darkness.\n\n[ <code>STATUS</code> ] ($statusIcon)\nEvery <b>" . number_format($formulaManaPoints, 2, ".", "") . "</b>/min of (Mana) consumed, your Shadow Clone produces <b>" . number_format($formulaExperience) . "</b>/min of (Experience).\n\nℹ️ <i>To upgrade your Shadow Clone to the next level, you need " . number_format($formulaCoins, 2, ".", "") . " (Coins).</i>", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"🥷🏻 (status):","callback_data":"empty"},{"text":"✔️","callback_data":"shadow_clone_activate"},{"text":"❌","callback_data":"shadow_clone_deactivate"}],[{"text":"🔍 (inspect)","callback_data":"shadow_clone_inspect"},{"text":"⏏️ Upgrade","callback_data":"shadow_clone_upgrade"}]],"resize_keyboard":true}');
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Shadow Clone) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($queryData === "shadow_clone_activate") {
	try {
		$selectUsersShadowClone = $pdo->prepare("SELECT * FROM users_shadow_clone WHERE user_id = :user_id");
		$selectUsersShadowClone->execute([":user_id" => $queryUserId]);
		$rowUsersShadowClone = $selectUsersShadowClone->fetch(PDO::FETCH_ASSOC);
		$selectPayments = $pdo->prepare("SELECT 1 FROM payments WHERE product_name = 'Darkness Pass (DP)' AND product_datetime >= NOW() OR product_datetime IS NULL AND user_id = :user_id ORDER BY payments.id DESC LIMIT 1");
		$selectPayments->execute([":user_id" => $queryUserId]);
		$rowPayments = $selectPayments->fetch(PDO::FETCH_ASSOC);
		$formulaManaPoints = max(1, 25 - (($rowUsersShadowClone["level"] - 1) * (24 / 98)));
		$darknessPass = $rowPayments ? 0.09 : 0;
		$formulaExperience = pow($rowUsersShadowClone["level"], 1.52) * 1 * (1 + $darknessPass);
		$formulaCoins = floor(3.5 * pow($rowUsersShadowClone["level"], 1.5));
		$selectUsersStatistics = $pdo->prepare("SELECT * FROM users_statistics WHERE mana_points >= :mana_points AND user_id = :user_id");
		$selectUsersStatistics->execute([":mana_points" => $formulaManaPoints, ":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC)) {
		try {
			$updateUsersShadowClone = $pdo->prepare("UPDATE users_shadow_clone SET status = 1, experience = 0, datetime = NOW() WHERE user_id = :user_id");
			$updateUsersShadowClone->execute([":user_id" => $queryUserId]);
			editMessageText($queryUserId, $queryMessageId, "🥷🏻 [ <code>SHADOW CLONE</code> ] (Level <b>" . $rowUsersShadowClone["level"] . "</b>)\nThe <i>Shadow Clone</i>, a fragment of lost light, is a spectral ally forged from the three suns’ remnants, gathering strength in the shadows to battle the darkness.\n\n[ <code>STATUS</code> ] (✔️)\nEvery <b>" . number_format($formulaManaPoints, 2, ".", "") . "</b>/min of (Mana) consumed, your Shadow Clone produces <b>" . number_format($formulaExperience) . "</b>/min of (Experience).\n\nℹ️ <i>To upgrade your Shadow Clone to the next level, you need " . number_format($formulaCoins, 2, ".", "") . " (Coins).</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"🥷🏻 (status):","callback_data":"empty"},{"text":"✔️","callback_data":"shadow_clone_activate"},{"text":"❌","callback_data":"shadow_clone_deactivate"}],[{"text":"🔍 (inspect)","callback_data":"shadow_clone_inspect"},{"text":"⏏️ Upgrade","callback_data":"shadow_clone_upgrade"}]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🤨 You do not have enough 💧 (Mana Points).");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($queryData === "shadow_clone_deactivate") {
	try {
		$selectUsersShadowClone = $pdo->prepare("SELECT * FROM users_shadow_clone WHERE user_id = :user_id");
		$selectUsersShadowClone->execute([":user_id" => $queryUserId]);
		$rowUsersShadowClone = $selectUsersShadowClone->fetch(PDO::FETCH_ASSOC);
		$selectPayments = $pdo->prepare("SELECT 1 FROM payments WHERE product_name = 'Darkness Pass (DP)' AND product_datetime >= NOW() OR product_datetime IS NULL AND user_id = :user_id ORDER BY payments.id DESC LIMIT 1");
		$selectPayments->execute([":user_id" => $queryUserId]);
		$rowPayments = $selectPayments->fetch(PDO::FETCH_ASSOC);
		$formulaManaPoints = max(1, 25 - (($rowUsersShadowClone["level"] - 1) * (24 / 98)));
		$darknessPass = $rowPayments ? 0.09 : 0;
		$formulaExperience = pow($rowUsersShadowClone["level"], 1.52) * 1 * (1 + $darknessPass);
		$formulaCoins = floor(3.5 * pow($rowUsersShadowClone["level"], 1.5));
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	try {
		$updateUsersShadowClone = $pdo->prepare("UPDATE users_shadow_clone SET status = 0 WHERE user_id = :user_id");
		$updateUsersShadowClone->execute([":user_id" => $queryUserId]);
		editMessageText($queryUserId, $queryMessageId, "🥷🏻 [ <code>SHADOW CLONE</code> ] (Level <b>" . $rowUsersShadowClone["level"] . "</b>)\nThe <i>Shadow Clone</i>, a fragment of lost light, is a spectral ally forged from the three suns’ remnants, gathering strength in the shadows to battle the darkness.\n\n[ <code>STATUS</code> ] (❌)\nEvery <b>" . number_format($formulaManaPoints, 2, ".", "") . "</b>/min of (Mana) consumed, your Shadow Clone produces <b>" . number_format($formulaExperience) . "</b>/min of (Experience).\n\nℹ️ <i>To upgrade your Shadow Clone to the next level, you need " . number_format($formulaCoins, 2, ".", "") . " (Coins).</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"🥷🏻 (status):","callback_data":"empty"},{"text":"✔️","callback_data":"shadow_clone_activate"},{"text":"❌","callback_data":"shadow_clone_deactivate"}],[{"text":"🔍 (inspect)","callback_data":"shadow_clone_inspect"},{"text":"⏏️ Upgrade","callback_data":"shadow_clone_upgrade"}]],"resize_keyboard":true}');
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
		exit;
	}	
}

if ($queryData == "shadow_clone_inspect") {
	$selectUsersShadowClone = $pdo->prepare("SELECT * FROM users_shadow_clone WHERE user_id = :user_id");
	$selectUsersShadowClone->execute([":user_id" => $queryUserId]);
	$rowUsersShadowClone = $selectUsersShadowClone->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersShadowClone["status"] == 0) {
		answerCallbackQuery($queryId, "😵‍💫 (Shadow Clone) is currently deactivated.");
		exit;
	}
	$datetime = new DateTime($rowUsersShadowClone["datetime"]);
	$difference = new DateTime();
	$interval = $datetime->diff($difference);
	$string = "" . 
		$interval->format("%a") . "(d) " .
		$interval->format("%H") . "(h) " .
		$interval->format("%i") . "(m) " .
		$interval->format("%s") . "(s)";
	answerCallbackQuery($queryId, $string . "\nYou have gained " . number_format($rowUsersShadowClone["experience"]) . " (Experience).");
}

if ($queryData == "shadow_clone_upgrade") {
	$selectUsersShadowClone = $pdo->prepare("SELECT * FROM users_shadow_clone WHERE user_id = :user_id");
	$selectUsersShadowClone->execute([":user_id" => $queryUserId]);
	$rowUsersShadowClone = $selectUsersShadowClone->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersShadowClone["level"] < 99) {
		$formulaCoins = floor(3.5 * pow($rowUsersShadowClone["level"], 1.5));
		$selectUsersProfiles = $pdo->prepare("SELECT coins FROM users_profiles WHERE user_id = :user_id");
		$selectUsersProfiles->execute([":user_id" => $queryUserId]);
		$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
		if ($rowUsersProfiles["coins"] >= $formulaCoins) {
			try {
				$pdo->beginTransaction();
				$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET coins = coins - :coins WHERE user_id = :user_id");
				$updateUsersProfiles->execute([":coins" => $formulaCoins, ":user_id" => $queryUserId]);
				$updateUsersShadowClone = $pdo->prepare("UPDATE users_shadow_clone SET level = level + 1 WHERE user_id = :user_id");
				$updateUsersShadowClone->execute([":user_id" => $queryUserId]);
				$pdo->commit();
				answerCallbackQuery($queryId, "😎 (Shadow Clone) leveled up to " . ($rowUsersShadowClone["level"] + 1) . "!");
				$statusIcon = $rowUsersShadowClone["status"] ? "✔️" : "❌";
				$selectPayments = $pdo->prepare("SELECT 1 FROM payments WHERE product_name = 'Darkness Pass (DP)' AND product_datetime >= NOW() OR product_datetime IS NULL AND user_id = :user_id ORDER BY payments.id DESC LIMIT 1");
				$selectPayments->execute([":user_id" => $queryUserId]);
				$rowPayments = $selectPayments->fetch(PDO::FETCH_ASSOC);
				$formulaManaPoints = max(1, 25 - ((($rowUsersShadowClone["level"] + 1) - 1) * (24 / 98)));
				$darknessPass = $rowPayments ? 0.09 : 0;
				$formulaExperience = pow(($rowUsersShadowClone["level"] + 1), 1.1) * 1 * (1 + $darknessPass);
				$formulaCoins = floor(10 * pow(($rowUsersShadowClone["level"] + 1), 1.5));
				editMessageText($queryUserId, $queryMessageId, "🥷🏻 [ <code>SHADOW CLONE</code> ] (Level <b>" . ($rowUsersShadowClone["level"] + 1) . "</b>)\nThe <i>Shadow Clone</i>, a fragment of lost light, is a spectral ally forged from the three suns’ remnants, gathering strength in the shadows to battle the darkness.\n\n[ <code>STATUS</code> ] ($statusIcon)\nEvery <b>" . number_format($formulaManaPoints, 2, ".", "") . "</b>/min of (Mana) consumed, your Shadow Clone produces <b>" . number_format($formulaExperience) . "</b>/min of (Experience).\n\nℹ️ <i>To upgrade your Shadow Clone to the next level, you need " . number_format($formulaCoins, 2, ".", "") . " (Coins).</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"🥷🏻 (status):","callback_data":"empty"},{"text":"✔️","callback_data":"shadow_clone_activate"},{"text":"❌","callback_data":"shadow_clone_deactivate"}],[{"text":"🔍 (inspect)","callback_data":"shadow_clone_inspect"},{"text":"⏏️ Upgrade","callback_data":"shadow_clone_upgrade"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			}
		} else {
			answerCallbackQuery($queryId, "😨 You don’t have enough (Coins).");
		}
	} else {
		answerCallbackQuery($queryId, "🎉 (Shadow Clone) is already at the maximum level.");
	}
}
