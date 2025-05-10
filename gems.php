<?php
include_once("/var/www/html/inventory.php");

if ($text === "🔘 Gems" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section IN ('Profile', 'Profile/Campfire', 'Profile/Attributes', 'Profile/Inventory') AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersGems = $pdo->prepare("SELECT * FROM users_gems WHERE user_id = :user_id");
			$selectUsersGems->execute([":user_id" => $userId]);
			$rowUsersGems = $selectUsersGems->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
		try {
			sendMessage($chatId, "🔘 [ <code>GEMS</code> ]\nPrecious stones mined by the miners of <i>Abandoned Mines</i>. These precious gems are indeed very valuable and, since the miners are very greedy, their price is usually not too affordable.\n\n(🔴) Crimson Gemstone ➔ " . $rowUsersGems["crimson_gemstone"] . "\n(🟠) Amber Crystal ➔ " . $rowUsersGems["amber_crystal"] . "\n(🟤) Sapphire Gemstone ➔ " . $rowUsersGems["sapphire_gemstone"] . "\n(🟣) Amethyst Crystal ➔ " . $rowUsersGems["amethyst_crystal"] . "\n(🟢) Emerald Gemstone ➔ " . $rowUsersGems["emerald_gemstone"] . "\n(⚫️) Obsidian Shard ➔ " . $rowUsersGems["obsidian_shard"] . "\n(🔵) Quartz Gemstone ➔ " . $rowUsersGems["quartz_gemstone"] . "\n(🟡) Topaz Gem ➔ " . $rowUsersGems["topaz_gem"] . "\n(⚪️) Ivory Crystal ➔ " . $rowUsersGems["ivory_crystal"] . "\n\nℹ️ <i>Press the button below to embed the corresponding gem.</i>", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"(🔴)","callback_data":"gems_crimson_gemstone"},{"text":"(🟠)","callback_data":"gems_amber_crystal"},{"text":"(🟤)","callback_data":"gems_sapphire_gemstone"},{"text":"(🟣)","callback_data":"gems_amethyst_crystal"}],[{"text":"(🟢)","callback_data":"gems_emerald_gemstone"},{"text":"(⚫️)","callback_data":"gems_obsidian_shard"},{"text":"(🔵)","callback_data":"gems_quartz_gemstone"},{"text":"(🟡)","callback_data":"gems_topaz_gem"}],[{"text":"(⚪️)","callback_data":"gems_ivory_crystal"}]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	} else {
		try {
			sendMessage($chatId, "🚫 Oops! You’re in a different section. Please return to (Gems) to access those options.");
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}

if ($queryData == "gems_crimson_gemstone") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersInventory = $pdo->prepare("SELECT * FROM users_inventory WHERE quantity >= 1 AND item_id = :item_id AND user_id = :user_id");
			$selectUsersInventory->execute([":item_id" => 9, ":user_id" => $queryUserId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}			
		if ($rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC)) {
			try {
				$selectUsersGems = $pdo->prepare("SELECT * FROM users_gems WHERE user_id = :user_id");
				$selectUsersGems->execute([":user_id" => $queryUserId]);
				$rowUsersGems = $selectUsersGems->fetch(PDO::FETCH_ASSOC);
				$selectUsersProfiles = $pdo->prepare("SELECT * FROM users_profiles WHERE user_id = :user_id");
				$selectUsersProfiles->execute([":user_id" => $queryUserId]);
				$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
			if ($rowUsersGems["crimson_gemstone"] < $rowUsersProfiles["level"] * 2) {
				try {
					$pdo->beginTransaction();
					$updateUsersGems = $pdo->prepare("UPDATE users_gems SET crimson_gemstone = crimson_gemstone + :crimson_gemstone WHERE user_id = :user_id");
					$updateUsersGems->execute([":crimson_gemstone" => 1, ":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET max_health_points = max_health_points + :max_health_points WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":max_health_points" => 24, ":user_id" => $queryUserId]);
					removeItemFromInventory($pdo, $queryUserId, 9);
					$pdo->commit();
					editMessageText($queryUserId, $queryMessageId, "🔘 [ <code>GEMS</code> ]\nPrecious stones mined by the miners of <i>Abandoned Mines</i>. These precious gems are indeed very valuable and, since the miners are very greedy, their price is usually not too affordable.\n\n(🔴) Crimson Gemstone ➔ " . ($rowUsersGems["crimson_gemstone"] + 1) . "\n(🟠) Amber Crystal ➔ " . $rowUsersGems["amber_crystal"] . "\n(🟤) Sapphire Gemstone ➔ " . $rowUsersGems["sapphire_gemstone"] . "\n(🟣) Amethyst Crystal ➔ " . $rowUsersGems["amethyst_crystal"] . "\n(🟢) Emerald Gemstone ➔ " . $rowUsersGems["emerald_gemstone"] . "\n(⚫️) Obsidian Shard ➔ " . $rowUsersGems["obsidian_shard"] . "\n(🔵) Quartz Gemstone ➔ " . $rowUsersGems["quartz_gemstone"] . "\n(🟡) Topaz Gem ➔ " . $rowUsersGems["topaz_gem"] . "\n(⚪️) Ivory Crystal ➔ " . $rowUsersGems["ivory_crystal"] . "\n\nℹ️ <i>Press the button below to embed the corresponding gem.</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"(🔴)","callback_data":"gems_crimson_gemstone"},{"text":"(🟠)","callback_data":"gems_amber_crystal"},{"text":"(🟤)","callback_data":"gems_sapphire_gemstone"},{"text":"(🟣)","callback_data":"gems_amethyst_crystal"}],[{"text":"(🟢)","callback_data":"gems_emerald_gemstone"},{"text":"(⚫️)","callback_data":"gems_obsidian_shard"},{"text":"(🔵)","callback_data":"gems_quartz_gemstone"},{"text":"(🟡)","callback_data":"gems_topaz_gem"}],[{"text":"(⚪️)","callback_data":"gems_ivory_crystal"}]],"resize_keyboard":true}');
				} catch (Exception $exception) {
					$pdo->rollBack();
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			} else {
				try {
					answerCallbackQuery($queryId, "🥴 You have reach the (🔴) - (Crimson Gemstone) limit.");
				} catch (Exception $exception) {
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			}
		} else {
			try {
				answerCallbackQuery($queryId, "🤔 You don't have enough (🔴) - (Crimson Gemstone).");
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	}
}

if ($queryData == "gems_amber_crystal") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersInventory = $pdo->prepare("SELECT * FROM users_inventory WHERE quantity >= 1 AND item_id = :item_id AND user_id = :user_id");
			$selectUsersInventory->execute([":item_id" => 10, ":user_id" => $queryUserId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}			
		if ($rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC)) {
			try {
				$selectUsersGems = $pdo->prepare("SELECT * FROM users_gems WHERE user_id = :user_id");
				$selectUsersGems->execute([":user_id" => $queryUserId]);
				$rowUsersGems = $selectUsersGems->fetch(PDO::FETCH_ASSOC);
				$selectUsersProfiles = $pdo->prepare("SELECT * FROM users_profiles WHERE user_id = :user_id");
				$selectUsersProfiles->execute([":user_id" => $queryUserId]);
				$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
			if ($rowUsersGems["amber_crystal"] < $rowUsersProfiles["level"] * 2) {
				try {
					$pdo->beginTransaction();
					$updateUsersGems = $pdo->prepare("UPDATE users_gems SET amber_crystal = amber_crystal + :amber_crystal WHERE user_id = :user_id");
					$updateUsersGems->execute([":amber_crystal" => 1, ":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET physical_damage = physical_damage + :physical_damage WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":physical_damage" => 13, ":user_id" => $queryUserId]);
					removeItemFromInventory($pdo, $queryUserId, 10);
					$pdo->commit();
					editMessageText($queryUserId, $queryMessageId, "🔘 [ <code>GEMS</code> ]\nPrecious stones mined by the miners of <i>Abandoned Mines</i>. These precious gems are indeed very valuable and, since the miners are very greedy, their price is usually not too affordable.\n\n(🔴) Crimson Gemstone ➔ " . $rowUsersGems["crimson_gemstone"] . "\n(🟠) Amber Crystal ➔ " . ($rowUsersGems["amber_crystal"] + 1) . "\n(🟤) Sapphire Gemstone ➔ " . $rowUsersGems["sapphire_gemstone"] . "\n(🟣) Amethyst Crystal ➔ " . $rowUsersGems["amethyst_crystal"] . "\n(🟢) Emerald Gemstone ➔ " . $rowUsersGems["emerald_gemstone"] . "\n(⚫️) Obsidian Shard ➔ " . $rowUsersGems["obsidian_shard"] . "\n(🔵) Quartz Gemstone ➔ " . $rowUsersGems["quartz_gemstone"] . "\n(🟡) Topaz Gem ➔ " . $rowUsersGems["topaz_gem"] . "\n(⚪️) Ivory Crystal ➔ " . $rowUsersGems["ivory_crystal"] . "\n\nℹ️ <i>Press the button below to embed the corresponding gem.</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"(🔴)","callback_data":"gems_crimson_gemstone"},{"text":"(🟠)","callback_data":"gems_amber_crystal"},{"text":"(🟤)","callback_data":"gems_sapphire_gemstone"},{"text":"(🟣)","callback_data":"gems_amethyst_crystal"}],[{"text":"(🟢)","callback_data":"gems_emerald_gemstone"},{"text":"(⚫️)","callback_data":"gems_obsidian_shard"},{"text":"(🔵)","callback_data":"gems_quartz_gemstone"},{"text":"(🟡)","callback_data":"gems_topaz_gem"}],[{"text":"(⚪️)","callback_data":"gems_ivory_crystal"}]],"resize_keyboard":true}');
				} catch (Exception $exception) {
					$pdo->rollBack();
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			} else {
				try {
					answerCallbackQuery($queryId, "🥴 You have reach the (🟠) - (Amber Crystal) limit.");
				} catch (Exception $exception) {
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			}
		} else {
			try {
				answerCallbackQuery($queryId, "🤔 You don't have enough (🟠) - (Amber Crystal).");
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	}
}

if ($queryData == "gems_sapphire_gemstone") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersInventory = $pdo->prepare("SELECT * FROM users_inventory WHERE quantity >= 1 AND item_id = :item_id AND user_id = :user_id");
			$selectUsersInventory->execute([":item_id" => 11, ":user_id" => $queryUserId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}			
		if ($rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC)) {
			try {
				$selectUsersGems = $pdo->prepare("SELECT * FROM users_gems WHERE user_id = :user_id");
				$selectUsersGems->execute([":user_id" => $queryUserId]);
				$rowUsersGems = $selectUsersGems->fetch(PDO::FETCH_ASSOC);
				$selectUsersProfiles = $pdo->prepare("SELECT * FROM users_profiles WHERE user_id = :user_id");
				$selectUsersProfiles->execute([":user_id" => $queryUserId]);
				$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
			if ($rowUsersGems["sapphire_gemstone"] < $rowUsersProfiles["level"] * 2) {
				try {
					$pdo->beginTransaction();
					$updateUsersGems = $pdo->prepare("UPDATE users_gems SET sapphire_gemstone = sapphire_gemstone + :sapphire_gemstone WHERE user_id = :user_id");
					$updateUsersGems->execute([":sapphire_gemstone" => 1, ":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET ranged_damage = ranged_damage + :ranged_damage WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":ranged_damage" => 11, ":user_id" => $queryUserId]);
					removeItemFromInventory($pdo, $queryUserId, 11);
					$pdo->commit();
					editMessageText($queryUserId, $queryMessageId, "🔘 [ <code>GEMS</code> ]\nPrecious stones mined by the miners of <i>Abandoned Mines</i>. These precious gems are indeed very valuable and, since the miners are very greedy, their price is usually not too affordable.\n\n(🔴) Crimson Gemstone ➔ " . $rowUsersGems["crimson_gemstone"] . "\n(🟠) Amber Crystal ➔ " . $rowUsersGems["amber_crystal"] . "\n(🟤) Sapphire Gemstone ➔ " . ($rowUsersGems["sapphire_gemstone"] + 1) . "\n(🟣) Amethyst Crystal ➔ " . $rowUsersGems["amethyst_crystal"] . "\n(🟢) Emerald Gemstone ➔ " . $rowUsersGems["emerald_gemstone"] . "\n(⚫️) Obsidian Shard ➔ " . $rowUsersGems["obsidian_shard"] . "\n(🔵) Quartz Gemstone ➔ " . $rowUsersGems["quartz_gemstone"] . "\n(🟡) Topaz Gem ➔ " . $rowUsersGems["topaz_gem"] . "\n(⚪️) Ivory Crystal ➔ " . $rowUsersGems["ivory_crystal"] . "\n\nℹ️ <i>Press the button below to embed the corresponding gem.</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"(🔴)","callback_data":"gems_crimson_gemstone"},{"text":"(🟠)","callback_data":"gems_amber_crystal"},{"text":"(🟤)","callback_data":"gems_sapphire_gemstone"},{"text":"(🟣)","callback_data":"gems_amethyst_crystal"}],[{"text":"(🟢)","callback_data":"gems_emerald_gemstone"},{"text":"(⚫️)","callback_data":"gems_obsidian_shard"},{"text":"(🔵)","callback_data":"gems_quartz_gemstone"},{"text":"(🟡)","callback_data":"gems_topaz_gem"}],[{"text":"(⚪️)","callback_data":"gems_ivory_crystal"}]],"resize_keyboard":true}');
				} catch (Exception $exception) {
					$pdo->rollBack();
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			} else {
				try {
					answerCallbackQuery($queryId, "🥴 You have reached the (🟤) - (Sapphire Gemstone) limit.");
				} catch (Exception $exception) {
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			}
		} else {
			try {
				answerCallbackQuery($queryId, "🤔 You don't have enough (🟤) - (Sapphire Gemstone).");
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	}
}

if ($queryData == "gems_amethyst_crystal") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersInventory = $pdo->prepare("SELECT * FROM users_inventory WHERE quantity >= 1 AND item_id = :item_id AND user_id = :user_id");
			$selectUsersInventory->execute([":item_id" => 12, ":user_id" => $queryUserId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}			
		if ($rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC)) {
			try {
				$selectUsersGems = $pdo->prepare("SELECT * FROM users_gems WHERE user_id = :user_id");
				$selectUsersGems->execute([":user_id" => $queryUserId]);
				$rowUsersGems = $selectUsersGems->fetch(PDO::FETCH_ASSOC);
				$selectUsersProfiles = $pdo->prepare("SELECT * FROM users_profiles WHERE user_id = :user_id");
				$selectUsersProfiles->execute([":user_id" => $queryUserId]);
				$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
			if ($rowUsersGems["amethyst_crystal"] < $rowUsersProfiles["level"] * 2) {
				try {
					$pdo->beginTransaction();
					$updateUsersGems = $pdo->prepare("UPDATE users_gems SET amethyst_crystal = amethyst_crystal + :amethyst_crystal WHERE user_id = :user_id");
					$updateUsersGems->execute([":amethyst_crystal" => 1, ":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET magic_damage = magic_damage + :magic_damage WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":magic_damage" => 9, ":user_id" => $queryUserId]);
					removeItemFromInventory($pdo, $queryUserId, 12);
					$pdo->commit();
					editMessageText($queryUserId, $queryMessageId, "🔘 [ <code>GEMS</code> ]\nPrecious stones mined by the miners of <i>Abandoned Mines</i>. These precious gems are indeed very valuable and, since the miners are very greedy, their price is usually not too affordable.\n\n(🔴) Crimson Gemstone ➔ " . $rowUsersGems["crimson_gemstone"] . "\n(🟠) Amber Crystal ➔ " . $rowUsersGems["amber_crystal"] . "\n(🟤) Sapphire Gemstone ➔ " . $rowUsersGems["sapphire_gemstone"] . "\n(🟣) Amethyst Crystal ➔ " . ($rowUsersGems["amethyst_crystal"] + 1) . "\n(🟢) Emerald Gemstone ➔ " . $rowUsersGems["emerald_gemstone"] . "\n(⚫️) Obsidian Shard ➔ " . $rowUsersGems["obsidian_shard"] . "\n(🔵) Quartz Gemstone ➔ " . $rowUsersGems["quartz_gemstone"] . "\n(🟡) Topaz Gem ➔ " . $rowUsersGems["topaz_gem"] . "\n(⚪️) Ivory Crystal ➔ " . $rowUsersGems["ivory_crystal"] . "\n\nℹ️ <i>Press the button below to embed the corresponding gem.</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"(🔴)","callback_data":"gems_crimson_gemstone"},{"text":"(🟠)","callback_data":"gems_amber_crystal"},{"text":"(🟤)","callback_data":"gems_sapphire_gemstone"},{"text":"(🟣)","callback_data":"gems_amethyst_crystal"}],[{"text":"(🟢)","callback_data":"gems_emerald_gemstone"},{"text":"(⚫️)","callback_data":"gems_obsidian_shard"},{"text":"(🔵)","callback_data":"gems_quartz_gemstone"},{"text":"(🟡)","callback_data":"gems_topaz_gem"}],[{"text":"(⚪️)","callback_data":"gems_ivory_crystal"}]],"resize_keyboard":true}');
				} catch (Exception $exception) {
					$pdo->rollBack();
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			} else {
				try {
					answerCallbackQuery($queryId, "🥴 You have reached the (🟣) - (Amethyst Crystal) limit.");
				} catch (Exception $exception) {
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			}
		} else {
			try {
				answerCallbackQuery($queryId, "🤔 You don't have enough (🟣) - (Amethyst Crystal).");
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	}
}

if ($queryData == "gems_emerald_gemstone") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersInventory = $pdo->prepare("SELECT * FROM users_inventory WHERE quantity >= 1 AND item_id = :item_id AND user_id = :user_id");
			$selectUsersInventory->execute([":item_id" => 13, ":user_id" => $queryUserId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}			
		if ($rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC)) {
			try {
				$selectUsersGems = $pdo->prepare("SELECT * FROM users_gems WHERE user_id = :user_id");
				$selectUsersGems->execute([":user_id" => $queryUserId]);
				$rowUsersGems = $selectUsersGems->fetch(PDO::FETCH_ASSOC);
				$selectUsersProfiles = $pdo->prepare("SELECT * FROM users_profiles WHERE user_id = :user_id");
				$selectUsersProfiles->execute([":user_id" => $queryUserId]);
				$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
			if ($rowUsersGems["emerald_gemstone"] < $rowUsersProfiles["level"] * 2) {
				try {
					$pdo->beginTransaction();
					$updateUsersGems = $pdo->prepare("UPDATE users_gems SET emerald_gemstone = emerald_gemstone + :emerald_gemstone WHERE user_id = :user_id");
					$updateUsersGems->execute([":emerald_gemstone" => 1, ":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET physical_defense = physical_defense + :physical_defense WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":physical_defense" => 6, ":user_id" => $queryUserId]);
					removeItemFromInventory($pdo, $queryUserId, 13);
					$pdo->commit();
					editMessageText($queryUserId, $queryMessageId, "🔘 [ <code>GEMS</code> ]\nPrecious stones mined by the miners of <i>Abandoned Mines</i>. These precious gems are indeed very valuable and, since the miners are very greedy, their price is usually not too affordable.\n\n(🔴) Crimson Gemstone ➔ " . $rowUsersGems["crimson_gemstone"] . "\n(🟠) Amber Crystal ➔ " . $rowUsersGems["amber_crystal"] . "\n(🟤) Sapphire Gemstone ➔ " . $rowUsersGems["sapphire_gemstone"] . "\n(🟣) Amethyst Crystal ➔ " . $rowUsersGems["amethyst_crystal"] . "\n(🟢) Emerald Gemstone ➔ " . ($rowUsersGems["emerald_gemstone"] + 1) . "\n(⚫️) Obsidian Shard ➔ " . $rowUsersGems["obsidian_shard"] . "\n(🔵) Quartz Gemstone ➔ " . $rowUsersGems["quartz_gemstone"] . "\n(🟡) Topaz Gem ➔ " . $rowUsersGems["topaz_gem"] . "\n(⚪️) Ivory Crystal ➔ " . $rowUsersGems["ivory_crystal"] . "\n\nℹ️ <i>Press the button below to embed the corresponding gem.</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"(🔴)","callback_data":"gems_crimson_gemstone"},{"text":"(🟠)","callback_data":"gems_amber_crystal"},{"text":"(🟤)","callback_data":"gems_sapphire_gemstone"},{"text":"(🟣)","callback_data":"gems_amethyst_crystal"}],[{"text":"(🟢)","callback_data":"gems_emerald_gemstone"},{"text":"(⚫️)","callback_data":"gems_obsidian_shard"},{"text":"(🔵)","callback_data":"gems_quartz_gemstone"},{"text":"(🟡)","callback_data":"gems_topaz_gem"}],[{"text":"(⚪️)","callback_data":"gems_ivory_crystal"}]],"resize_keyboard":true}');
				} catch (Exception $exception) {
					$pdo->rollBack();
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			} else {
				try {
					answerCallbackQuery($queryId, "🥴 You have reached the (🟢) - (Emerald Gemstone) limit.");
				} catch (Exception $exception) {
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			}
		} else {
			try {
				answerCallbackQuery($queryId, "🤔 You don't have enough (🟢) - (Emerald Gemstone).");
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	}
}

if ($queryData == "gems_obsidian_shard") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersInventory = $pdo->prepare("SELECT * FROM users_inventory WHERE quantity >= 1 AND item_id = :item_id AND user_id = :user_id");
			$selectUsersInventory->execute([":item_id" => 14, ":user_id" => $queryUserId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}			
		if ($rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC)) {
			try {
				$selectUsersGems = $pdo->prepare("SELECT * FROM users_gems WHERE user_id = :user_id");
				$selectUsersGems->execute([":user_id" => $queryUserId]);
				$rowUsersGems = $selectUsersGems->fetch(PDO::FETCH_ASSOC);
				$selectUsersProfiles = $pdo->prepare("SELECT * FROM users_profiles WHERE user_id = :user_id");
				$selectUsersProfiles->execute([":user_id" => $queryUserId]);
				$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
			if ($rowUsersGems["obsidian_shard"] < $rowUsersProfiles["level"] * 2) {
				try {
					$pdo->beginTransaction();
					$updateUsersGems = $pdo->prepare("UPDATE users_gems SET obsidian_shard = obsidian_shard + :obsidian_shard WHERE user_id = :user_id");
					$updateUsersGems->execute([":obsidian_shard" => 1, ":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET magic_defense = magic_defense + :magic_defense WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":magic_defense" => 5, ":user_id" => $queryUserId]);
					removeItemFromInventory($pdo, $queryUserId, 14);
					$pdo->commit();
					editMessageText($queryUserId, $queryMessageId, "🔘 [ <code>GEMS</code> ]\nPrecious stones mined by the miners of <i>Abandoned Mines</i>. These precious gems are indeed very valuable and, since the miners are very greedy, their price is usually not too affordable.\n\n(🔴) Crimson Gemstone ➔ " . $rowUsersGems["crimson_gemstone"] . "\n(🟠) Amber Crystal ➔ " . $rowUsersGems["amber_crystal"] . "\n(🟤) Sapphire Gemstone ➔ " . $rowUsersGems["sapphire_gemstone"] . "\n(🟣) Amethyst Crystal ➔ " . $rowUsersGems["amethyst_crystal"] . "\n(🟢) Emerald Gemstone ➔ " . $rowUsersGems["emerald_gemstone"] . "\n(⚫️) Obsidian Shard ➔ " . ($rowUsersGems["obsidian_shard"] + 1) . "\n(🔵) Quartz Gemstone ➔ " . $rowUsersGems["quartz_gemstone"] . "\n(🟡) Topaz Gem ➔ " . $rowUsersGems["topaz_gem"] . "\n(⚪️) Ivory Crystal ➔ " . $rowUsersGems["ivory_crystal"] . "\n\nℹ️ <i>Press the button below to embed the corresponding gem.</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"(🔴)","callback_data":"gems_crimson_gemstone"},{"text":"(🟠)","callback_data":"gems_amber_crystal"},{"text":"(🟤)","callback_data":"gems_sapphire_gemstone"},{"text":"(🟣)","callback_data":"gems_amethyst_crystal"}],[{"text":"(🟢)","callback_data":"gems_emerald_gemstone"},{"text":"(⚫️)","callback_data":"gems_obsidian_shard"},{"text":"(🔵)","callback_data":"gems_quartz_gemstone"},{"text":"(🟡)","callback_data":"gems_topaz_gem"}],[{"text":"(⚪️)","callback_data":"gems_ivory_crystal"}]],"resize_keyboard":true}');
				} catch (Exception $exception) {
					$pdo->rollBack();
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			} else {
				try {
					answerCallbackQuery($queryId, "🥴 You have reached the (⚫️) - (Obsidian Shard) limit.");
				} catch (Exception $exception) {
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			}
		} else {
			try {
				answerCallbackQuery($queryId, "🤔 You don't have enough (⚫️) - (Obsidian Shard).");
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	}
}

if ($queryData == "gems_quartz_gemstone") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersInventory = $pdo->prepare("SELECT * FROM users_inventory WHERE quantity >= 1 AND item_id = :item_id AND user_id = :user_id");
			$selectUsersInventory->execute([":item_id" => 15, ":user_id" => $queryUserId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}			
		if ($rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC)) {
			try {
				$selectUsersGems = $pdo->prepare("SELECT * FROM users_gems WHERE user_id = :user_id");
				$selectUsersGems->execute([":user_id" => $queryUserId]);
				$rowUsersGems = $selectUsersGems->fetch(PDO::FETCH_ASSOC);
				$selectUsersProfiles = $pdo->prepare("SELECT * FROM users_profiles WHERE user_id = :user_id");
				$selectUsersProfiles->execute([":user_id" => $queryUserId]);
				$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
			if ($rowUsersGems["quartz_gemstone"] < $rowUsersProfiles["level"] * 2) {
				try {
					$pdo->beginTransaction();
					$updateUsersGems = $pdo->prepare("UPDATE users_gems SET quartz_gemstone = quartz_gemstone + :quartz_gemstone WHERE user_id = :user_id");
					$updateUsersGems->execute([":quartz_gemstone" => 1, ":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET critical_damage = critical_damage + :critical_damage WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":critical_damage" => 0.2, ":user_id" => $queryUserId]);
					removeItemFromInventory($pdo, $queryUserId, 15);
					$pdo->commit();
					editMessageText($queryUserId, $queryMessageId, "🔘 [ <code>GEMS</code> ]\nPrecious stones mined by the miners of <i>Abandoned Mines</i>. These precious gems are indeed very valuable and, since the miners are very greedy, their price is usually not too affordable.\n\n(🔴) Crimson Gemstone ➔ " . $rowUsersGems["crimson_gemstone"] . "\n(🟠) Amber Crystal ➔ " . $rowUsersGems["amber_crystal"] . "\n(🟤) Sapphire Gemstone ➔ " . $rowUsersGems["sapphire_gemstone"] . "\n(🟣) Amethyst Crystal ➔ " . $rowUsersGems["amethyst_crystal"] . "\n(🟢) Emerald Gemstone ➔ " . $rowUsersGems["emerald_gemstone"] . "\n(⚫️) Obsidian Shard ➔ " . $rowUsersGems["obsidian_shard"] . "\n(🔵) Quartz Gemstone ➔ " . ($rowUsersGems["quartz_gemstone"] + 1) . "\n(🟡) Topaz Gem ➔ " . $rowUsersGems["topaz_gem"] . "\n(⚪️) Ivory Crystal ➔ " . $rowUsersGems["ivory_crystal"] . "\n\nℹ️ <i>Press the button below to embed the corresponding gem.</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"(🔴)","callback_data":"gems_crimson_gemstone"},{"text":"(🟠)","callback_data":"gems_amber_crystal"},{"text":"(🟤)","callback_data":"gems_sapphire_gemstone"},{"text":"(🟣)","callback_data":"gems_amethyst_crystal"}],[{"text":"(🟢)","callback_data":"gems_emerald_gemstone"},{"text":"(⚫️)","callback_data":"gems_obsidian_shard"},{"text":"(🔵)","callback_data":"gems_quartz_gemstone"},{"text":"(🟡)","callback_data":"gems_topaz_gem"}],[{"text":"(⚪️)","callback_data":"gems_ivory_crystal"}]],"resize_keyboard":true}');
				} catch (Exception $exception) {
					$pdo->rollBack();
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			} else {
				try {
					answerCallbackQuery($queryId, "🥴 You have reached the (🔵) - (Quartz Gemstone) limit.");
				} catch (Exception $exception) {
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			}
		} else {
			try {
				answerCallbackQuery($queryId, "🤔 You don't have enough (🔵) - (Quartz Gemstone).");
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	}
}

if ($queryData == "gems_topaz_gem") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersInventory = $pdo->prepare("SELECT * FROM users_inventory WHERE quantity >= 1 AND item_id = :item_id AND user_id = :user_id");
			$selectUsersInventory->execute([":item_id" => 16, ":user_id" => $queryUserId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}			
		if ($rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC)) {
			try {
				$selectUsersGems = $pdo->prepare("SELECT * FROM users_gems WHERE user_id = :user_id");
				$selectUsersGems->execute([":user_id" => $queryUserId]);
				$rowUsersGems = $selectUsersGems->fetch(PDO::FETCH_ASSOC);
				$selectUsersProfiles = $pdo->prepare("SELECT * FROM users_profiles WHERE user_id = :user_id");
				$selectUsersProfiles->execute([":user_id" => $queryUserId]);
				$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
			if ($rowUsersGems["topaz_gem"] < $rowUsersProfiles["level"] * 2) {
				try {
					$pdo->beginTransaction();
					$updateUsersGems = $pdo->prepare("UPDATE users_gems SET topaz_gem = topaz_gem + :topaz_gem WHERE user_id = :user_id");
					$updateUsersGems->execute([":topaz_gem" => 1, ":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET evade = evade + :evade WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":evade" => 3, ":user_id" => $queryUserId]);
					removeItemFromInventory($pdo, $queryUserId, 16);
					$pdo->commit();
					editMessageText($queryUserId, $queryMessageId, "🔘 [ <code>GEMS</code> ]\nPrecious stones mined by the miners of <i>Abandoned Mines</i>. These precious gems are indeed very valuable and, since the miners are very greedy, their price is usually not too affordable.\n\n(🔴) Crimson Gemstone ➔ " . $rowUsersGems["crimson_gemstone"] . "\n(🟠) Amber Crystal ➔ " . $rowUsersGems["amber_crystal"] . "\n(🟤) Sapphire Gemstone ➔ " . $rowUsersGems["sapphire_gemstone"] . "\n(🟣) Amethyst Crystal ➔ " . $rowUsersGems["amethyst_crystal"] . "\n(🟢) Emerald Gemstone ➔ " . $rowUsersGems["emerald_gemstone"] . "\n(⚫️) Obsidian Shard ➔ " . $rowUsersGems["obsidian_shard"] . "\n(🔵) Quartz Gemstone ➔ " . $rowUsersGems["quartz_gemstone"] . "\n(🟡) Topaz Gem ➔ " . ($rowUsersGems["topaz_gem"] + 1) . "\n(⚪️) Ivory Crystal ➔ " . $rowUsersGems["ivory_crystal"] . "\n\nℹ️ <i>Press the button below to embed the corresponding gem.</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"(🔴)","callback_data":"gems_crimson_gemstone"},{"text":"(🟠)","callback_data":"gems_amber_crystal"},{"text":"(🟤)","callback_data":"gems_sapphire_gemstone"},{"text":"(🟣)","callback_data":"gems_amethyst_crystal"}],[{"text":"(🟢)","callback_data":"gems_emerald_gemstone"},{"text":"(⚫️)","callback_data":"gems_obsidian_shard"},{"text":"(🔵)","callback_data":"gems_quartz_gemstone"},{"text":"(🟡)","callback_data":"gems_topaz_gem"}],[{"text":"(⚪️)","callback_data":"gems_ivory_crystal"}]],"resize_keyboard":true}');
				} catch (Exception $exception) {
					$pdo->rollBack();
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			} else {
				try {
					answerCallbackQuery($queryId, "🥴 You have reached the (🟡) - (Topaz Gem) limit.");
				} catch (Exception $exception) {
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			}
		} else {
			try {
				answerCallbackQuery($queryId, "🤔 You don't have enough (🟡) - (Topaz Gem).");
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	}
}

if ($queryData == "gems_ivory_crystal") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Profile' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$selectUsersInventory = $pdo->prepare("SELECT * FROM users_inventory WHERE quantity >= 1 AND item_id = :item_id AND user_id = :user_id");
			$selectUsersInventory->execute([":item_id" => 17, ":user_id" => $queryUserId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
			exit;
		}			
		if ($rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC)) {
			try {
				$selectUsersGems = $pdo->prepare("SELECT * FROM users_gems WHERE user_id = :user_id");
				$selectUsersGems->execute([":user_id" => $queryUserId]);
				$rowUsersGems = $selectUsersGems->fetch(PDO::FETCH_ASSOC);
				$selectUsersProfiles = $pdo->prepare("SELECT * FROM users_profiles WHERE user_id = :user_id");
				$selectUsersProfiles->execute([":user_id" => $queryUserId]);
				$rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC);
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
			if ($rowUsersGems["ivory_crystal"] < $rowUsersProfiles["level"] * 2) {
				try {
					$pdo->beginTransaction();
					$updateUsersGems = $pdo->prepare("UPDATE users_gems SET ivory_crystal = ivory_crystal + :ivory_crystal WHERE user_id = :user_id");
					$updateUsersGems->execute([":ivory_crystal" => 1, ":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET hit_rate = hit_rate + :hit_rate WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":hit_rate" => 3, ":user_id" => $queryUserId]);
					removeItemFromInventory($pdo, $queryUserId, 17);
					$pdo->commit();
					editMessageText($queryUserId, $queryMessageId, "🔘 [ <code>GEMS</code> ]\nPrecious stones mined by the miners of <i>Abandoned Mines</i>. These precious gems are indeed very valuable and, since the miners are very greedy, their price is usually not too affordable.\n\n(🔴) Crimson Gemstone ➔ " . $rowUsersGems["crimson_gemstone"] . "\n(🟠) Amber Crystal ➔ " . $rowUsersGems["amber_crystal"] . "\n(🟤) Sapphire Gemstone ➔ " . $rowUsersGems["sapphire_gemstone"] . "\n(🟣) Amethyst Crystal ➔ " . $rowUsersGems["amethyst_crystal"] . "\n(🟢) Emerald Gemstone ➔ " . $rowUsersGems["emerald_gemstone"] . "\n(⚫️) Obsidian Shard ➔ " . $rowUsersGems["obsidian_shard"] . "\n(🔵) Quartz Gemstone ➔ " . $rowUsersGems["quartz_gemstone"] . "\n(🟡) Topaz Gem ➔ " . $rowUsersGems["topaz_gem"] . "\n(⚪️) Ivory Crystal ➔ " . ($rowUsersGems["ivory_crystal"] + 1) . "\n\nℹ️ <i>Press the button below to embed the corresponding gem.</i>", false, '&reply_markup={"inline_keyboard":[[{"text":"(🔴)","callback_data":"gems_crimson_gemstone"},{"text":"(🟠)","callback_data":"gems_amber_crystal"},{"text":"(🟤)","callback_data":"gems_sapphire_gemstone"},{"text":"(🟣)","callback_data":"gems_amethyst_crystal"}],[{"text":"(🟢)","callback_data":"gems_emerald_gemstone"},{"text":"(⚫️)","callback_data":"gems_obsidian_shard"},{"text":"(🔵)","callback_data":"gems_quartz_gemstone"},{"text":"(🟡)","callback_data":"gems_topaz_gem"}],[{"text":"(⚪️)","callback_data":"gems_ivory_crystal"}]],"resize_keyboard":true}');
				} catch (Exception $exception) {
					$pdo->rollBack();
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			} else {
				try {
					answerCallbackQuery($queryId, "🥴 You have reached the (⚪️) - (Ivory Crystal) limit.");
				} catch (Exception $exception) {
					systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
					exit;
				}
			}
		} else {
			try {
				answerCallbackQuery($queryId, "🤔 You don't have enough (⚪️) - (Ivory Crystal).");
			} catch (Exception $exception) {
				systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
				exit;
			}
		}
	}
}
