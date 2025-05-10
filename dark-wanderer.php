<?php
include_once("/var/www/html/inventory.php");

if ($text === "👺 Dark Wanderer" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section IN ('Main Menu', 'Dark Wanderer', 'Expeditions') AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Dark Wanderer' WHERE user_id = :user_id");
		$updateUsersUtilities->execute([":user_id" => $userId]);
		sendMessage($chatId, "👺 [ <code>DARK WANDERER</code> ]\nA mysterious merchant from the abyss of Evermel. He appears in towns, offering common, rare and powerful items in exchange for 🪙 (<b>Coins</b>) and 💎 (<b>Diamonds</b>).\n\nℹ️ <i><a href='https://telegra.ph/dark-wanderer-12-26'>Here</a> is a complete list of the items you can buy.</i>", true, false, false, '&reply_markup={"inline_keyboard":[[{"text":"🔍 (search)","callback_data":"dark_wanderer_search"}]],"resize_keyboard":true}');
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Dark Wanderer) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text && $chatType == "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Dark Wanderer (search)' AND user_id = :user_id");
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
				$selectDarkWanderer = $pdo->prepare("SELECT * FROM dark_wanderer WHERE item_id = :item_id");
				$selectDarkWanderer->execute([":item_id" => $rowItems["id"]]);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
			if ($rowDarkWanderer = $selectDarkWanderer->fetch(PDO::FETCH_ASSOC)) {
				$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET result = :result WHERE user_id = :user_id");
				$updateUsersUtilities->execute([":result" => $rowDarkWanderer["item_id"], ":user_id" => $userId]);
				$icon = ($rowDarkWanderer["currency"] === "Coins") ? "🪙" : (($rowDarkWanderer["currency"] === "Diamonds") ? "💎" : '');
				$price = ($rowDarkWanderer["currency"] === "Coins") ? "🪙 " . number_format($rowDarkWanderer["price"], 2, ".", "") . " (Coins)." : (($rowDarkWanderer["currency"] === "Diamonds") ? "💎 " . intval($rowDarkWanderer["price"]) . " (Diamonds)." : '');
				sendMessage($chatId, "ℹ️ <i>How many " . $rowItems["icon"] . " (" . $rowItems["name"] . ") do you want to buy? Each costs " . $price . "</i>", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"x1 (buy)","callback_data":"dark_wanderer_buy_one"},{"text":"x2 (buy)","callback_data":"dark_wanderer_buy_two"},{"text":"x3 (buy)","callback_data":"dark_wanderer_buy_three"}],[{"text":"x4 (buy)","callback_data":"dark_wanderer_buy_four"},{"text":"x5 (buy)","callback_data":"dark_wanderer_buy_five"}]],"resize_keyboard":true}');
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

if ($queryData == "dark_wanderer_search") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Dark Wanderer' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Dark Wanderer (search)' WHERE user_id = :user_id");
		$updateUsersUtilities->execute([":user_id" => $queryUserId]);
		sendMessage($queryUserId, "ℹ️ <i>Enter the name of the (item) you wish to buy.</i>", false, false, false, '&reply_markup={"keyboard":[["🔙 Go Back"]],"resize_keyboard":true}');
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Dark Wanderer) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

if ($queryData == "dark_wanderer_buy_one" || $queryData == "dark_wanderer_buy_two" || $queryData == "dark_wanderer_buy_three" || $queryData == "dark_wanderer_buy_four" || $queryData == "dark_wanderer_buy_five") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT result FROM users_utilities WHERE section = 'Dark Wanderer (search)' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		preg_match("/\(([^)]+)\)/", $queryText, $matches);
		try {
			$selectItems = $pdo->prepare("SELECT * FROM items WHERE name LIKE :name");
			$selectItems->execute([":name" => trim($matches[1])]);
			$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
			$selectDarkWanderer = $pdo->prepare("SELECT * FROM dark_wanderer WHERE item_id = :item_id");
			$selectDarkWanderer->execute([":item_id" => $rowItems["id"]]);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		if ($rowDarkWanderer = $selectDarkWanderer->fetch(PDO::FETCH_ASSOC)) {
			$quantity = 1;
			if ($queryData == "dark_wanderer_buy_two") {
				$quantity = 2;
			} elseif ($queryData == "dark_wanderer_buy_three") {
				$quantity = 3;
			} elseif ($queryData == "dark_wanderer_buy_four") {
				$quantity = 4;
			} elseif ($queryData == "dark_wanderer_buy_five") {
				$quantity = 5;
			}
			$priceTotal = $rowDarkWanderer["price"] * $quantity;
			$price = ($rowDarkWanderer["currency"] === "Coins") ? "🪙 " . number_format($priceTotal, 2, ".", ",") . " (Coins)": "💎 " . number_format($priceTotal) . " (Diamonds)";
			editMessageText($queryUserId, $queryMessageId, "ℹ️ <i>Are you sure you want to spend " . $price . " to buy " . $rowItems["icon"] . " (" . $rowItems["name"] . ")?</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"✔️ (confirm)","callback_data":"dark_wanderer_buy_confirm"},{"text":"❌ (cancel)","callback_data":"dark_wanderer_cancel"}]],"resize_keyboard":true}');
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
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Dark Wanderer) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}

try {
	if ($queryData == "dark_wanderer_buy_confirm") {
		try {
			$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Dark Wanderer (search)' AND user_id = :user_id");
			$selectUsersUtilities->execute([":user_id" => $queryUserId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
		if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
			preg_match("/spend .*? (\d+(?:\.\d+)?) \(([^)]+)\) to buy .*? \(([^)]+)\)\?/", $queryText, $matches);
			$priceFromMessage = floatval(str_replace(",", "", $matches[1]));
			try {
				$selectItems = $pdo->prepare("SELECT * FROM items WHERE name LIKE :name");
				$selectItems->execute([":name" => trim($matches[3])]);
				$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
				$selectDarkWanderer = $pdo->prepare("SELECT * FROM dark_wanderer WHERE item_id = :item_id");
				$selectDarkWanderer->execute([":item_id" => $rowItems["id"]]);
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
			if ($rowDarkWanderer = $selectDarkWanderer->fetch(PDO::FETCH_ASSOC)) {
				try {
					$basePrice = floatval($rowDarkWanderer["price"]);
					if ($priceFromMessage == $basePrice) {
						$quantity = 1;
					} elseif ($priceFromMessage > $basePrice) {
						$quantity = intval($priceFromMessage / $basePrice);
					} else {
						systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
						exit;
					}
					$selectUsersProfiles = $pdo->prepare("SELECT * FROM users_profiles WHERE user_id = :user_id");
					$selectUsersProfiles->execute([":user_id" => $queryUserId]);
					$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
					if ($rowDarkWanderer["currency"] == "Coins" && $rowUsersProfiles["coins"] >= $priceFromMessage) {
						$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET coins = coins - :coins WHERE user_id = :user_id");
						$updateUsersProfiles->execute([":coins" => $priceFromMessage, ":user_id" => $queryUserId]);
						addItemToInventory($pdo, $queryUserId, $rowDarkWanderer["item_id"], $quantity);
						answerCallbackQuery($queryId, "🥰 You have purchased " . $quantity . "x " . $rowItems["icon"] . " (" . trim($matches[3]) . ")!");
						editMessageReplyMarkup($queryUserId, $queryMessageId, '&reply_markup={"inline_keyboard":[[{"text":"🔄 (re-purchase)","callback_data":"dark_wanderer_buy_confirm"}]],"resize_keyboard":true}');
					} elseif ($rowDarkWanderer["currency"] == "Diamonds" && $rowUsersProfiles["diamonds"] >= $priceFromMessage) {
						$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET diamonds = diamonds - :diamonds WHERE user_id = :user_id");
						$updateUsersProfiles->execute([":diamonds" => $priceFromMessage, ":user_id" => $queryUserId]);
						addItemToInventory($pdo, $queryUserId, $rowDarkWanderer["item_id"], $quantity);
						answerCallbackQuery($queryId, "😍 You have purchased " . $quantity . "x " . $rowItems["icon"] . " (" . trim($matches[3]) . ")!");
						editMessageReplyMarkup($queryUserId, $queryMessageId, '&reply_markup={"inline_keyboard":[[{"text":"🔄 (re-purchase)","callback_data":"dark_wanderer_buy_confirm"}]],"resize_keyboard":true}');
					} else {
						answerCallbackQuery($queryId, "🤨 You do not have enough (" . $rowDarkWanderer["currency"] . ").");
					}
				} catch (Exception $exception) {
					exit;
				}
			} else {
				answerCallbackQuery($queryId, "😅 The (item) was not found. Try again!");
			}
		} else {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Dark Wanderer) to access those options.");
		}
	}
} catch (Exception) {
	exit;
}

if ($queryData == "dark_wanderer_cancel") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT result FROM users_utilities WHERE section = 'Dark Wanderer (search)' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Dark Wanderer' WHERE user_id = :user_id");
		$updateUsersUtilities->execute([":user_id" => $queryUserId]);
		editMessageText($queryUserId, $queryMessageId, "👺 [ <code>DARK WANDERER</code> ]\nA mysterious merchant from the abyss of Evermel. He appears in towns, offering common, rare and powerful items in exchange for 🪙 (<b>Coins</b>) and 💎 (<b>Diamonds</b>).", false, '&reply_markup={"inline_keyboard":[[{"text":"🔍 (search)","callback_data":"dark_wanderer_search"}]],"resize_keyboard":true}');
	} else {
		try {
			answerCallbackQuery($queryId, "🚫 Oops! You’re in a different section. Please return to (Dark Wanderer) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}
	}
}
