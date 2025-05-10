<?php
include_once("/var/www/html/back.php");

function addItemToInventory($pdo, $userId, $itemId, $quantity = 1) {
	$userId = $userId ?? $queryUserId;
	try {
		$selectItems = $pdo->prepare("SELECT is_stackable FROM items WHERE id = :id");
		$selectItems->execute([":id" => $itemId]);
		$isStackable = $selectItems->fetchColumn();
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage());
		exit;
	}
	if ($isStackable) {
		try {
			$selectUsersInventory = $pdo->prepare("SELECT * FROM users_inventory WHERE item_id = :item_id AND user_id = :user_id");
			$selectUsersInventory->execute([":item_id" => $itemId, ":user_id" => $userId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage());
			exit;
		}
		if ($rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC)) {
			try {
				$updateUsersInventory = $pdo->prepare("UPDATE users_inventory SET quantity = quantity + :quantity WHERE item_id = :item_id AND user_id = :user_id");
				$updateUsersInventory->execute([
					":quantity" => $quantity,
					":item_id" => $itemId,
					":user_id" => $userId
				]);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage());
				exit;
			}
		} else {
			try {
				$insertUsersInventory = $pdo->prepare("INSERT INTO users_inventory (user_id, item_id, quantity) VALUES (:user_id, :item_id, :quantity)");
				$insertUsersInventory->execute([
					":user_id" => $userId,
					":item_id" => $itemId,
					":quantity" => $quantity
				]);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage());
				exit;
			}
		}
	} else {
		try {
			$insertUsersInventory = $pdo->prepare("INSERT INTO users_inventory (user_id, item_id, quantity) VALUES (:user_id, :item_id, :quantity)");
			$insertUsersInventory->execute([
				":user_id" => $userId,
				":item_id" => $itemId,
				":quantity" => $quantity
			]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage());
			exit;
		}
	}
}

function removeItemFromInventory($pdo, $userId, $itemId, $quantity = 1) {
	$userId = $userId ?? $queryUserId;
	try {
		$selectItems = $pdo->prepare("SELECT is_stackable FROM items WHERE id = :id");
		$selectItems->execute([":id" => $itemId]);
		$isStackable = $selectItems->fetchColumn();
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage());
		exit;
	}
	if ($isStackable) {
		try {
			$selectUsersInventory = $pdo->prepare("SELECT * FROM users_inventory WHERE item_id = :item_id AND quantity > :quantity AND user_id = :user_id");
			$selectUsersInventory->execute([
				":quantity" => $quantity,
				":item_id" => $itemId, 
				":user_id" => $userId
			]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage());
			exit;
		}
		if ($rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC)) {
			try {
				$updateUsersInventory = $pdo->prepare("UPDATE users_inventory SET quantity = quantity - :quantity WHERE item_id = :item_id AND user_id = :user_id");
				$updateUsersInventory->execute([
					":quantity" => $quantity,
					":item_id" => $itemId,
					":user_id" => $userId
				]);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage());
				exit;
			}
		} else {
			try {
				$deleteUsersInventory = $pdo->prepare("DELETE FROM users_inventory WHERE item_id = :item_id AND user_id = :user_id");
				$deleteUsersInventory->execute([
					":user_id" => $userId,
					":item_id" => $itemId
				]);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage());
				exit;
			}
		}
	} else {
		try {
			$deleteUsersInventory = $pdo->prepare("DELETE FROM users_inventory WHERE item_id = :item_id AND user_id = :user_id");
			$deleteUsersInventory->execute([
				":user_id" => $userId,
				":item_id" => $itemId
			]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage());
			exit;
		}
	}
}

if ($text === "🎒 Inventory" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section IN ('Profile', 'Profile/Campfire', 'Profile/Attributes', 'Profile/Inventory', 'Shadow Clone') AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$resultPerPage = 10;
		$page = 1;
		$pageFirstResult = ($page - 1) * $resultPerPage;
		try {
			$selectUsersInventoryCount = $pdo->prepare("SELECT COUNT(*) as total FROM users_inventory WHERE user_id = :user_id");
			$selectUsersInventoryCount->execute([":user_id" => $userId]);
			$numUsersInventory = $selectUsersInventoryCount->fetch(PDO::FETCH_ASSOC)["total"];
			$selectUsersInventory = $pdo->prepare("
				SELECT i.name, i.icon, ui.quantity
				FROM users_inventory ui
				JOIN items i ON ui.item_id = i.id
				WHERE ui.user_id = :user_id
				LIMIT $pageFirstResult, $resultPerPage
			");
			$selectUsersInventory->execute([":user_id" => $userId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		while ($rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC)) {
			$string .= "- " . $rowUsersInventory["icon"]." x<b>".number_format($rowUsersInventory["quantity"])."</b> (".$rowUsersInventory["name"].")\n";
		}
		try {
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Profile/Inventory', result = 1 WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			if ($numUsersInventory > 0 ) {
				if ($numUsersInventory > $resultPerPage) {
					sendMessage($chatId, "🎒 [ <code>INVENTORY</code> ]\n" . $string, false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"⏩","callback_data":"inventory_page_up"}],[{"text":"🔍 (search)","callback_data":"inventory_search"}]],"resize_keyboard":true}');
				} else {
					sendMessage($chatId, "🎒 [ <code>INVENTORY</code> ]\n" . $string, false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"🔍 (search)","callback_data":"inventory_search"}]],"resize_keyboard":true}');
				}
			} else {
				sendMessage($chatId, "🎒 [ <code>INVENTORY</code> ]\n\n😐 Your (Inventory) is <i>empty</i>.", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"🔍 (search)","callback_data":"inventory_search"}]],"resize_keyboard":true}');
			}
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Inventory) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($queryData == "inventory_page_up") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Inventory' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$num = $rowUsersUtilities["result"] + 1;
		$resultPerPage = 10;
		$pageFirstResult = ($num - 1) * $resultPerPage;
		try {
			$selectUsersInventoryCount = $pdo->prepare("SELECT COUNT(*) as total FROM users_inventory WHERE user_id = :user_id");
			$selectUsersInventoryCount->execute([":user_id" => $userId]);
			$numUsersInventory = $selectUsersInventoryCount->fetch(PDO::FETCH_ASSOC)["total"];
			$selectUsersInventoryCount = $pdo->prepare("SELECT COUNT(*) as total FROM users_inventory WHERE user_id = :user_id");
			$selectUsersInventoryCount->execute([":user_id" => $userId]);
			$numUsersInventory = $selectUsersInventoryCount->fetch(PDO::FETCH_ASSOC)["total"];
			$selectUsersInventory = $pdo->prepare("
				SELECT i.name, i.icon, ui.quantity
				FROM users_inventory ui
				JOIN items i ON ui.item_id = i.id
				WHERE ui.user_id = :user_id
				LIMIT $pageFirstResult, $resultPerPage
			");
			$selectUsersInventory->execute([":user_id" => $userId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		while ($rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC)) {
			$string .= "- " . $rowUsersInventory["icon"]." x<b>".number_format($rowUsersInventory["quantity"])."</b> (".$rowUsersInventory["name"].")\n";
		}
		try {
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Profile/Inventory', result = :result WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":result" => $num, ":user_id" => $userId]);
			if (($numUsersInventory - $pageFirstResult) > $resultPerPage) {
				editMessageText($queryUserId, $queryMessageId, $string, false, '&reply_markup={"inline_keyboard":[[{"text":"⏩","callback_data":"inventory_page_up"},{"text":"⏪","callback_data":"inventory_page_down"}],[{"text":"🔍 (search)","callback_data":"inventory_search"}]],"resize_keyboard":true}');
			} else {
				editMessageText($queryUserId, $queryMessageId, $string, false, '&reply_markup={"inline_keyboard":[[{"text":"⏪","callback_data":"inventory_page_down"}],[{"text":"🔍 (search)","callback_data":"inventory_search"}]],"resize_keyboard":true}');
			}
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Inventory) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "inventory_page_down") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Inventory' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$num = $rowUsersUtilities["result"] - 1;
		$resultPerPage = 10;
		$pageFirstResult = ($num - 1) * $resultPerPage;
		try {
			$selectUsersInventoryCount = $pdo->prepare("SELECT COUNT(*) as total FROM users_inventory WHERE user_id = :user_id");
			$selectUsersInventoryCount->execute([":user_id" => $userId]);
			$numUsersInventory = $selectUsersInventoryCount->fetch(PDO::FETCH_ASSOC)["total"];
			$selectUsersInventoryCount = $pdo->prepare("SELECT COUNT(*) as total FROM users_inventory WHERE user_id = :user_id");
			$selectUsersInventoryCount->execute([":user_id" => $userId]);
			$numUsersInventory = $selectUsersInventoryCount->fetch(PDO::FETCH_ASSOC)["total"];
			$selectUsersInventory = $pdo->prepare("
				SELECT i.name, i.icon, ui.quantity
				FROM users_inventory ui
				JOIN items i ON ui.item_id = i.id
				WHERE ui.user_id = :user_id
				LIMIT $pageFirstResult, $resultPerPage
			");
			$selectUsersInventory->execute([":user_id" => $userId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		while ($rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC)) {
			$string .= "- " . $rowUsersInventory["icon"]." x<b>".number_format($rowUsersInventory["quantity"])."</b> (".$rowUsersInventory["name"].")\n";
		}
		try {
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Profile/Inventory', result = :result WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":result" => $num, ":user_id" => $userId]);
			if ($num == 1) {
				editMessageText($queryUserId, $queryMessageId, $string, false, '&reply_markup={"inline_keyboard":[[{"text":"⏩","callback_data":"inventory_page_up"}],[{"text":"🔍 (search)","callback_data":"inventory_search"}]],"resize_keyboard":true}');
			} else {
				editMessageText($queryUserId, $queryMessageId, $string, false, '&reply_markup={"inline_keyboard":[[{"text":"⏩","callback_data":"inventory_page_up"},{"text":"⏪","callback_data":"inventory_page_down"}],[{"text":"🔍 (search)","callback_data":"inventory_search"}]],"resize_keyboard":true}');
			}
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Inventory) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($text && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Inventory (search)' AND user_id = :user_id");
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
				$selectUsersInventory = $pdo->prepare("SELECT * FROM users_inventory WHERE item_id = :item_id AND user_id = :user_id LIMIT 1");
				$selectUsersInventory->execute([":item_id" => $rowItems["id"], ":user_id" => $userId]);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
			if ($rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC)) {
				$selectConsumables = $pdo->prepare("SELECT 1 FROM consumables WHERE item_id = :item_id");
				$selectConsumables->execute([":item_id" => $rowItems["id"]]);
				$rowConsumables = $selectConsumables->fetch(PDO::FETCH_ASSOC);
				$selectBlacksmith = $pdo->prepare("SELECT 1 FROM blacksmith WHERE item_id = :item_id");
				$selectBlacksmith->execute([":item_id" => $rowItems["id"]]);
				$rowBlacksmith = $selectBlacksmith->fetch(PDO::FETCH_ASSOC);
				$keyboard = '{"inline_keyboard":[';
				$row = [];
				if ($rowItems) {
					$row[] = '{"text":"🔍 (inspect)","callback_data":"inventory_inspect"}';
				}
				if ($rowConsumables) {
					$row[] = '{"text":"💊 (consume)","callback_data":"consumables_consume"}';
				}
				if ($rowBlacksmith) {
					$selectUsersEquipment = $pdo->prepare("SELECT * FROM users_equipment WHERE weapon = :item_id OR head = :item_id OR body = :item_id OR hands = :item_id OR legs = :item_id OR feet = :item_id");
					$selectUsersEquipment->execute([":item_id" => $rowItems["id"]]);
					if ($rowUsersEquipment = $selectUsersEquipment->fetch(PDO::FETCH_ASSOC)) {
						$row[] = '{"text":"❌ (remove)","callback_data":"equipment"}';
					} else {
						$row[] = '{"text":"✔️ (equip)","callback_data":"equipment"}';
					}
				}
				if (!empty($row)) {
					$keyboard .= '[' . implode(',', $row) . '],';
				}
				$keyboard = rtrim($keyboard, ',') . '],"resize_keyboard":true}';
				sendMessage($chatId, $rowItems["icon"] . " [ <code>" . strtoupper($rowItems["name"]) . "</code> ]\n" . $rowItems["description"], false, false, false, '&reply_markup=' . $keyboard);
			} else {
				try {
					sendMessage($chatId, "🤨 You do not have enough " . $rowItems["icon"] . " (" . $rowItems["name"] . ")");
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

if ($queryData == "inventory_search") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile/Inventory' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Inventory (search)' WHERE user_id = :user_id");
		$updateUsersUtilities->execute([":user_id" => $queryUserId]);
		sendMessage($queryUserId, "ℹ️ <i>Enter the name of the (item) you wish to interact.</i>", false, false, false, '&reply_markup={"keyboard":[["🔙 Go Back"]],"resize_keyboard":true}');
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Inventory) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "inventory_inspect") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Inventory (search)' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		preg_match("/\[([^\]]+)\]/", $queryText, $matches);
		$selectItems = $pdo->prepare("SELECT * FROM items WHERE name LIKE :name");
		$selectItems->execute([":name" => trim($matches[1])]);
		if ($rowItems = $selectItems->fetch(PDO::FETCH_ASSOC)) {
			answerCallbackQuery($queryId, "― INSPECT ―\n" . $rowItems["short_description"]);
		} else {
			try {
				answerCallbackQuery($queryId, "😅 The (item) was not found. Try again!");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Inventory) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}
