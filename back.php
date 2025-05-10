<?php
if ($text === "🔙 Go Back") {
	try {
		$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET result = NULL WHERE user_id = :user_id");
		$updateUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception) {
		exit;
	}
}

if ($text === "🔙 Go Back" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT * FROM users_utilities WHERE section IN ('Profile', 'Profile/Campfire', 'Profile/Attributes', 'Perks', 'Battles', 'Battles/Maps', 'Leaderboard', 'Shop', 'Dark Wanderer (search)', 'Profile/Gems', 'Profile/Inventory', 'Expeditions (search)', 'Alchemy', 'Potions', 'Blacksmith', 'Forge') AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Main Menu' WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			sendMessage($chatId, "🔙 Go Back to (<i>Main Menu</i>).", '&reply_markup={"keyboard":[["🔰 Profile", "🔺 Perks"],["⚔️ Battles", "🍄 Expeditions"],["⚒️ Blacksmith", "⚗️ Alchemy"],["👺 Dark Wanderer", "🏆 Leaderboard"],["💳 Shop"]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text === "🔙 Go Back" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT * FROM users_utilities WHERE section IN ('Shop/Games', 'Shop/Diamonds') AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Shop' WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			sendMessage($chatId, "🔙 Go Back to <i>Shop</i>.");
			sendMessage($chatId, "💳 [ <code>SHOP</code> ]\nAll purchases are subject to our <b>Terms of Service</b>. By proceeding you confirm that you have read these <a href='https://telegra.ph/terms-of-service-10-11'>Terms of Service</a>. Please review them before making a purchase.", true, false, false, '&reply_markup={"keyboard":[["😈 Darkness Pass", "💎 Diamonds"],["🎮 Games"],["🔙 Go Back"]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text === "🔙 Go Back" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Inventory (search)' AND user_id = :user_id");
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
			sendMessage($chatId, "🔙 Go Back to <i>Inventory</i>.", false, false, false, '&reply_markup={"keyboard":[["🎒 Inventory", "🦺 Equipment"],["✳️ Attributes", "⛺ Campfire"],["🥷🏻 Shadow Clone", "🔘 Gems"],["🔙 Go Back"]],"resize_keyboard":true}');
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
	}
}


if ($text === "🔙 Go Back" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Potions (search)' AND user_id = :user_id");
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
			sendMessage($chatId, "🔙 Go Back to <i>Alchemy</i>.");
			sendMessage($chatId, "⚗️ [ <code>ALCHEMY</code> ] (Level <b>" . $rowUsersAlchemy["level"] . "</b>)\nThe ancient art of transforming materials into powerful potions and rare artifacts, harnessing the remnants of the shattered suns.\n\nℹ️ <i>To advance to the next level, you need " . number_format($experienceRequired) . " (Experience), <a href='https://telegra.ph/expeditions-12-28-6'>here</a> is a complete list of the items that can be synthesize.</i>", true, false, false, '&reply_markup={"keyboard":[["🧪 Potions"],["🔙 Go Back"]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text === "🔙 Go Back" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Forge (search)' AND user_id = :user_id");
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
			sendMessage($chatId, "🔙 Go Back to <i>Blacksmith</i>.");
			sendMessage($chatId, "⚒️ [ <code>BLACKSMITH</code> ] (Level <b>" . $rowUsersBlacksmith["level"] . "</b>)\nMaster the art of forging legendary weapons and armor. Utilize mystical materials to create powerful artifacts.\n\nℹ️ <i>To advance to the next level, you need " . number_format($experienceRequired) . " (Experience). <a href='https://telegra.ph/blacksmith-items-12-28-6'>Here</a> is a complete list of items you can forge.</i>", true, false, false, '&reply_markup={"keyboard":[["⚙️ Forge"],["🔙 Go Back"]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text === "🔙 Go Back" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
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
			sendMessage($chatId, "🔙 Go Back to <i>Perks</i>.");
			sendMessage($chatId, "🔺 [ <code>PERKS</code> ]\nPerks are special abilities can provide a variety of benefits, such as increased ❤️ (<i>Health Points</i>), increase your ⚔️ (<i>Damage</i>) or boost your 🛡 (<i>Defense</i>).", false, false, false, '&reply_markup={"keyboard":[["🥊 Strength", "🧠 Intelligence"],["🛡 Endurance"],["📖 Education", "🎲 Luck"],["🔙 Go Back"]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}
