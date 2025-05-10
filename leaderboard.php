<?php
if ($text === "🏆 Leaderboard" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section IN ('Main Menu', 'Dark Wanderer', 'Expeditions') AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Leaderboard' WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			sendMessage($chatId, "🏆 [ <code>LEADERBOARD</code> ]\n\n▪️ 🔰 <b>Level</b> - Check the top players by level.\n▪️ 💰 <b>Coins</b> - Check the top players by coins.\n\nℹ️ The <b>50</b> best players are shown in each category.", false, false, false, '&reply_markup={"keyboard":[["🔰 Level", "🪙 Coins"],["🔙 Go Back"]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text === "🔰 Level" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Leaderboard' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersProfiles = $pdo->prepare("
			SELECT up.* 
			FROM users_profiles up
				LEFT JOIN system_bans sb ON up.user_id = sb.user_id
				WHERE sb.user_id IS NULL
				ORDER BY up.level DESC, up.experience DESC
				LIMIT 50;
			");
			$selectUsersProfiles->execute();
			$numUsersProfiles = $selectUsersProfiles->rowCount();
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
		$string = "[ <code>HALL OF FAME</code> ]";
		$increment = 1;
		while ($increment <= $numUsersProfiles) {
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
			try {
				$selectUsers = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
				$selectUsers->execute([":user_id" => $rowUsersProfiles["user_id"]]);
				$rowUsers = $selectUsers->fetch(PDO::FETCH_ASSOC);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
			switch ($increment) {
				case 1:
					$string .= "\n🥇 <b>" . $rowUsers["first_name"] . "</b> - " . $rowUsersProfiles["level"] . " (LVL)";
					break;
				case 2:
					$string .= "\n🥈 <b>" . $rowUsers["first_name"] . "</b> - " . $rowUsersProfiles["level"] . " (LVL)";
					break;
				case 3:
					$string .= "\n🥉 <b>" . $rowUsers["first_name"] . "</b> - " . $rowUsersProfiles["level"] . " (LVL)";
					break;
				default:
					$string .= "\n<code>#" . $increment . "</code> <b>" . $rowUsers["first_name"] . "</b> - " . $rowUsersProfiles["level"] . " (LVL)";
					break;
			}
			if ($increment > 50 && $rowUsersProfiles["user_id"] == $userId) {
				$string .= "\n\n➔ Your position: <code>#" . $increment . "</code> <b>" . $rowUsers["first_name"] . "</b> - " . $rowUsersProfiles["level"] . " (LVL)";
			}
			$increment++;
		}
		try {
			sendMessage($chatId, $string);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text === "🪙 Coins" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Leaderboard' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersProfiles = $pdo->prepare("
			SELECT up.* 
			FROM users_profiles up
				LEFT JOIN system_bans sb ON up.user_id = sb.user_id
				WHERE sb.user_id IS NULL
				ORDER BY up.coins DESC
				LIMIT 50;
			");
			$selectUsersProfiles->execute();
			$numUsersProfiles = $selectUsersProfiles->rowCount();
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
		$string = "[ <code>HALL OF FAME</code> ]";
		$increment = 1;
		while ($increment <= $numUsersProfiles) {
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
			try {
				$selectUsers = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
				$selectUsers->execute([":user_id" => $rowUsersProfiles["user_id"]]);
				$rowUsers = $selectUsers->fetch(PDO::FETCH_ASSOC);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
			switch ($increment) {
				case 1:
					$string .= "\n🥇 <b>" . $rowUsers["first_name"] . "</b> - " . number_format($rowUsersProfiles["coins"], 2, ".", "") . " (Coins)";
					break;
				case 2:
					$string .= "\n🥈 <b>" . $rowUsers["first_name"] . "</b> - " . number_format($rowUsersProfiles["coins"], 2, ".", "") . " (Coins)";
					break;
				case 3:
					$string .= "\n🥉 <b>" . $rowUsers["first_name"] . "</b> - " . number_format($rowUsersProfiles["coins"], 2, ".", "") . " (Coins)";
					break;
				default:
					$string .= "\n<code>#".$increment."</code> <b>".$rowUsers["first_name"]."</b> - " . number_format($rowUsersProfiles["coins"], 2, ".", "") . " (Coins)";
					break;
			}
			if ($increment > 50 && $rowUsersProfiles["user_id"] == $userId) {
				$string .= "\n\n➔ Yor position: <code>#".$increment."</code> <b>" . $rowUsers["first_name"] . "</b> - " . number_format($rowUsersProfiles["coins"], 2, ".", "") . " (Coins)";
			}
			$increment++;
		}
		try {
			sendMessage($chatId, $string);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}
