<?php
if ($text === "✳️ Attributes" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section IN ('Profile', 'Profile/Campfire', 'Profile/Attributes', 'Profile/Inventory', 'Shadow Clone') AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $userId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Profile/Attributes' WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			sendMessage($chatId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . $rowUsersAttributes["points"] . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . $rowUsersAttributes["vitality"] . "\n▪️ (<code>STR</code>) Strength ➔ " . $rowUsersAttributes["strength"] . "\n▪️ (<code>INT</code>) Intelligence ➔ " . $rowUsersAttributes["intelligence"] . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . $rowUsersAttributes["accuracy"] . "\n▪️ (<code>AGI</code>) Agility ➔ " . $rowUsersAttributes["agility"] . "\n▪️ (<code>WIL</code>) Willpower ➔ " . $rowUsersAttributes["willpower"] . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($queryData == "attributes_vitality_one") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}	
		if ($rowUsersAttributes["points"] > 0) {
			try {
				$pdo->beginTransaction();
				$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points - 1, vitality = vitality + 1 WHERE user_id = :user_id");
				$updateUsersAttributes->execute([":user_id" => $queryUserId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET max_health_points = max_health_points + 25, physical_defense = physical_defense + 3 WHERE user_id = :user_id");
				$updateUsersStatistics->execute(["user_id" => $queryUserId]);
				$pdo->commit();
				editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] - 1) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . ($rowUsersAttributes["vitality"] + 1) . "\n▪️ (<code>STR</code>) Strength ➔ " . $rowUsersAttributes["strength"] . "\n▪️ (<code>INT</code>) Intelligence ➔ " . $rowUsersAttributes["intelligence"] . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . $rowUsersAttributes["accuracy"] . "\n▪️ (<code>AGI</code>) Agility ➔ " . $rowUsersAttributes["agility"] . "\n▪️ (<code>WIL</code>) Willpower ➔ " . $rowUsersAttributes["willpower"] . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have enough (Attribute) points.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_vitality_two") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}	
		if ($rowUsersAttributes["points"] >= 2) {
			try {
				$pdo->beginTransaction();
				$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points - 2, vitality = vitality + 2 WHERE user_id = :user_id");
				$updateUsersAttributes->execute([":user_id" => $queryUserId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET max_health_points = max_health_points + 50, physical_defense = physical_defense + 6 WHERE user_id = :user_id");
				$updateUsersStatistics->execute(["user_id" => $queryUserId]);
				$pdo->commit();
				editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] - 2) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . ($rowUsersAttributes["vitality"] + 2) . "\n▪️ (<code>STR</code>) Strength ➔ " . $rowUsersAttributes["strength"] . "\n▪️ (<code>INT</code>) Intelligence ➔ " . $rowUsersAttributes["intelligence"] . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . $rowUsersAttributes["accuracy"] . "\n▪️ (<code>AGI</code>) Agility ➔ " . $rowUsersAttributes["agility"] . "\n▪️ (<code>WIL</code>) Willpower ➔ " . $rowUsersAttributes["willpower"] . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have enough (Attribute) points.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_vitality_three") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}	
		if ($rowUsersAttributes["points"] >= 3) {
			try {
				$pdo->beginTransaction();
				$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points - 3, vitality = vitality + 3 WHERE user_id = :user_id");
				$updateUsersAttributes->execute([":user_id" => $queryUserId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET max_health_points = max_health_points + 75, physical_defense = physical_defense + 9 WHERE user_id = :user_id");
				$updateUsersStatistics->execute(["user_id" => $queryUserId]);
				$pdo->commit();
				editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] - 3) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . ($rowUsersAttributes["vitality"] + 3) . "\n▪️ (<code>STR</code>) Strength ➔ " . $rowUsersAttributes["strength"] . "\n▪️ (<code>INT</code>) Intelligence ➔ " . $rowUsersAttributes["intelligence"] . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . $rowUsersAttributes["accuracy"] . "\n▪️ (<code>AGI</code>) Agility ➔ " . $rowUsersAttributes["agility"] . "\n▪️ (<code>WIL</code>) Willpower ➔ " . $rowUsersAttributes["willpower"] . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have enough (Attribute) points.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_strength_one") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}	
		if ($rowUsersAttributes["points"] > 0) {
			try {
				$pdo->beginTransaction();
				$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points - 1, strength = strength + 1 WHERE user_id = :user_id");
				$updateUsersAttributes->execute([":user_id" => $queryUserId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET physical_damage = physical_damage + 5, ranged_damage = ranged_damage + 5, critical_damage = critical_damage + 0.50 WHERE user_id = :user_id");
				$updateUsersStatistics->execute(["user_id" => $queryUserId]);
				$pdo->commit();
				editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] - 1) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . $rowUsersAttributes["vitality"] . "\n▪️ (<code>STR</code>) Strength ➔ " . ($rowUsersAttributes["strength"] + 1) . "\n▪️ (<code>INT</code>) Intelligence ➔ " . $rowUsersAttributes["intelligence"] . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . $rowUsersAttributes["accuracy"] . "\n▪️ (<code>AGI</code>) Agility ➔ " . $rowUsersAttributes["agility"] . "\n▪️ (<code>WIL</code>) Willpower ➔ " . $rowUsersAttributes["willpower"] . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have enough (Attribute) points.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_strength_two") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}	
		if ($rowUsersAttributes["points"] >= 2) {
			try {
				$pdo->beginTransaction();
				$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points - 2, strength = strength + 2 WHERE user_id = :user_id");
				$updateUsersAttributes->execute([":user_id" => $queryUserId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET physical_damage = physical_damage + 10, ranged_damage = ranged_damage + 10, critical_damage = critical_damage + 1 WHERE user_id = :user_id");
				$updateUsersStatistics->execute(["user_id" => $queryUserId]);
				$pdo->commit();
				editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] - 2) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . $rowUsersAttributes["vitality"] . "\n▪️ (<code>STR</code>) Strength ➔ " . ($rowUsersAttributes["strength"] + 2) . "\n▪️ (<code>INT</code>) Intelligence ➔ " . $rowUsersAttributes["intelligence"] . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . $rowUsersAttributes["accuracy"] . "\n▪️ (<code>AGI</code>) Agility ➔ " . $rowUsersAttributes["agility"] . "\n▪️ (<code>WIL</code>) Willpower ➔ " . $rowUsersAttributes["willpower"] . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have enough (Attribute) points.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_strength_three") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}	
		if ($rowUsersAttributes["points"] >= 3) {
			try {
				$pdo->beginTransaction();
				$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points - 3, strength = strength + 3 WHERE user_id = :user_id");
				$updateUsersAttributes->execute([":user_id" => $queryUserId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET physical_damage = physical_damage + 15, ranged_damage = ranged_damage + 15, critical_damage = critical_damage + 1.50 WHERE user_id = :user_id");
				$updateUsersStatistics->execute(["user_id" => $queryUserId]);
				$pdo->commit();
				editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] - 3) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . $rowUsersAttributes["vitality"] . "\n▪️ (<code>STR</code>) Strength ➔ " . ($rowUsersAttributes["strength"] + 3) . "\n▪️ (<code>INT</code>) Intelligence ➔ " . $rowUsersAttributes["intelligence"] . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . $rowUsersAttributes["accuracy"] . "\n▪️ (<code>AGI</code>) Agility ➔ " . $rowUsersAttributes["agility"] . "\n▪️ (<code>WIL</code>) Willpower ➔ " . $rowUsersAttributes["willpower"] . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have enough (Attribute) points.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_intelligence_one") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}	
		if ($rowUsersAttributes["points"] > 0) {
			try {
				$pdo->beginTransaction();
				$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points - 1, intelligence = intelligence + 1 WHERE user_id = :user_id");
				$updateUsersAttributes->execute([":user_id" => $queryUserId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET magic_damage = magic_damage + 3, magic_defense = magic_defense + 3 WHERE user_id = :user_id");
				$updateUsersStatistics->execute(["user_id" => $queryUserId]);
				$pdo->commit();
				editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] - 1) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . $rowUsersAttributes["vitality"] . "\n▪️ (<code>STR</code>) Strength ➔ " . $rowUsersAttributes["strength"] . "\n▪️ (<code>INT</code>) Intelligence ➔ " . ($rowUsersAttributes["intelligence"] + 1) . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . $rowUsersAttributes["accuracy"] . "\n▪️ (<code>AGI</code>) Agility ➔ " . $rowUsersAttributes["agility"] . "\n▪️ (<code>WIL</code>) Willpower ➔ " . $rowUsersAttributes["willpower"] . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have enough (Attribute) points.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_intelligence_two") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}	
		if ($rowUsersAttributes["points"] >= 2) {
			try {
				$pdo->beginTransaction();
				$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points - 2, intelligence = intelligence + 2 WHERE user_id = :user_id");
				$updateUsersAttributes->execute([":user_id" => $queryUserId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET magic_damage = magic_damage + 6, magic_defense = magic_defense + 6 WHERE user_id = :user_id");
				$updateUsersStatistics->execute(["user_id" => $queryUserId]);
				$pdo->commit();
				editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] - 2) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . $rowUsersAttributes["vitality"] . "\n▪️ (<code>STR</code>) Strength ➔ " . $rowUsersAttributes["strength"] . "\n▪️ (<code>INT</code>) Intelligence ➔ " . ($rowUsersAttributes["intelligence"] + 2) . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . $rowUsersAttributes["accuracy"] . "\n▪️ (<code>AGI</code>) Agility ➔ " . $rowUsersAttributes["agility"] . "\n▪️ (<code>WIL</code>) Willpower ➔ " . $rowUsersAttributes["willpower"] . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have enough (Attribute) points.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_intelligence_three") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}	
		if ($rowUsersAttributes["points"] >= 3) {
			try {
				$pdo->beginTransaction();
				$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points - 3, intelligence = intelligence + 3 WHERE user_id = :user_id");
				$updateUsersAttributes->execute([":user_id" => $queryUserId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET magic_damage = magic_damage + 9, magic_defense = magic_defense + 9 WHERE user_id = :user_id");
				$updateUsersStatistics->execute(["user_id" => $queryUserId]);
				$pdo->commit();
				editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] - 3) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . $rowUsersAttributes["vitality"] . "\n▪️ (<code>STR</code>) Strength ➔ " . $rowUsersAttributes["strength"] . "\n▪️ (<code>INT</code>) Intelligence ➔ " . ($rowUsersAttributes["intelligence"] + 3) . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . $rowUsersAttributes["accuracy"] . "\n▪️ (<code>AGI</code>) Agility ➔ " . $rowUsersAttributes["agility"] . "\n▪️ (<code>WIL</code>) Willpower ➔ " . $rowUsersAttributes["willpower"] . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have enough (Attribute) points.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_accuracy_one") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}	
		if ($rowUsersAttributes["points"] > 0) {
			try {
				$pdo->beginTransaction();
				$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points - 1, accuracy = accuracy + 1 WHERE user_id = :user_id");
				$updateUsersAttributes->execute([":user_id" => $queryUserId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET hit_rate = hit_rate + 1 WHERE user_id = :user_id");
				$updateUsersStatistics->execute(["user_id" => $queryUserId]);
				$pdo->commit();
				editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] - 1) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . $rowUsersAttributes["vitality"] . "\n▪️ (<code>STR</code>) Strength ➔ " . $rowUsersAttributes["strength"] . "\n▪️ (<code>INT</code>) Intelligence ➔ " . $rowUsersAttributes["intelligence"] . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . ($rowUsersAttributes["accuracy"] + 1) . "\n▪️ (<code>AGI</code>) Agility ➔ " . $rowUsersAttributes["agility"] . "\n▪️ (<code>WIL</code>) Willpower ➔ " . $rowUsersAttributes["willpower"] . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have enough (Attribute) points.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_accuracy_two") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}	
		if ($rowUsersAttributes["points"] >= 2) {
			try {
				$pdo->beginTransaction();
				$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points - 2, accuracy = accuracy + 2 WHERE user_id = :user_id");
				$updateUsersAttributes->execute([":user_id" => $queryUserId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET hit_rate = hit_rate + 2 WHERE user_id = :user_id");
				$updateUsersStatistics->execute(["user_id" => $queryUserId]);
				$pdo->commit();
				editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] - 2) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . $rowUsersAttributes["vitality"] . "\n▪️ (<code>STR</code>) Strength ➔ " . $rowUsersAttributes["strength"] . "\n▪️ (<code>INT</code>) Intelligence ➔ " . $rowUsersAttributes["intelligence"] . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . ($rowUsersAttributes["accuracy"] + 2) . "\n▪️ (<code>AGI</code>) Agility ➔ " . $rowUsersAttributes["agility"] . "\n▪️ (<code>WIL</code>) Willpower ➔ " . $rowUsersAttributes["willpower"] . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have enough (Attribute) points.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_accuracy_three") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}	
		if ($rowUsersAttributes["points"] >= 3) {
			try {
				$pdo->beginTransaction();
				$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points - 3, accuracy = accuracy + 3 WHERE user_id = :user_id");
				$updateUsersAttributes->execute([":user_id" => $queryUserId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET hit_rate = hit_rate + 3 WHERE user_id = :user_id");
				$updateUsersStatistics->execute(["user_id" => $queryUserId]);
				$pdo->commit();
				editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] - 3) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . $rowUsersAttributes["vitality"] . "\n▪️ (<code>STR</code>) Strength ➔ " . $rowUsersAttributes["strength"] . "\n▪️ (<code>INT</code>) Intelligence ➔ " . $rowUsersAttributes["intelligence"] . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . ($rowUsersAttributes["accuracy"] + 3) . "\n▪️ (<code>AGI</code>) Agility ➔ " . $rowUsersAttributes["agility"] . "\n▪️ (<code>WIL</code>) Willpower ➔ " . $rowUsersAttributes["willpower"] . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have enough (Attribute) points.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_agility_one") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}	
		if ($rowUsersAttributes["points"] > 0) {
			try {
				$pdo->beginTransaction();
				$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points - 1, agility = agility + 1 WHERE user_id = :user_id");
				$updateUsersAttributes->execute([":user_id" => $queryUserId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET evade = evade + 1 WHERE user_id = :user_id");
				$updateUsersStatistics->execute(["user_id" => $queryUserId]);
				$pdo->commit();
				editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] - 1) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . $rowUsersAttributes["vitality"] . "\n▪️ (<code>STR</code>) Strength ➔ " . $rowUsersAttributes["strength"] . "\n▪️ (<code>INT</code>) Intelligence ➔ " . $rowUsersAttributes["intelligence"] . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . $rowUsersAttributes["accuracy"] . "\n▪️ (<code>AGI</code>) Agility ➔ " . ($rowUsersAttributes["agility"] + 1) . "\n▪️ (<code>WIL</code>) Willpower ➔ " . $rowUsersAttributes["willpower"] . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have enough (Attribute) points.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_agility_two") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}	
		if ($rowUsersAttributes["points"] >= 2) {
			try {
				$pdo->beginTransaction();
				$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points - 2, agility = agility + 2 WHERE user_id = :user_id");
				$updateUsersAttributes->execute([":user_id" => $queryUserId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET evade = evade + 2 WHERE user_id = :user_id");
				$updateUsersStatistics->execute(["user_id" => $queryUserId]);
				$pdo->commit();
				editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] - 2) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . $rowUsersAttributes["vitality"] . "\n▪️ (<code>STR</code>) Strength ➔ " . $rowUsersAttributes["strength"] . "\n▪️ (<code>INT</code>) Intelligence ➔ " . $rowUsersAttributes["intelligence"] . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . $rowUsersAttributes["accuracy"] . "\n▪️ (<code>AGI</code>) Agility ➔ " . ($rowUsersAttributes["agility"] + 2) . "\n▪️ (<code>WIL</code>) Willpower ➔ " . $rowUsersAttributes["willpower"] . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have enough (Attribute) points.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_agility_three") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}	
		if ($rowUsersAttributes["points"] >= 3) {
			try {
				$pdo->beginTransaction();
				$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points - 3, agility = agility + 3 WHERE user_id = :user_id");
				$updateUsersAttributes->execute([":user_id" => $queryUserId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET evade = evade + 3 WHERE user_id = :user_id");
				$updateUsersStatistics->execute(["user_id" => $queryUserId]);
				$pdo->commit();
				editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] - 3) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . $rowUsersAttributes["vitality"] . "\n▪️ (<code>STR</code>) Strength ➔ " . $rowUsersAttributes["strength"] . "\n▪️ (<code>INT</code>) Intelligence ➔ " . $rowUsersAttributes["intelligence"] . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . $rowUsersAttributes["accuracy"] . "\n▪️ (<code>AGI</code>) Agility ➔ " . ($rowUsersAttributes["agility"] + 3) . "\n▪️ (<code>WIL</code>) Willpower ➔ " . $rowUsersAttributes["willpower"] . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have enough (Attribute) points.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_willpower_one") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}	
		if ($rowUsersAttributes["points"] > 0) {
			try {
				$pdo->beginTransaction();
				$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points - 1, willpower = willpower + 1 WHERE user_id = :user_id");
				$updateUsersAttributes->execute([":user_id" => $queryUserId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET max_mana_points = max_mana_points + 3 WHERE user_id = :user_id");
				$updateUsersStatistics->execute(["user_id" => $queryUserId]);
				$pdo->commit();
				editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] - 1) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . $rowUsersAttributes["vitality"] . "\n▪️ (<code>STR</code>) Strength ➔ " . $rowUsersAttributes["strength"] . "\n▪️ (<code>INT</code>) Intelligence ➔ " . $rowUsersAttributes["intelligence"] . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . $rowUsersAttributes["accuracy"] . "\n▪️ (<code>AGI</code>) Agility ➔ " . $rowUsersAttributes["agility"] . "\n▪️ (<code>WIL</code>) Willpower ➔ " . ($rowUsersAttributes["willpower"] + 1) . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have enough (Attribute) points.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_willpower_two") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}	
		if ($rowUsersAttributes["points"] >= 2) {
			try {
				$pdo->beginTransaction();
				$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points - 2, willpower = willpower + 2 WHERE user_id = :user_id");
				$updateUsersAttributes->execute([":user_id" => $queryUserId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET max_mana_points = max_mana_points + 6 WHERE user_id = :user_id");
				$updateUsersStatistics->execute(["user_id" => $queryUserId]);
				$pdo->commit();
				editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] - 2) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . $rowUsersAttributes["vitality"] . "\n▪️ (<code>STR</code>) Strength ➔ " . $rowUsersAttributes["strength"] . "\n▪️ (<code>INT</code>) Intelligence ➔ " . $rowUsersAttributes["intelligence"] . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . $rowUsersAttributes["accuracy"] . "\n▪️ (<code>AGI</code>) Agility ➔ " . $rowUsersAttributes["agility"] . "\n▪️ (<code>WIL</code>) Willpower ➔ " . ($rowUsersAttributes["willpower"] + 2) . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have enough (Attribute) points.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_willpower_three") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}	
		if ($rowUsersAttributes["points"] >= 3) {
			try {
				$pdo->beginTransaction();
				$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points - 3, willpower = willpower + 3 WHERE user_id = :user_id");
				$updateUsersAttributes->execute([":user_id" => $queryUserId]);
				$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET max_mana_points = max_mana_points + 9 WHERE user_id = :user_id");
				$updateUsersStatistics->execute(["user_id" => $queryUserId]);
				$pdo->commit();
				editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] - 3) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . $rowUsersAttributes["vitality"] . "\n▪️ (<code>STR</code>) Strength ➔ " . $rowUsersAttributes["strength"] . "\n▪️ (<code>INT</code>) Intelligence ➔ " . $rowUsersAttributes["intelligence"] . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . $rowUsersAttributes["accuracy"] . "\n▪️ (<code>AGI</code>) Agility ➔ " . $rowUsersAttributes["agility"] . "\n▪️ (<code>WIL</code>) Willpower ➔ " . ($rowUsersAttributes["willpower"] + 3) . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have enough (Attribute) points.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_reset") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		if ($rowUsersAttributes["vitality"] OR $rowUsersAttributes["strength"] OR $rowUsersAttributes["intelligence"] OR $rowUsersAttributes["accuracy"] OR $rowUsersAttributes["agility"] > 0 OR $rowUsersAttributes["willpower"]) {
			editMessageText($queryUserId, $queryMessageId, "ℹ️ Are you sure you want to spend 💎 <b>100</b> (<i>Diamonds</i>) to reset your Attribute Points?", false, '&reply_markup={"inline_keyboard":[[{"text":"✔️","callback_data":"attributes_reset_confirm"},{"text":"❌","callback_data":"attributes_reset_cancel"}]],"resize_keyboard":true}');
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have any (Attribute) points to reset.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_reset_confirm") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
			$selectUsersProfiles = $pdo->prepare("SELECT * FROM users_profiles WHERE user_id = :user_id");
			$selectUsersProfiles->execute([":user_id" => $queryUserId]);
			$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		if ($rowUsersAttributes["vitality"] OR $rowUsersAttributes["strength"] OR $rowUsersAttributes["intelligence"] OR $rowUsersAttributes["accuracy"] OR $rowUsersAttributes["agility"] > 0 OR $rowUsersAttributes["willpower"]) {
			if ($rowUsersProfiles["diamonds"] >= 100) {
				try {
					$pdo->beginTransaction();
					$updateUsersAttributes = $pdo->prepare("UPDATE users_attributes SET points = points + vitality + strength + intelligence + accuracy + agility, vitality = 0, strength = 0, intelligence = 0, accuracy = 0, agility = 0 WHERE user_id = :user_id");
					$updateUsersAttributes->execute([":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("
						UPDATE users_statistics
						SET
							physical_defense = physical_defense - (:vitality * 3),
							magic_damage = magic_damage - (:intelligence * 3),
							magic_defense = magic_defense - (:intelligence * 3),
							physical_damage = physical_damage - (:strength * 5),
							critical_damage = critical_damage - (:strength * 0.5),
							ranged_damage = ranged_damage - (:strength * 5),
							health_points = CASE
								WHEN health_points = max_health_points THEN GREATEST(health_points - (:vitality * 25), 0)
								WHEN health_points > max_health_points - (:vitality * 25) THEN max_health_points - (:vitality * 25)
								ELSE health_points
							END,
							max_health_points = max_health_points - (:vitality * 25),
							mana_points = CASE
								WHEN mana_points = max_mana_points THEN GREATEST(mana_points - (:willpower * 3), 0)
								WHEN mana_points > max_mana_points - (:willpower * 3) THEN max_mana_points - (:willpower * 3)
								ELSE mana_points
							END,
							max_mana_points = max_mana_points - (:willpower * 3),
							evade = evade - :agility,
							hit_rate = hit_rate - :accuracy
						WHERE user_id = :user_id");
					$updateUsersStatistics->execute([
						":vitality" => $rowUsersAttributes["vitality"],
						":strength" => $rowUsersAttributes["strength"],
						":accuracy" => $rowUsersAttributes["accuracy"],
						":intelligence" => $rowUsersAttributes["intelligence"],
						":agility" => $rowUsersAttributes["agility"],
						":willpower" => $rowUsersAttributes["willpower"],
						":user_id" => $queryUserId
					]);
					$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET diamonds = diamonds - 100 WHERE user_id = :user_id");
					$updateUsersProfiles->execute([":user_id" => $queryUserId]);
					$pdo->commit();
					answerCallbackQuery($queryId, "🔄 You have successfully reset your (Attribute) points.");
					editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . ($rowUsersAttributes["points"] + $rowUsersAttributes["vitality"] + $rowUsersAttributes["strength"] + $rowUsersAttributes["intelligence"] + $rowUsersAttributes["accuracy"] + $rowUsersAttributes["agility"]) . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ 0\n▪️ (<code>STR</code>) Strength ➔ 0\n▪️ (<code>INT</code>) Intelligence ➔ 0\n▪️ (<code>ACC</code>) Accuracy ➔ 0\n▪️ (<code>AGI</code>) Agility ➔ 0\n▪️ (<code>WIL</code>) Willpower ➔ 0\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
				} catch (Exception $exception) {
					$pdo->rollBack();
					systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			} else {
				try {
					answerCallbackQuery($queryId, "😕 You don't have enough 💎 (Diamonds) to reset your Attribute Points.");
				} catch (Exception $exception) {
					systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😕 You don't have any (Attribute) points to reset.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "attributes_reset_cancel") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Attributes' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAttributes = $pdo->prepare("SELECT * FROM users_attributes WHERE user_id = :user_id");
			$selectUsersAttributes->execute([":user_id" => $queryUserId]);
			$rowUsersAttributes = $selectUsersAttributes->fetch(PDO::FETCH_ASSOC);
			editMessageText($queryUserId, $queryMessageId, "✳️ [ <code>ATTRIBUTES</code> ]\nPoints Available: <b>" . $rowUsersAttributes["points"] . "</b>\n▪️ (<code>VIT</code>) Vitality ➔ " . $rowUsersAttributes["vitality"] . "\n▪️ (<code>STR</code>) Strength ➔ " . $rowUsersAttributes["strength"] . "\n▪️ (<code>INT</code>) Intelligence ➔ " . $rowUsersAttributes["intelligence"] . "\n▪️ (<code>ACC</code>) Accuracy ➔ " . $rowUsersAttributes["accuracy"] . "\n▪️ (<code>AGI</code>) Agility ➔ " . $rowUsersAttributes["agility"] . "\n▪️ (<code>WIL</code>) Willpower ➔ " . $rowUsersAttributes["willpower"] . "\n\nℹ️ You can reset your attribute points by spending 💎 100 (<i>Diamonds</i>).", false, '&reply_markup={"inline_keyboard":[[{"text":"VIT","callback_data":"empty"},{"text":"1","callback_data":"attributes_vitality_one"},{"text":"2","callback_data":"attributes_vitality_two"},{"text":"3","callback_data":"attributes_vitality_three"}],[{"text":"STR","callback_data":"empty"},{"text":"1","callback_data":"attributes_strength_one"},{"text":"2","callback_data":"attributes_strength_two"},{"text":"3","callback_data":"attributes_strength_three"}],[{"text":"INT","callback_data":"empty"},{"text":"1","callback_data":"attributes_intelligence_one"},{"text":"2","callback_data":"attributes_intelligence_two"},{"text":"3","callback_data":"attributes_intelligence_three"}],[{"text":"ACC","callback_data":"empty"},{"text":"1","callback_data":"attributes_accuracy_one"},{"text":"2","callback_data":"attributes_accuracy_two"},{"text":"3","callback_data":"attributes_accuracy_three"}],[{"text":"AGI","callback_data":"empty"},{"text":"1","callback_data":"attributes_agility_one"},{"text":"2","callback_data":"attributes_agility_two"},{"text":"3","callback_data":"attributes_agility_three"}],[{"text":"WIL","callback_data":"empty"},{"text":"1","callback_data":"attributes_willpower_one"},{"text":"2","callback_data":"attributes_willpower_two"},{"text":"3","callback_data":"attributes_willpower_three"}],[{"text":"♻️ Reset","callback_data":"attributes_reset"}]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Attributes) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}
