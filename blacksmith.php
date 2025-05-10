<?php
include_once("/var/www/html/inventory.php");

if ($text === "⚒️ Blacksmith" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Main Menu' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersBlacksmith = $pdo->prepare("SELECT * FROM users_blacksmith WHERE user_id = :user_id");
			$selectUsersBlacksmith->execute([":user_id" => $userId]);
			$rowUsersBlacksmith = $selectUsersBlacksmith->fetch(PDO::FETCH_ASSOC);
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Blacksmith' WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			$experienceRequired = pow($rowUsersBlacksmith["level"], 2.79);
			$experienceRequired = $experienceRequired - $rowUsersBlacksmith["experience"];
			sendMessage($chatId, "⚒️ [ <code>BLACKSMITH</code> ] (Level <b>" . $rowUsersBlacksmith["level"] . "</b>)\nForge legendary weapons and armor using mystical materials harnessed from the remnants of shattered suns.\n\nℹ️ <i>To advance to the next level, you need " . number_format($experienceRequired) . " (Experience). <a href='https://telegra.ph/blacksmith-01-02-7'>Here</a> is a complete list of items you can forge.</i>", true, false, false, '&reply_markup={"keyboard":[["⚙️ Forge"],["🔙 Go Back"]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Blacksmith) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text === "⚙️ Forge" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Blacksmith' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
		if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Forge' WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			sendMessage($chatId, "⚙️ [ <code>FORGE</code> ]\nDiscover the art of crafting legendary weapons and armor. Use the search button below to find specific blueprints or materials.", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"🔍 (search)","callback_data":"forge_search"}]]}');
		} else {
			try {
				sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Blacksmith) to access those options.");
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
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Forge (search)' AND user_id = :user_id");
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
			$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
			$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
		if ($rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC)) {
			try {
				$selectIngredients = $pdo->prepare("SELECT * FROM ingredients WHERE item_id = :item_id AND type = 'Blacksmith'");
				$selectIngredients->execute([":item_id" => $rowBlacksmith["item_id"]]);
				$ingredientsList = [];
				while ($rowIngredients = $selectIngredients->fetch(PDO::FETCH_ASSOC)) {
					$selectItems = $pdo->prepare("SELECT * FROM items WHERE id = :id");
					$selectItems->execute([":id" => $rowIngredients["ingredient_id"]]);
					$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
					$ingredientsList[] = "x" . number_format($rowIngredients["quantity"]) . " " . $rowItems["icon"] . " (" . $rowItems["name"] . ")";
				}
				$ingredients = "The materials required for forging are: " . implode(", ", array_slice($ingredientsList, 0, -1)) . " and " . end($ingredientsList) . ".";
				$selectItems = $pdo->prepare("SELECT * FROM items WHERE name = :name");
				$selectItems->execute([":name" => $text]);
				$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
				$selectUsersBlacksmith = $pdo->prepare("SELECT * FROM users_blacksmith WHERE user_id = :user_id");
				$selectUsersBlacksmith->execute([":user_id" => $userId]);
				$rowUsersBlacksmith = $selectUsersBlacksmith->fetch(PDO::FETCH_ASSOC);
				$formulaSuccessChance = $rowBlacksmith["success_chance"]
					+ ($rowUsersBlacksmith["level"] * 0.15);
				$formulaSuccessChance = min($formulaSuccessChance, 100);
				sendMessage($chatId, $rowItems["icon"] . " [ <code>" . strtoupper($rowItems["name"]) . "</code> ]\n" . $rowItems["description"] . "\n\nℹ️ <i>$ingredients The chance of successfully forging this item is " . number_format($formulaSuccessChance, 2) . "%.</i>", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"✔️ (forge)","callback_data":"forge_create"}]],"resize_keyboard":true}');
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

if ($queryData == "forge_search") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Forge' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Forge (search)' WHERE user_id = :user_id");
		$updateUsersUtilities->execute([":user_id" => $queryUserId]);
		sendMessage($queryUserId, "ℹ️ <i>Enter the name of the (item) you wish to forge.</i>", false, false, false, '&reply_markup={"keyboard":[["🔙 Go Back"]],"resize_keyboard":true}');
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to Forge to access this option.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "forge_create") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Forge (search)' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		preg_match("/\[([^\]]+)\]/", $queryText, $matches);
		try {
			$selectItems = $pdo->prepare("SELECT * FROM items WHERE name LIKE :name");
			$selectItems->execute([":name" => trim($matches[1])]);
			$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
			$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
			$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
			$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
			$selectIngredients = $pdo->prepare("SELECT * FROM ingredients WHERE item_id = :item_id AND type = 'Blacksmith'");
			$selectIngredients->execute([":item_id" => $rowBlacksmith["item_id"]]);
			$ingredientsList = [];
			while ($rowIngredients = $selectIngredients->fetch(PDO::FETCH_ASSOC)) {
				$selectItems = $pdo->prepare("SELECT * FROM items WHERE id = :id");
				$selectItems->execute([":id" => $rowIngredients["ingredient_id"]]);
				$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
				$ingredientsList[] = "x" . number_format($rowIngredients["quantity"]) . " " . $rowItems["icon"] . " (" . $rowItems["name"] . ")";
			}
			$ingredients = implode(", ", array_slice($ingredientsList, 0, -1)) . " and " . end($ingredientsList);
			$selectItems = $pdo->prepare("SELECT * FROM items WHERE name LIKE :name");
			$selectItems->execute([":name" => trim($matches[1])]);
			$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
			editMessageText($queryUserId, $queryMessageId, "ℹ️ <i>Are you sure you want to use $ingredients to forge x1 " . $rowItems["icon"] . " (" . $rowItems["name"] . ")?</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"✔️ (confirm)","callback_data":"forge_create_confirm"},{"text":"❌ (cancel)","callback_data":"forge_create_cancel"}]]}');
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

try {
	if ($queryData == "forge_create_confirm") {
		try {
			$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Forge (search)' AND user_id = :user_id");
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
			preg_match('/to forge x(\d+)\s+(\S+)\s+\(([^)]+)\)\?/', $queryText, $finalMatch);
			$requestedQuantity = intval($finalMatch[1]);
			$productIcon = $finalMatch[2];
			$productName = $finalMatch[3];
			$ingredients = array_filter($ingredients, function ($ingredient) use ($productName) {
				return trim($ingredient) !== trim($productName);
			});
			try {
				$selectItems = $pdo->prepare("SELECT id FROM items WHERE name = :name");
				$selectItems->execute([":name" => trim($productName)]);
				if (!$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC)) {
					exit;
				}
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
			$productId = $rowItems["id"];
			$canForge = true;
			$craftedQuantity = 0;
			for ($i = 0; $i < count($ingredients); $i++) {
				try {
					$selectItems = $pdo->prepare("SELECT * FROM items WHERE name LIKE :name");
					$selectItems->execute([":name" => trim($ingredients[$i])]);
					$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
					if (!$rowItems) {
						$canForge = false;
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
						$canForge = false;
						break;
					}
					$quantityPerUnit = intval($rowIngredients["quantity"]);
					$quantityNeeded = $quantityPerUnit * $requestedQuantity;
					$selectInventory = $pdo->prepare("SELECT quantity FROM users_inventory WHERE user_id = :user_id AND item_id = :item_id");
					$selectInventory->execute([":user_id" => $queryUserId, ":item_id" => $ingredientId]);
					$rowInventory = $selectInventory->fetch(PDO::FETCH_ASSOC);
					if (!$rowInventory || intval($rowInventory["quantity"]) < $quantityNeeded) {
						answerCallbackQuery($queryId, "🥹 You do not have enough (ingredients)!");
						$canForge = false;
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
			if ($canForge) {
				for ($i = 1; $i <= $requestedQuantity; $i++) {
					try {
						$selectBlacksmith = $pdo->prepare("SELECT * FROM blacksmith WHERE item_id = :item_id");
						$selectBlacksmith->execute([":item_id" => $productId]);
						$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
						$selectUsersBlacksmith = $pdo->prepare("SELECT * FROM users_blacksmith WHERE user_id = :user_id");
						$selectUsersBlacksmith->execute([":user_id" => $queryUserId]);
						$rowUsersBlacksmith = $selectUsersBlacksmith->fetch(PDO::FETCH_ASSOC);
						$formulaSuccessChance = $rowBlacksmith["success_chance"]
							+ ($rowUsersBlacksmith["level"] * 0.15);
						$formulaSuccessChance = min($formulaSuccessChance, 100);
						if (rand(1, 100) <= $formulaSuccessChance) {
							$craftedQuantity++;
						}
					} catch (Exception $exception) {
						systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
						exit;
					}
				}
				$expSuccess = $craftedQuantity * (($rowUsersBlacksmith["level"] * 0.8) + 5);
				$expFailed = ($requestedQuantity - $craftedQuantity) * (($rowUsersBlacksmith["level"] * 0.4) + 2);
				$expGained = max(1, floor($expSuccess + $expFailed));
				if ($craftedQuantity > 0) {
					try {
						$updateExperience = $pdo->prepare("UPDATE users_blacksmith SET experience = experience + :experience WHERE user_id = :user_id");
						$updateExperience->execute([":experience" => $expGained, ":user_id" => $queryUserId]);
						addItemToInventory($pdo, $queryUserId, $productId, $craftedQuantity);
						answerCallbackQuery($queryId, "😏 Successfully forged x$craftedQuantity ($productName)!");
					} catch (Exception $exception) {
						systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
						exit;
					}
				} else {
					$updateUsersBlacksmith = $pdo->prepare("UPDATE users_blacksmith SET experience = experience + :experience WHERE user_id = :user_id");
					$updateUsersBlacksmith->execute([":experience" => $expGained, ":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "🤧 All attempts failed!");
				}
			} else {
				answerCallbackQuery($queryId, "🤧 Not enough ingredients to forge $productIcon ($productName).");
			}
		} else {
			answerCallbackQuery($queryId, "🚫 You're not in the correct section!");
		}
	}
} catch (Exception $exception) {
	systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
	exit;
}
