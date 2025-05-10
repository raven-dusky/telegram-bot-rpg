<?php
if ($text === "🔰 Profile" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section IN ('Main Menu', 'Dark Wanderer', 'Expeditions') AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersProfiles = $pdo->prepare("SELECT * FROM users_profiles WHERE user_id = :user_id");
			$selectUsersProfiles->execute([":user_id" => $userId]);
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
			$selectUsersStatistics = $pdo->prepare("SELECT * FROM users_statistics WHERE user_id = :user_id");
			$selectUsersStatistics->execute([":user_id" => $userId]);
			$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
			$selectUsersStatisticsBonuses = $pdo->prepare("SELECT * FROM users_statistics_bonuses WHERE user_id = :user_id");
			$selectUsersStatisticsBonuses->execute([":user_id" => $userId]);
			$rowUsersStatisticsBonuses = $selectUsersStatisticsBonuses->fetch(PDO::FETCH_ASSOC);
			$selectUsersPerks = $pdo->prepare("SELECT strength_level, intelligence_level, endurance_level, education_level, luck_level FROM users_perks WHERE user_id = :user_id");
			$selectUsersPerks->execute([":user_id" => $userId]);
			$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
			$selectUsersMaps = $pdo->prepare("SELECT * FROM users_maps WHERE user_id = :user_id");
			$selectUsersMaps->execute([":user_id" => $userId]);
			$rowUsersMaps = $selectUsersMaps->fetch(PDO::FETCH_ASSOC);
			$selectPayments = $pdo->prepare("SELECT 1 FROM payments WHERE product_name = 'Darkness Pass (DP)' AND product_datetime >= NOW() OR product_datetime IS NULL AND user_id = :user_id ORDER BY payments.id DESC LIMIT 1");
			$selectPayments->execute([":user_id" => $userId]);
			$rowPayments = $selectPayments->fetch(PDO::FETCH_ASSOC);
			$selectSystemRoles = $pdo->prepare("SELECT role FROM system_roles WHERE user_id = :user_id");
			$selectSystemRoles->execute([":user_id" => $userId]);
			$rowSystemRoles = $selectSystemRoles->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
		$formulaPhysicalDamage = round($rowUsersStatistics["physical_damage"] + (($rowUsersStatisticsBonuses["physical_damage"] / 100) * $rowUsersStatistics["physical_damage"]));
		$formulaRangedDamage = round($rowUsersStatistics["ranged_damage"] + (($rowUsersStatisticsBonuses["ranged_damage"] / 100) * $rowUsersStatistics["ranged_damage"]));
		$formulaMagicDamage = round($rowUsersStatistics["magic_damage"] + (($rowUsersStatisticsBonuses["magic_damage"] / 100) * $rowUsersStatistics["magic_damage"]));
		$formulaPhysicalDefense = $rowUsersStatistics["physical_defense"] + (($rowUsersStatisticsBonuses["physical_defense"] / 100) * $rowUsersStatistics["physical_defense"]);
		$formulaMagicDefense = $rowUsersStatistics["magic_defense"] + (($rowUsersStatisticsBonuses["magic_defense"] / 100) * $rowUsersStatistics["magic_defense"]);
		$formulaCriticalDamage = $rowUsersStatistics["critical_damage"] + (($rowUsersStatisticsBonuses["critical_damage"] / 100) * $rowUsersStatistics["critical_damage"]);
		$formulaHitRate = round($rowUsersStatistics["hit_rate"] + (($rowUsersStatisticsBonuses["hit_rate"] / 100) * $rowUsersStatistics["hit_rate"]));
		$formulaEvade = round($rowUsersStatistics["evade"] + (($rowUsersStatisticsBonuses["evade"] / 100) * $rowUsersStatistics["evade"]));
		$formulaActionSpeed = round($rowUsersStatistics["action_speed"] + (($rowUsersStatisticsBonuses["action_speed"] / 100) * $rowUsersStatistics["action_speed"]));
		$url = file_get_contents("https://api.telegram.org/bot" . $bot . "/getUserProfilePhotos?user_id=" . $userId);
		$url = json_decode($url);
		$photos = $url->result->photos[0][0]->file_id;
		$stringDarknessPass = $rowPayments ? (!empty($rowPayments["product_datetime"]) ? "\n😈 " . (new DateTime())->diff(new DateTime($rowPayments["product_datetime"]))->format("%d(d) %h(h) %i(m) %s(s) — (Darkness Pass)") : "\n😈 Unlimited — (Darkness Pass)") : "";
		$stringSystemRoles = $rowSystemRoles ? "\n🎖 " . $rowSystemRoles["role"] : "";
		$iconEnergyPoints = ((($rowUsersStatistics["energy_points"] > 93) && ($rowUsersStatistics["max_energy_points"] == 125)) || ($rowUsersStatistics["energy_points"] > 375 && $rowUsersStatistics["max_energy_points"] == 500)) ? "🔋" : "🪫";
		if (!empty($photos)) {
			try {
				$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Profile' WHERE user_id = :user_id");
				$updateUsersUtilities->execute([":user_id" => $userId]);
				sendPhoto($chatId, $photos, "🆔 <code>$userId</code>\n🌐 @$username\n👤 <b>$firstName</b>\n\n🔰 " . $rowUsersProfiles["level"] . " LVL\n♦️" . number_format($rowUsersProfiles["experience"]) . "/" . number_format(round(pow($rowUsersProfiles["level"], 3.80))) . " [<b>" . number_format(($rowUsersProfiles["experience"] / round(pow($rowUsersProfiles["level"], 3.80))) * 100, 2, ".", "") . "%</b>] EXP$stringSystemRoles$stringDarknessPass\n🪙 " . number_format($rowUsersProfiles["coins"], 2, ".", "") . " (Coins)\n💎 " . number_format($rowUsersProfiles["diamonds"]) . " (Diamonds)\n\n[ <code>STATISTICS</code> ]\n❤️ " . number_format($rowUsersStatistics["health_points"]) . "/" . number_format($rowUsersStatistics["max_health_points"]) . " (Health Points)\n$iconEnergyPoints " . $rowUsersStatistics["energy_points"] . "/" . $rowUsersStatistics["max_energy_points"] . " (Energy Points)\n💧 " . number_format($rowUsersStatistics["mana_points"], 2, ".", "") . "/" . $rowUsersStatistics["max_mana_points"] . " (Mana Points)\n🎯 " . number_format($formulaHitRate) . " +<b>" . number_format($rowUsersStatisticsBonuses["hit_rate"], 2, ".", "") . "%</b> (Hit Rate)\n⚔️ " . number_format($formulaPhysicalDamage) . " +<b>" . number_format($rowUsersStatisticsBonuses["physical_damage"], 2, ".", "") . "%</b> (P-Damage)\n🏹 " . number_format($formulaRangedDamage) . " +<b>" . number_format($rowUsersStatisticsBonuses["ranged_damage"], 2, ".", "") . "%</b> (R-Damage)\n🔮 " . number_format($formulaMagicDamage) . " +<b>" . number_format($rowUsersStatisticsBonuses["magic_damage"], 2, ".", "") . "%</b> (M-Damage)\n🛡 " . number_format($formulaPhysicalDefense) . " +<b>" . number_format($rowUsersStatisticsBonuses["physical_defense"], 2, ".", "") . "%</b> (P-Defense)\n🛡 " . number_format($formulaMagicDefense) . " +<b>" . number_format($rowUsersStatisticsBonuses["magic_defense"], 2, ".", "") . "%</b> (M-Defense)\n💥 " . number_format($formulaCriticalDamage, 2, ".", "") . " +<b>" . number_format($rowUsersStatisticsBonuses["critical_damage"], 2, ".", "") . "%</b> (Critical Damage)\n🥾 " . number_format($formulaEvade) . " +<b>" . number_format($rowUsersStatisticsBonuses["evade"], 2, ".", "") . "%</b> (Evade)\n⚡ " . number_format($formulaActionSpeed, 2, ".", "") . " +<b>" . number_format($rowUsersStatisticsBonuses["action_speed"], 2, ".", "") . "%</b> (Action Speed)\n\n[ <code>RESISTANCE</code> ]\n☀️ " . $rowUsersStatistics["holy_resistance"] . " (Holy Resistance)\n🌀 " . $rowUsersStatistics["dark_resistance"] . " (Dark Resistance)\n🔥 " . $rowUsersStatistics["elemental_resistance"] . " (Elemental Resistance)\n☠️ " . $rowUsersStatistics["poison_resistance"] . " (Poison Resistance)\n\n[ <code>PERKS</code> ]\n🥊 " . $rowUsersPerks["strength_level"] . " (Strength)\n🧠 " . $rowUsersPerks["intelligence_level"] . " (Intelligence)\n🛡 " . $rowUsersPerks["endurance_level"] . " (Endurance)\n📖 " . $rowUsersPerks["education_level"] . " (Education)\n🎲 " . $rowUsersPerks["luck_level"] . " (Luck)", '&reply_markup={"keyboard":[["🎒 Inventory", "🦺 Equipment"],["✳️ Attributes", "⛺ Campfire"],["🥷🏻 Shadow Clone", "🔘 Gems"],["🔙 Go Back"]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
		} else {
			try {
				$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Profile' WHERE user_id = :user_id");
				$updateUsersUtilities->execute([":user_id" => $userId]);
				sendMessage($chatId, "🆔 <code>$userId</code>\n🌐 @$username\n👤 <b>$firstName</b>\n\n🔰 " . $rowUsersProfiles["level"] . " LVL\n♦️" . number_format($rowUsersProfiles["experience"]) . "/" . number_format(round(pow($rowUsersProfiles["level"], 3.80))) . " [<b>" . number_format(($rowUsersProfiles["experience"] / round(pow($rowUsersProfiles["level"], 3.80))) * 100, 2, ".", "") . "%</b>] EXP$stringSystemRoles$stringDarknessPass\n🪙 " . number_format($rowUsersProfiles["coins"], 2, ".", "") . " (Coins)\n💎 " . number_format($rowUsersProfiles["diamonds"]) . " (Diamonds)\n\n[ <code>STATISTICS</code> ]\n❤️ " . number_format($rowUsersStatistics["health_points"]) . "/" . number_format($rowUsersStatistics["max_health_points"]) . " (Health Points)\n$iconEnergyPoints " . $rowUsersStatistics["energy_points"] . "/" . $rowUsersStatistics["max_energy_points"] . " (Energy Points)\n💧 " . number_format($rowUsersStatistics["mana_points"], 2, ".", "") . "/" . $rowUsersStatistics["max_mana_points"] . " (Mana Points)\n🎯 " . number_format($formulaHitRate) . " +<b>" . number_format($rowUsersStatisticsBonuses["hit_rate"], 2, ".", "") . "%</b> (Hit Rate)\n⚔️ " . number_format($formulaPhysicalDamage) . " +<b>" . number_format($rowUsersStatisticsBonuses["physical_damage"], 2, ".", "") . "%</b> (P-Damage)\n🏹 " . number_format($formulaRangedDamage) . " +<b>" . number_format($rowUsersStatisticsBonuses["ranged_damage"], 2, ".", "") . "%</b> (R-Damage)\n🔮 " . number_format($formulaMagicDamage) . " +<b>" . number_format($rowUsersStatisticsBonuses["magic_damage"], 2, ".", "") . "%</b> (M-Damage)\n🛡 " . number_format($formulaPhysicalDefense) . " +<b>" . number_format($rowUsersStatisticsBonuses["physical_defense"], 2, ".", "") . "%</b> (P-Defense)\n🛡 " . number_format($formulaMagicDefense) . " +<b>" . number_format($rowUsersStatisticsBonuses["magic_defense"], 2, ".", "") . "%</b> (M-Defense)\n💥 " . number_format($formulaCriticalDamage, 2, ".", "") . " +<b>" . number_format($rowUsersStatisticsBonuses["critical_damage"], 2, ".", "") . "%</b> (Critical Damage)\n🥾 " . number_format($formulaEvade) . " +<b>" . number_format($rowUsersStatisticsBonuses["evade"], 2, ".", "") . "%</b> (Evade)\n⚡ " . number_format($formulaActionSpeed, 2, ".", "") . " +<b>" . number_format($rowUsersStatisticsBonuses["action_speed"], 2, ".", "") . "%</b> (Action Speed)\n\n[ <code>RESISTANCE</code> ]\n☀️ " . $rowUsersStatistics["holy_resistance"] . " (Holy Resistance)\n🌀 " . $rowUsersStatistics["dark_resistance"] . " (Dark Resistance)\n🔥 " . $rowUsersStatistics["elemental_resistance"] . " (Elemental Resistance)\n☠️ " . $rowUsersStatistics["poison_resistance"] . " (Poison Resistance)\n\n[ <code>PERKS</code> ]\n🥊 " . $rowUsersPerks["strength_level"] . " (Strength)\n🧠 " . $rowUsersPerks["intelligence_level"] . " (Intelligence)\n🛡 " . $rowUsersPerks["endurance_level"] . " (Endurance)\n📖 " . $rowUsersPerks["education_level"] . " (Education)\n🎲 " . $rowUsersPerks["luck_level"] . " (Luck)", false, false, false, '&reply_markup={"keyboard":[["🎒 Inventory", "🦺 Equipment"],["✳️ Attributes", "⛺ Campfire"],["🥷🏻 Shadow Clone" ,"🔘 Gems"],["🔙 Go Back"]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Profile) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}
