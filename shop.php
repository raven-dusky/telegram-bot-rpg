<?php
if ($text === "💳 Shop" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section IN ('Main Menu', 'Dark Wanderer', 'Expeditions') AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Shop' WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			sendMessage($chatId, "💳 [ <code>SHOP</code> ]\nAll purchases are subject to our <b>Terms of Service</b>. By proceeding you confirm that you have read these <a href='https://telegra.ph/terms-of-service-10-11'>Terms of Service</a>. Please review them before making a purchase.", true, false, false, '&reply_markup={"keyboard":[["😈 Darkness Pass", "💎 Diamonds"],["🎮 Games"],["🔙 Go Back"]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text === "🎮 Games" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Shop' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Shop/Games' WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			sendMessage($chatId, "🎮 [ <code>GAMES</code> ]\n➔ 🎲 Dice: Roll the dice and test your luck! 1-6 (<i>Diamonds</i>).\n➔ 🎯 Darts: Aim and throw the darts to hit the target! 1-6 (<i>Diamonds</i>).\n➔ 🎰 Slots: Spin the slots and win big prizes! 1-64 (<i>Diamonds</i>).\n\nℹ️ <i>Games reset every dat at</i> <b>19:00</b>.", false, false, false, '&reply_markup={"keyboard":[["🎲", "🎯", "🎰"],["🔙 Go Back"]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Shop) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text === "😈 Darkness Pass" && $chatType == "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Shop' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectPayments = $pdo->prepare("SELECT 1 FROM payments WHERE product_name = 'Darkness Pass (DP)' AND product_datetime >= NOW() OR product_datetime IS NULL AND user_id = :user_id ORDER BY payments.id DESC LIMIT 1");
			$selectPayments->execute([":user_id" => $userId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
		if ($rowPayments = $selectPayments->fetch(PDO::FETCH_ASSOC)) {
			try {
				sendMessage($chatId, "😈 [ <code>DARKNESS PASS</code> ]\nSuccumb to the corrupting forces of darkness — a sacrifice essential to ensure victory in the eternal struggle.\n\n- 💎 <b>300</b> Diamonds <i>(immediately)</i>\n- 🔋 +<b>375</b> <i>(Energy Points)</i>\n- ♦️ +<b>25%</b> EXP\n- 🪙 +<b>10%</b> Coins\n- 🥷🏻 +9% <i>Shadow Clone</i>\n- 🍄 -<b>50%</b> (Expeditions Time Reduction)\n- 🍄 <b>3 ➔ 5</b> (Maximum Expeditions Increased)\n- 🎰 Unlock Access to <i>Slots</i> Game\n\nℹ️ <i>Darkness Pass benefits are valid for 30 days.</i>");
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
		} else {
			try {
				sendMessage($chatId, "😈 [ <code>DARKNESS PASS</code> ]\nSuccumb to the corrupting forces of darkness — a sacrifice essential to ensure victory in the eternal struggle.\n\n- 💎 <b>300</b> Diamonds <i>(immediately)</i>\n- 🔋 +<b>375</b> <i>(Energy Points)</i>\n- ♦️ +<b>25%</b> EXP\n- 🪙 +<b>10%</b> Coins\n- 🥷🏻 +9% <i>Shadow Clone</i>\n- 🍄 -<b>50%</b> (Expeditions Time Reduction)\n- 🍄 <b>3 ➔ 5</b> (Maximum Expeditions Increased)\n- 🎰 Unlock Access to <i>Slots</i> Game\n\nℹ️ <i>Darkness Pass benefits are valid for 30 days.</i>");
				sendInvoice($chatId, 1.25, "😈 Darkness Pass (DP)", "This purchase provides access for a limited duration of (30 Days).");
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
		}
	}
}

if ($diceEmoji === "🎲" && $chatType == "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Shop/Games' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersGames = $pdo->prepare("SELECT 1 FROM users_games WHERE dice = 0 AND user_id = :user_id");
			$selectUsersGames->execute([":user_id" => $userId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
		if ($rowUsersGames = $selectUsersGames->fetch(PDO::FETCH_ASSOC)) {
			try {
				$pdo->beginTransaction();
				$updateUsersGames = $pdo->prepare("UPDATE users_games SET dice = 1 WHERE user_id = :user_id");
				$updateUsersGames->execute([":user_id" => $userId]);
				$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET diamonds = diamonds + :diamonds WHERE user_id = :user_id");
				$updateUsersProfiles->execute([":diamonds" => $diceValue, ":user_id" => $userId]);
				$pdo->commit();
				sendMessage($chatId, "🎲 You rolled the (Dice) and got <b>" . $diceValue . "</b> (<i>Diamonds</i>).");
				systemLogs($pdo, $userId, "INFO", "Dice rolled successfully, received +$diceValue (Diamonds).");
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
		} else {
			try {
				sendMessage($chatId, "🎲 You have already rolled the (Dice) today. Please try again tomorrow.", false);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Shop) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($diceEmoji === "🎯" && $chatType == "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Shop/Games' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersGames = $pdo->prepare("SELECT 1 FROM users_games WHERE darts = 0 AND user_id = :user_id");
			$selectUsersGames->execute([":user_id" => $userId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
		if ($rowUsersGames = $selectUsersGames->fetch(PDO::FETCH_ASSOC)) {
			try {
				$pdo->beginTransaction();
				$updateUsersGames = $pdo->prepare("UPDATE users_games SET darts = 1 WHERE user_id = :user_id");
				$updateUsersGames->execute([":user_id" => $userId]);
				$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET diamonds = diamonds + :diamonds WHERE user_id = :user_id");
				$updateUsersProfiles->execute([":diamonds" => $diceValue, ":user_id" => $userId]);
				$pdo->commit();
				sendMessage($chatId, "🎯 You rolled the (Darts) and got <b>" . $diceValue . "</b> (<i>Diamonds</i>).");
				systemLogs($pdo, $userId, "INFO", "Dice rolled successfully, received +$diceValue (Diamonds).");
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
		} else {
			try {
				sendMessage($chatId, "🎯 You have already rolled the (Darts) today. Please try again tomorrow.", false);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Shop) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($diceEmoji === "🎰" && $chatType == "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Shop/Games' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectPayments = $pdo->prepare("SELECT 1 FROM payments WHERE product_name = 'Darkness Pass (DP)' AND product_datetime >= NOW() OR product_datetime IS NULL AND user_id = :user_id ORDER BY payments.id DESC LIMIT 1");
			$selectPayments->execute([":user_id" => $userId]);
			$selectUsersGames = $pdo->prepare("SELECT 1 FROM users_games WHERE slots = 0 AND user_id = :user_id");
			$selectUsersGames->execute([":user_id" => $userId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
		if ($rowPayments = $selectPayments->fetch(PDO::FETCH_ASSOC)) {
			if ($rowUsersGames = $selectUsersGames->fetch(PDO::FETCH_ASSOC)) {
				try {
					$pdo->beginTransaction();
					$updateUsersGames = $pdo->prepare("UPDATE users_games SET slots = 1 WHERE user_id = :user_id");
					$updateUsersGames->execute([":user_id" => $userId]);
					$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET diamonds = diamonds + :diamonds WHERE user_id = :user_id");
					$updateUsersProfiles->execute([":diamonds" => $diceValue, ":user_id" => $userId]);
					$pdo->commit();
					sendMessage($chatId, "🎰 You rolled the (Slots) and got <b>" . $diceValue . "</b> (<i>Diamonds</i>).");
					systemLogs($pdo, $userId, "INFO", "Dice rolled successfully, received +$diceValue (Diamonds).");
				} catch (Exception $exception) {
					$pdo->rollBack();
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
					exit;
				}
			} else {
				try {
					sendMessage($chatId, "🎰 You have already rolled the (Slots) today. Please try again tomorrow.", false);
				} catch (Exception $exception) {
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
					exit;
				}
			}
		} else {
			try {
				sendMessage($chatId, "😈 You need <b>Darkness Pass</b>.");
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
				exit;
			}
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Shop) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text === "💎 Diamonds" && $chatType == "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Shop' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Shop/Diamonds' WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			sendMessage($chatId, "💎 [ <code>DIAMONDS</code> ]\nDiamonds are the essence of power and progression in the realm. Use them to unlock exclusive items, speed up your journey, and gain access to special abilities.\n\nℹ️ <i>The more diamonds you buy, the higher the bonus percentage (<b>%</b>) added to your purchase, giving you even greater value with larger packs!</i>", false, false, false, '&reply_markup={"keyboard":[["💎 1,000","💎 5,000","💎 12,000"],["💎 25,000","💎 50,000"],["🔙 Go Back"]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Shop) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text === "💎 1,000" && $chatType == "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Shop/Diamonds' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			sendInvoice($chatId, 1, "💎 1,000 (Diamonds)", "You received 1,000 Diamonds plus a 5% bonus (50 Diamonds).");
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Shop) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text === "💎 5,000" && $chatType == "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Shop/Diamonds' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			sendInvoice($chatId, 5, "💎 5,000 (Diamonds)", "You received 5,000 Diamonds plus a 10% bonus (500 Diamonds).");
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Shop) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text === "💎 12,000" && $chatType == "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Shop/Diamonds' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			sendInvoice($chatId, 12, "💎 12,000 (Diamonds)", "You received 12,000 Diamonds plus a 15% bonus (1,800 Diamonds).");
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Shop) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text === "💎 25,000" && $chatType == "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Shop/Diamonds' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			sendInvoice($chatId, 25, "💎 25,000 (Diamonds)", "You received 25,000 Diamonds plus a 20% bonus (5,000 Diamonds).");
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Shop) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($text === "💎 50,000" && $chatType == "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Shop/Diamonds' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			sendInvoice($chatId, 50, "💎 50,000 (Diamonds)", "You received 50,000 Diamonds plus a 25% bonus (12,500 Diamonds).");
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Shop) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}
