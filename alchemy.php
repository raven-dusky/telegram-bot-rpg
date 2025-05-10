<?php
include_once("/var/www/html/inventory.php");

if ($text === "⚗️ Alchemy" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Main Menu' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersAlchemy = $pdo->prepare("SELECT * FROM users_alchemy WHERE user_id = :user_id");
			$selectUsersAlchemy->execute([":user_id" => $userId]);
			$rowUsersAlchemy = $selectUsersAlchemy->fetch(PDO::FETCH_ASSOC);
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Alchemy' WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			$experienceRequired = pow($rowUsersAlchemy["level"], 2.79);
			$experienceRequired = $experienceRequired - $rowUsersAlchemy["experience"];
			sendMessage($chatId, "⚗️ [ <code>ALCHEMY</code> ] (Level <b>" . $rowUsersAlchemy["level"] . "</b>)\nThe ancient art of transforming materials into powerful potions and rare artifacts, harnessing the remnants of the shattered suns.\n\nℹ️ <i>To advance to the next level, you need " . number_format($experienceRequired) . " (Experience), <a href='https://telegra.ph/alchemy-12-31-10'>here</a> is a complete list of the items that can be synthesize.</i>", true, false, false, '&reply_markup={"keyboard":[["🧪 Potions"],["🔙 Go Back"]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Alchemy) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text === "🧪 Potions" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Alchemy' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
		if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Potions' WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			sendMessage($chatId,"🧪 [ <code>POTIONS</code> ]\nExplore the art of crafting mystical potions. Use the search button below to find specific recipes or ingredients.", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"🔍 (search)","callback_data":"potions_search"}]]}');
		} else {
			try {
				sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Alchemy) to access those options.");
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
		}
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
	}
}

if ($text && $chatType == "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Potions (search)' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectItems = $pdo->prepare("SELECT * FROM items WHERE name = :name");
			$selectItems->execute([":name" => $text]);
			$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
			$selectAlchemy = $pdo->prepare("SELECT * FROM alchemy WHERE item_id = :item_id");
			$selectAlchemy->execute([":item_id" => $rowItems["id"]]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
		if ($rowAlchemy = $selectAlchemy->fetch(PDO::FETCH_ASSOC)) {
			try {
				$selectIngredients = $pdo->prepare("SELECT * FROM ingredients WHERE item_id = :item_id AND type = 'Alchemy'");
				$selectIngredients->execute([":item_id" => $rowAlchemy["item_id"]]);
				$ingredientsList = [];
				while ($rowIngredients = $selectIngredients->fetch(PDO::FETCH_ASSOC)) {
					$selectItems = $pdo->prepare("SELECT * FROM items WHERE id = :id");
					$selectItems->execute([":id" => $rowIngredients["ingredient_id"]]);
					$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
					$ingredientsList[] = "x" . number_format($rowIngredients["quantity"]) . " " . $rowItems["icon"] . " (" . $rowItems["name"] . ")";
				}
				$ingredients .= "The ingredients required for crafting are: " . implode(", ", array_slice($ingredientsList, 0, -1)) . " and " . end($ingredientsList) . ".";
				$selectItems = $pdo->prepare("SELECT * FROM items WHERE name = :name");
				$selectItems->execute([":name" => $text]);
				$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
				$selectUsersAlchemy = $pdo->prepare("SELECT * FROM users_alchemy WHERE user_id = :user_id");
				$selectUsersAlchemy->execute([":user_id" => $userId]);
				$rowUsersAlchemy = $selectUsersAlchemy->fetch(PDO::FETCH_ASSOC);
				$selectUsersPerks = $pdo->prepare("SELECT education_level, luck_level FROM users_perks WHERE user_id = :user_id");
				$selectUsersPerks->execute([":user_id" => $userId]);
				$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
				$formulaSuccessChance = $rowAlchemy["success_chance"]
				+ ($rowUsersAlchemy["level"] * 0.19)
				+ (log($rowUsersPerks["education_level"] + 1, 10) * 0.7)
				+ ($rowUsersPerks["luck_level"] * 0.05);
				$formulaSuccessChance = min($formulaSuccessChance, 100);
				sendMessage($chatId, $rowItems["icon"] . " [ <code>" . strtoupper($rowItems["name"]) . "</code> ]\n" . $rowItems["description"] . "\n\nℹ️ <i>$ingredients The chance of obtaining this item is " . number_format($formulaSuccessChance, 2) . "%.</i>", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"✔️ (create)","callback_data":"potions_create"}]],"resize_keyboard":true}');
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
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

if ($queryData == "potions_search") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Potions' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Potions (search)' WHERE user_id = :user_id");
		$updateUsersUtilities->execute([":user_id" => $queryUserId]);
		sendMessage($queryUserId, "ℹ️ <i>Enter the name of the (item) you wish to buy.</i>", false, false, false, '&reply_markup={"keyboard":[["🔙 Go Back"]],"resize_keyboard":true}');
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to Potions to access this option.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "potions_create") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Potions (search)' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			preg_match("/\[([^\]]+)\]/", $queryText, $matches);
			$selectItems = $pdo->prepare("SELECT * FROM items WHERE name LIKE :name");
			$selectItems->execute([":name" => trim($matches[1])]);
			$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
			$selectAlchemy = $pdo->prepare("SELECT * FROM alchemy WHERE item_id = :item_id");
			$selectAlchemy->execute([":item_id" => $rowItems["id"]]);
			$rowAlchemy = $selectAlchemy->fetch(PDO::FETCH_ASSOC);
			if ($rowAlchemy) {
				editMessageText($queryUserId, $queryMessageId, "ℹ️ <i>How many " . $rowItems["icon"] . " (" . $rowItems["name"] . ") do you want to create?</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"x1","callback_data":"potions_create_one"},{"text":"x3","callback_data":"potions_create_three"},{"text":"x5","callback_data":"potions_create_five"}]]}');
			} else {
				try {
					sendMessage($chatId, "😅 The (item) was not found. Try again!");
				} catch (Exception $exception) {
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
					exit;
				}
			}
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "potions_create_one" || $queryData == "potions_create_three" || $queryData == "potions_create_five") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Potions (search)' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$quantity = 1;
		if ($queryData == "potions_create_three") {
			$quantity = 3;
		} elseif ($queryData == "potions_create_five") {
			$quantity = 5;
		}
		preg_match("/\(([^)]+)\)/", $queryText, $matches);
		try {
			$selectItems = $pdo->prepare("SELECT * FROM items WHERE name LIKE :name");
			$selectItems->execute([":name" => trim($matches[1])]);
			$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
			$selectAlchemy = $pdo->prepare("SELECT * FROM alchemy WHERE item_id = :item_id");
			$selectAlchemy->execute([":item_id" => $rowItems["id"]]);
			$rowAlchemy = $selectAlchemy->fetch(PDO::FETCH_ASSOC);
			$selectIngredients = $pdo->prepare("SELECT * FROM ingredients WHERE item_id = :item_id AND type = 'Alchemy'");
			$selectIngredients->execute([":item_id" => $rowAlchemy["item_id"]]);
			$ingredientsList = [];
			while ($rowIngredients = $selectIngredients->fetch(PDO::FETCH_ASSOC)) {
				$selectItems = $pdo->prepare("SELECT * FROM items WHERE id = :id");
				$selectItems->execute([":id" => $rowIngredients["ingredient_id"]]);
				$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
				$ingredientsList[] = "x" . number_format($rowIngredients["quantity"] * $quantity) . " " . $rowItems["icon"] . " (" . $rowItems["name"] . ")";
			}
			$ingredients .= implode(", ", array_slice($ingredientsList, 0, -1)) . " and " . end($ingredientsList);
			$selectItems = $pdo->prepare("SELECT * FROM items WHERE name LIKE :name");
			$selectItems->execute([":name" => trim($matches[1])]);
			$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
			editMessageText($queryUserId, $queryMessageId, "ℹ️ <i>Are you sure you want to use $ingredients to craft x$quantity " . $rowItems["icon"] . " (" . $rowItems["name"] . ")?</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"✔️ (confirm)","callback_data":"potions_create_confirm"},{"text":"❌ (cancel)","callback_data":"potions_create_cancel"}]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

try {
	if ($queryData == "potions_create_confirm") {
		try {
			$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Potions (search)' AND user_id = :user_id");
			$selectUsersUtilities->execute([":user_id" => $queryUserId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
			preg_match_all('/x(\d+)\s+(\S+)\s+\(([^)]+)\)/', $queryText, $matches);
			$quantities = $matches[1];
			$icons = $matches[2];
			$ingredients = $matches[3];
			preg_match('/to craft x(\d+)\s+(\S+)\s+\(([^)]+)\)\?/', $queryText, $finalMatch);
			$requestedQuantity = intval($finalMatch[1]);
			$productIcon = $finalMatch[2];
			$productName = $finalMatch[3];
			$ingredients = array_filter($ingredients, function ($ingredient) use ($productName) {
				return trim($ingredient) !== trim($productName);
			});
			try {
				$selectItems = $pdo->prepare("SELECT id FROM items WHERE name = :name");
				$selectItems->execute([":name" => trim($productName)]);
				if(!$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC)) {
					exit;
				}
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
			$productId = $rowItems["id"];
			$canCraft = true;
			$craftedQuantity = 0;
			for ($i = 0; $i < count($ingredients); $i++) {
				try {
					$selectItems = $pdo->prepare("SELECT * FROM items WHERE name LIKE :name");
					$selectItems->execute([":name" => trim($ingredients[$i])]);
					$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
					if (!$rowItems) {
						$canCraft = false;
						break;
					}
					$ingredientId = $rowItems["id"];
					$selectIngredients = $pdo->prepare("SELECT * FROM ingredients WHERE item_id = :item_id AND ingredient_id = :ingredient_id");
					$selectIngredients->execute([
						":item_id" => $productId,
						":ingredient_id" => $ingredientId
					]);
					$rowIngredients = $selectIngredients->fetch(PDO::FETCH_ASSOC);	
					if (!$rowIngredients) {
						$canCraft = false;
						break;
					}
					$quantityPerUnit = intval($rowIngredients["quantity"]);
					$quantityNeeded = $quantityPerUnit * $requestedQuantity;
					$selectInventory = $pdo->prepare("SELECT quantity FROM users_inventory WHERE user_id = :user_id AND item_id = :item_id");
					$selectInventory->execute([":user_id" => $queryUserId, ":item_id" => $ingredientId]);
					$rowInventory = $selectInventory->fetch(PDO::FETCH_ASSOC);
					if (!$rowInventory || intval($rowInventory["quantity"]) < $quantityNeeded) {
						answerCallbackQuery($queryId, "🥹 You do not have enough (ingredients)!");
						$canCraft = false;
						break;
					}
					if ($rowItems["is_stackable"]) {
						removeItemFromInventory($pdo, $queryUserId, $ingredientId, $quantityNeeded);
					} else {
						$quantityToDeduct = $quantityNeeded;
						while ($quantityToDeduct > 0) {
							removeItemFromInventory($pdo, $queryUserId, $ingredientId, $quantityToDeduct);
							$quantityToDeduct--;
						}
					}
				} catch (Exception $exception) {
					systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			}
			if ($canCraft) {
				for ($i = 1; $i <= $requestedQuantity; $i++) {
					try {
						$selectAlchemy = $pdo->prepare("SELECT * FROM alchemy WHERE item_id = :item_id");
						$selectAlchemy->execute([":item_id" => $productId]);
						$rowAlchemy = $selectAlchemy->fetch(PDO::FETCH_ASSOC);
						$selectUsersAlchemy = $pdo->prepare("SELECT * FROM users_alchemy WHERE user_id = :user_id");
						$selectUsersAlchemy->execute([":user_id" => $queryUserId]);
						$rowUsersAlchemy = $selectUsersAlchemy->fetch(PDO::FETCH_ASSOC);
						$selectUsersPerks = $pdo->prepare("SELECT education_level, luck_level FROM users_perks WHERE user_id = :user_id");
						$selectUsersPerks->execute([":user_id" => $queryUserId]);
						$rowUsersPerks = $selectUsersPerks->fetch(PDO::FETCH_ASSOC);
						$formulaSuccessChance = $rowAlchemy["success_chance"]
						+ ($rowUsersAlchemy["level"] * 0.19)
						+ (log($rowUsersPerks["education_level"] + 1, 10) * 0.7)
						+ ($rowUsersPerks["luck_level"] * 0.05);
						$formulaSuccessChance = min($formulaSuccessChance, 100);
						if (rand(1, 100) <= $formulaSuccessChance) {
							$craftedQuantity++;
						}
					} catch (Exception $exception) {
						systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
						exit;
					}
				}
				$expSuccess = $craftedQuantity * (($rowUsersAlchemy["level"] * 0.8) + (log($rowUsersPerks["education_level"] + 1, 10) * 0.3));
				$expFailed = ($requestedQuantity - $craftedQuantity) * (($rowUsersAlchemy["level"] * 0.4) + (log($rowUsersPerks["education_level"] + 1, 10) * 0.2));
				$expGained = max(1, floor($expSuccess + $expFailed));
				if ($craftedQuantity > 0) {
					try {
						// Aggiungi esperienza guadagnata
						$updateExperience = $pdo->prepare("UPDATE users_alchemy SET experience = experience + :experience WHERE user_id = :user_id");
						$updateExperience->execute([":experience" => $expGained, ":user_id" => $queryUserId]);
						// Recupera i dati aggiornati dell'utente
						$selectUsersAlchemy = $pdo->prepare("SELECT * FROM users_alchemy WHERE user_id = :user_id");
						$selectUsersAlchemy->execute([":user_id" => $queryUserId]);
						$rowUsersAlchemy = $selectUsersAlchemy->fetch(PDO::FETCH_ASSOC);
						
						// Calcola l'esperienza richiesta per il livello corrente
						$experienceRequired = pow($rowUsersAlchemy["level"], 2.79);
						
						// Incrementa il livello finché l'esperienza è sufficiente
						if ($rowUsersAlchemy["experience"] >= $experienceRequired) {
							$updateLevel = $pdo->prepare("UPDATE users_alchemy SET level = level + 1, experience = 0 WHERE user_id = :user_id");
							$updateLevel->execute([":user_id" => $queryUserId]);
						}
	
						addItemToInventory($pdo, $queryUserId, $productId, $craftedQuantity);
						answerCallbackQuery($queryId, "😏 Successfully crafted x$craftedQuantity ($productName)!");
					} catch (Exception $exception) {
						systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
						exit;
					}
				} else {
					$updateUsersAlchemy = $pdo->prepare("UPDATE users_alchemy SET experience = experience + :experience WHERE user_id = :user_id");
					$updateUsersAlchemy->execute([":experience" => $expGained, ":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "🤧 All attemps was failed!");
				}
				editMessageText($queryUserId, $queryMessageId, $queryText, false, '&reply_markup={"inline_keyboard":[[{"text":"🔄 (re-purchase)","callback_data":"potions_create_confirm"}]],"resize_keyboard":true}');
			} else {
				answerCallbackQuery($queryId, "🤧 Not enough ingredients to craft $productIcon ($productName).");
				editMessageText($queryUserId, $queryMessageId, $queryText, false, '&reply_markup={"inline_keyboard":[[{"text":"🔄 (re-purchase)","callback_data":"potions_create_confirm"}]],"resize_keyboard":true}');
			}
			exit;
		}
	}
} catch (Exception) {
	exit;
}
