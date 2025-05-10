<?php
if ($text === "🍄 Expeditions" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section IN ('Main Menu', 'Expeditions') AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersExpeditions = $pdo->prepare("SELECT * FROM users_expeditions WHERE user_id = :user_id");
			$selectUsersExpeditions->execute([":user_id" => $userId]);
			$rowUsersExpeditions = $selectUsersExpeditions->fetch(PDO::FETCH_ASSOC);
			$selectExpeditions = $pdo->prepare("SELECT * FROM expeditions WHERE user_id = :user_id ORDER BY expiration_datetime ASC");
			$selectExpeditions->execute([":user_id" => $userId]);
			$numExpeditions = $selectExpeditions->rowCount();
			while ($rowExpeditions = $selectExpeditions->fetch(PDO::FETCH_ASSOC)) {
				$selectItems = $pdo->prepare("SELECT * FROM items WHERE id = :id");
				$selectItems->execute([":id" => $rowExpeditions["item_id"]]);
				$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
				$currentTime = new DateTime(date("Y-m-d H:i:s"));
				$nextTime = new DateTime(date($rowExpeditions["expiration_datetime"]));
				$interval = $currentTime->diff($nextTime);
				if ($currentTime >= $nextTime) {
					$string .= "▪️ Item: " . $rowItems["icon"] . " (<i>" . $rowItems["name"] . "</i>)\n▪️ Time: 0(h) 0(m) 0(s)\n\n";
				} else {
					$interval = $currentTime->diff($nextTime);
					$string .= "▪️ Item: " . $rowItems["icon"] . " (<i>" . $rowItems["name"] . "</i>)\n▪️ Time: " . $interval->format("%H") . "(h) " . $interval->format("%i") . "(m) " . $interval->format("%s") . "(s)\n\n";
				}
			}
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Expeditions' WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			$experienceRequired = pow($rowUsersExpeditions["level"], 2.5);
			$experienceRequired = $experienceRequired - $rowUsersExpeditions["experience"];
			if ($numExpeditions > 0) {
				sendMessage($chatId, "🍄 [ <code>EXPEDITIONS</code> ] (Level <b>" . $rowUsersExpeditions["level"] . "</b>)\nEmbark on thrilling journeys across unknown lands! Each Expedition promises rewards and hidden dangers—face them for a chance at rare loot and legendary encounters!\n\n" . $string . "ℹ️ <i>To advance to the next level, you need " . number_format($experienceRequired) . " (Experience), <a href='https://telegra.ph/expeditions-12-28-6'>here</a> is a complete list of the items you can obtain.</i>", true, false, false, '&reply_markup={"inline_keyboard":[[{"text":"🔍 (search)","callback_data":"expeditions_search"}]],"resize_keyboard":true}');
			} else {
				sendMessage($chatId, "🍄 [ <code>EXPEDITIONS</code> ] (Level <b>" . $rowUsersExpeditions["level"] . "</b>)\nEmbark on thrilling journeys across unknown lands! Each Expedition promises rewards and hidden dangers—face them for a chance at rare loot and legendary encounters!\n\nℹ️ <i>To advance to the next level, you need " . number_format($experienceRequired) . " (Experience), <a href='https://telegra.ph/expeditions-12-28-6'>here</a> is a complete list of the items you can obtain.</i>", true, false, false, '&reply_markup={"inline_keyboard":[[{"text":"🔍 (search)","callback_data":"expeditions_search"}]],"resize_keyboard":true}');
			}
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text && $chatType == "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Expeditions (search)' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectItems = $pdo->prepare("SELECT * FROM items WHERE name LIKE :name");
			$selectItems->execute([":name" => $text]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
		if ($rowItems = $selectItems->fetch(PDO::FETCH_ASSOC)) {
			try {
				$selectLoot = $pdo->prepare("SELECT * FROM loot WHERE item_id = :item_id");
				$selectLoot->execute([":item_id" => $rowItems["id"]]);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
			if ($rowLoot = $selectLoot->fetch(PDO::FETCH_ASSOC)) {
				$selectUsersExpeditions = $pdo->prepare("SELECT * FROM users_expeditions WHERE user_id = :user_id");
				$selectUsersExpeditions->execute([":user_id" => $userId]);
				$rowUsersExpeditions = $selectUsersExpeditions->fetch(PDO::FETCH_ASSOC);
				$selectUsersPerks = $pdo->prepare("SELECT strength_level, intelligence_level, endurance_level, education_level, luck_level FROM users_perks WHERE user_id = :user_id");
				$selectUsersPerks->execute([":user_id" => $userId]);
				$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
				$formulaDropRate = $rowLoot["drop_rate"] 
				+ ($rowUsersExpeditions["level"] * 0.19)
				+ (log($rowUsersPerks["education_level"] + 1, 10) * 0.7)
				+ ($rowUsersPerks["luck_level"] * 0.05);
				$formulaDropRate = min($formulaDropRate, 100);
				sendMessage($chatId, $rowItems["icon"] . " [ <code>" . strtoupper($rowItems["name"]) . "</code> ] (<i>" . $rowLoot["rarity"] . "</i>)\n" . $rowItems["description"] . "\n\nℹ️ <i>The chance of obtaining this item is " . number_format($formulaDropRate, 2) . "%.</i>", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"🫲🏻 (collect)","callback_data":"expeditions_collect"}]],"resize_keyboard":true}');
			} else {
				try {
					sendMessage($chatId, "😅 The (item) was not found. Try again!");
				} catch (Exception $exception) {
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
					exit;
				}
			}
		} else {
			try {
				sendMessage($chatId, "😅 The (item) was not found. Try again!");
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
		}
	}
}

if ($queryData == "expeditions_collect") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Expeditions (search)' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$selectUsersCampfire = $pdo->prepare("SELECT 1 FROM users_campfire WHERE status = 1 AND user_id = :user_id");
		$selectUsersCampfire->execute([":user_id" => $queryUserId]);
		if (!$rowUsersCampfire = $selectUsersCampfire->fetch(PDO::FETCH_ASSOC)) {
			preg_match("/\[([^\]]+)\]/", $queryText, $matches);
			try {
				$selectItems = $pdo->prepare("SELECT * FROM items WHERE name LIKE :name");
				$selectItems->execute([":name" => trim($matches[1])]);
				$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
				$selectLoot = $pdo->prepare("SELECT * FROM loot WHERE item_id = :item_id");
				$selectLoot->execute([":item_id" => $rowItems["id"]]);
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
			if ($rowLoot = $selectLoot->fetch(PDO::FETCH_ASSOC)) {
				$selectPayments = $pdo->prepare("SELECT 1 FROM payments WHERE product_name = 'Darkness Pass (DP)' AND product_datetime >= NOW() OR product_datetime IS NULL AND user_id = :user_id ORDER BY payments.id DESC LIMIT 1");
				$selectPayments->execute([":user_id" => $queryUserId]);
				$rowPayments = $selectPayments->fetch(PDO::FETCH_ASSOC);
				if ($rowPayments) {
					$totalCount = 5;
				} else {
					$totalCount = 3;
				}
				$selectExpeditions = $pdo->prepare("SELECT * FROM expeditions WHERE user_id = :user_id ORDER BY expiration_datetime ASC");
				$selectExpeditions->execute([":user_id" => $queryUserId]);
				$numExpeditions = $selectExpeditions->rowCount();
				if ($numExpeditions == $totalCount) {
					answerCallbackQuery($queryId, "⏳ Wait to get back from the (Expeditions).");
				} else {
					$selectUsersStatistics = $pdo->prepare("SELECT * FROM users_statistics WHERE user_id = :user_id");
					$selectUsersStatistics->execute([":user_id" => $queryUserId]);
					$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
					if ($rowUsersStatistics["energy_points"] >= 75) {
						editMessageText($queryUserId, $queryMessageId, "ℹ️ <i>Are you sure you want to spend 🪫 -75 (Energy Points) to collect " . $rowItems["icon"] . " (" . $rowItems["name"] . ")?</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"✔️","callback_data":"expeditions_collect_confirm"},{"text":"❌","callback_data":"expeditions"}]],"resize_keyboard":true}');
					} else {
						try {
							answerCallbackQuery($queryId, "🪫 Not enough (Energy Points) to collect this item.");
						} catch (Exception $exception) {
							systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
							exit;
						}
					}
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
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Expeditions) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

try {
	if ($queryData == "expeditions_collect_confirm") {
		try {
			$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Expeditions (search)' AND user_id = :user_id");
			$selectUsersUtilities->execute([":user_id" => $queryUserId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
			$selectUsersCampfire = $pdo->prepare("SELECT 1 FROM users_campfire WHERE status = 1 AND user_id = :user_id");
			$selectUsersCampfire->execute([":user_id" => $queryUserId]);
			if (!$rowUsersCampfire = $selectUsersCampfire->fetch(PDO::FETCH_ASSOC)) {
				preg_match("/\(([^)]+)\)\?$/", $queryText, $matches);
				try {
					$selectItems = $pdo->prepare("SELECT * FROM items WHERE name LIKE :name");
					$selectItems->execute([":name" => trim($matches[1])]);
					$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
					$selectLoot = $pdo->prepare("SELECT * FROM loot WHERE item_id = :item_id");
					$selectLoot->execute([":item_id" => $rowItems["id"]]);
				} catch (Exception $exception) {
					systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
				if ($rowLoot = $selectLoot->fetch(PDO::FETCH_ASSOC)) {
					$selectPayments = $pdo->prepare("SELECT 1 FROM payments WHERE product_name = 'Darkness Pass (DP)' AND product_datetime >= NOW() OR product_datetime IS NULL AND user_id = :user_id ORDER BY payments.id DESC LIMIT 1");
					$selectPayments->execute([":user_id" => $queryUserId]);
					$rowPayments = $selectPayments->fetch(PDO::FETCH_ASSOC);
					if ($rowPayments) {
						$totalCount = 5;
					} else {
						$totalCount = 3;
					}
					$selectExpeditions = $pdo->prepare("SELECT * FROM expeditions WHERE user_id = :user_id ORDER BY expiration_datetime ASC");
					$selectExpeditions->execute([":user_id" => $queryUserId]);
					$numExpeditions = $selectExpeditions->rowCount();
					if ($numExpeditions == $totalCount) {
						answerCallbackQuery($queryId, "⏳ Wait to get back from the (Expeditions).");
					} else {
						$selectUsersStatistics = $pdo->prepare("SELECT * FROM users_statistics WHERE user_id = :user_id");
						$selectUsersStatistics->execute([":user_id" => $queryUserId]);
						$rowUsersStatistics = $selectUsersStatistics->fetch(PDO::FETCH_ASSOC);
						if ($rowUsersStatistics["energy_points"] >= 75) {
							try {
								$expirationTime = $rowPayments ? '+1 hour' : '+2 hours';
								$expirationDatetime = (new DateTime())->modify($expirationTime)->format('Y-m-d H:i:s');
								$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET energy_points = energy_points - :energy_points WHERE user_id = :user_id");
								$updateUsersStatistics->execute([
									":energy_points" => 75,
									":user_id" => $queryUserId
								]);
								$insertExpeditions = $pdo->prepare("INSERT INTO expeditions (user_id, item_id, expiration_datetime) VALUES (:user_id, :item_id, :expiration_datetime)");
								$insertExpeditions->execute([":user_id" => $queryUserId, ":item_id" => $rowItems["id"], ":expiration_datetime" => $expirationDatetime]);
								answerCallbackQuery($queryId, "✌️ The (Expedition) is now in progress.");
								editMessageReplyMarkup($queryUserId, $queryMessageId, '&reply_markup={"inline_keyboard":[[{"text":"🔄 (re-collect)","callback_data":"expeditions_collect_confirm"}]],"resize_keyboard":true}');
							} catch (Exception $exception) {
								exit;
							}
						} else {
							try {
								answerCallbackQuery($queryId, "🪫 Not enough (Energy Points) to collect this item.");
							} catch (Exception $exception) {
								systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
								exit;
							}
						}
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
				answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Expeditions) to access those options.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	}
} catch (Exception) {
	exit;
}

if ($queryData == "expeditions_search") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Expeditions' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Expeditions (search)' WHERE user_id = :user_id");
		$updateUsersUtilities->execute([":user_id" => $queryUserId]);
		sendMessage($queryUserId, "ℹ️ <i>Enter the name of the (item) you wish to collect.</i>", false, false, false, '&reply_markup={"keyboard":[["🔙 Go Back"]],"resize_keyboard":true}');
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Expeditions) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}
