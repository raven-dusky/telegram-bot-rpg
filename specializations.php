<?php
if ($text === "🌀 Specializations" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Perks' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
		$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
		if ($rowUsersUtilities) {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $userId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Specializations' WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			$statusIcon = $rowUsersSpecializations["status"] === 0 ? "❌" : "✔️";
			sendMessage($chatId, "🔅 [ <code>SPECIALIZATION</code> ]\nHere you can upgrade a set of specific passive bonuses. Specialization points are required to level up specialization skills.\n▪️ Status: [ " . $statusIcon . " ]\n▪️ Points: <b>" . number_format($rowUsersSpecializations["points"]) . "</b>\n▪️ EXP: " . number_format($rowUsersSpecializations["experience"]) . "/15,000,000\n\nℹ️ <i>To improve your specializations, you need to convert</i> <b>100%</b> <i>of experience gained into specialization experience.</i>", false, false, false, '&reply_markup={"keyboard":[["❤️ Vital Core", "💧 Abundant Energy"],["🥊 Furious Force", "🏹 Sharpshooter"],["🔮 Magic Amplification", "🛡 Armor Master"],["🎯 Precision Shot", "🥾 Shadow Step"],["✔️ Enable/Disable"],["🔙 Go Back"]],"resize_keyboard":true}');
		}
	} catch (Exception) {
		exit;
	}
}

if ($update) {
	$userId = $userId ?? $queryUserId;
	try {
		$selectUsersSpecializations = $pdo->prepare("SELECT experience FROM users_specializations WHERE user_id = :user_id");
		$selectUsersSpecializations->execute([":user_id" => $userId]);
		$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
		if ($rowUsersSpecializations["experience"] >= 15000000) {
			$updateUsersSpecializations = $pdo->prepare("UPDATE users_specializations SET experience = 0, points = points + 1 WHERE user_id = :user_id");
			$updateUsersSpecializations->execute([":user_id" => $userId]);
		}
	} catch (Exception) {
		exit;
	}
}

if ($text === "✔️ Enable/Disable" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
		if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $userId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			if ($rowUsersUtilities) {
				$newStatus = $rowUsersSpecializations["status"] === 0 ? 1 : 0;
				$updateUsersSpecializations = $pdo->prepare("UPDATE users_specializations SET status = :status WHERE user_id = :user_id");
				$updateUsersSpecializations->execute([":status" => $newStatus, ":user_id" => $userId]);
				$statusMessage = $newStatus === 1 
					? "🫡 The specialization system is now (active)." 
					: "🫡 The specialization system is now (inactive).";
				sendMessage($chatId, $statusMessage);
			}
		}
	} catch (Exception) {
		exit;
	}
}

if ($text === "❤️ Vital Core" && $chatType === "private") {
	$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
	$selectUsersUtilities->execute([":user_id" => $userId]);
	$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersUtilities) {
		try {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $userId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			sendMessage($chatId, "❤️ [ <code>VITAL CORE</code> ] (Level <b>" . $rowUsersSpecializations["vital_core"] . "</b>/50)\nIncreases your maximum (Health Points).\n\nℹ️ Each level of this specialization increases your total (Health Points) by +<b>1,200</b>. You have currently gained a total bonus of +<b>" . number_format($rowUsersSpecializations["vital_core"] * 1200) . "</b> (Health Points) from this specialization.", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"⏏️ Upgrade","callback_data":"specialization_vital_core_upgrade"}]],"resize_keyboard":true}');
		} catch (Exception) {
			exit;
		}
	}
}

if ($queryData === "specialization_vital_core_upgrade") {
	$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
	$selectUsersUtilities->execute([":user_id" => $userId]);
	$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersUtilities) {
		try {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $queryUserId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			if ($rowUsersSpecializations["vital_core"] < 50) {
				if ($rowUsersSpecializations["points"] > 0) {
					$updateUsersSpecializations = $pdo->prepare("UPDATE users_specializations SET vital_core = vital_core + 1, points = points - 1 WHERE user_id = :user_id");
					$updateUsersSpecializations->execute([":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET max_health_points = max_health_points + 1200 WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "❤️ (Vital Core) specialization successfully upgraded!");
					editMessageText($queryUserId, $queryMessageId, "❤️ [ <code>VITAL CORE</code> ] (Level <b>" . ($rowUsersSpecializations["vital_core"] + 1) . "</b>/50)\nIncreases your maximum (Health Points).\n\nℹ️ Each level of this specialization increases your total (Health Points) by +<b>1,200</b>. You have currently gained a total bonus of +<b>" . number_format(($rowUsersSpecializations["vital_core"] + 1) * 1200) . "</b> (Health Points) from this specialization.", false, '&reply_markup={"inline_keyboard":[[{"text":"⏏️ Upgrade","callback_data":"specialization_vital_core_upgrade"}]],"resize_keyboard":true}'
					);
				} else {
					answerCallbackQuery($queryId, "🫡 You don't have enough (Specializations) points.");
				}
			} else {
				answerCallbackQuery($queryId, "🥳 Congratulations! You have reached the maximum (Specializations) level.");
			}
		} catch (Exception) {
			exit;
		}
	}
}

if ($text === "💧 Abundant Energy" && $chatType === "private") {
	$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
	$selectUsersUtilities->execute([":user_id" => $userId]);
	$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersUtilities) {
		try {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $userId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			sendMessage($chatId, "💧 [ <code>ABUNDANT ENERGY</code> ] (Level <b>" . $rowUsersSpecializations["abundant_energy"] . "</b>/50)\nIncreases your maximum (Mana Points).\n\nℹ️ Each level of this specialization increases your total (Mana Points) by +<b>10</b>. You have currently gained a total bonus of +<b>" . number_format($rowUsersSpecializations["abundant_energy"] * 10) . "</b> (Mana Points) from this specialization.", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"⏏️ Upgrade","callback_data":"specialization_abundant_energy_upgrade"}]],"resize_keyboard":true}');
		} catch (Exception) {
			exit;
		}
	}
}

if ($queryData === "specialization_abundant_energy_upgrade") {
	$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
	$selectUsersUtilities->execute([":user_id" => $userId]);
	$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersUtilities) {
		try {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $queryUserId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			if ($rowUsersSpecializations["abundant_energy"] < 50) {
				if ($rowUsersSpecializations["points"] > 0) {
					$updateUsersSpecializations = $pdo->prepare("UPDATE users_specializations SET abundant_energy = abundant_energy + 1, points = points - 1 WHERE user_id = :user_id");
					$updateUsersSpecializations->execute([":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics SET max_mana_points = max_mana_points + 10 WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "💧 (Abundant Energy) specialization successfully upgraded!");
					editMessageText($queryUserId, $queryMessageId, "💧 [ <code>ABUNDANT ENERGY</code> ] (Level <b>" . ($rowUsersSpecializations["abundant_energy"] + 1) . "</b>/50)\nIncreases your maximum (Mana Points).\n\nℹ️ Each level of this specialization increases your total (Mana Points) by +<b>10</b>. You have currently gained a total bonus of +<b>" . number_format(($rowUsersSpecializations["abundant_energy"] + 1) * 10) . "</b> (Mana Points) from this specialization.", false, '&reply_markup={"inline_keyboard":[[{"text":"⏏️ Upgrade","callback_data":"specialization_abundant_energy_upgrade"}]],"resize_keyboard":true}');
				} else {
					answerCallbackQuery($queryId, "🫡 You don't have enough (Specialization) points.");
				}
			} else {
				answerCallbackQuery($queryId, "🥳 Congratulations! You have reached the maximum (Specializations) level.");
			}
		} catch (Exception) {
			exit;
		}
	}
}

if ($text === "🥊 Furious Force" && $chatType === "private") {
	$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
	$selectUsersUtilities->execute([":user_id" => $userId]);
	$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersUtilities) {
		try {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $userId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			sendMessage($chatId, "🥊 [ <code>FURIOUS FORCE</code> ] (Level <b>" . $rowUsersSpecializations["furious_force"] . "</b>/50)\nAffects your (Physical Damage).\n\nℹ️ Each level of this specialization increases your total (Physical Damage) by +<b>1.25%</b>. You have currently gained a total bonus of +<b>" . number_format($rowUsersSpecializations["furious_force"] * 1.25) . "</b>% (Physical Damage) from this specialization.", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"⏏️ Upgrade","callback_data":"specialization_furious_force_upgrade"}]],"resize_keyboard":true}');
		} catch (Exception) {
			exit;
		}
	}
}

if ($queryData === "specialization_furious_force_upgrade") {
	$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
	$selectUsersUtilities->execute([":user_id" => $userId]);
	$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersUtilities) {
		try {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $queryUserId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			if ($rowUsersSpecializations["furious_force"] < 50) {
				if ($rowUsersSpecializations["points"] > 0) {
					$updateUsersSpecializations = $pdo->prepare("UPDATE users_specializations SET furious_force = furious_force + 1, points = points - 1 WHERE user_id = :user_id");
					$updateUsersSpecializations->execute([":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics_bonuses SET physical_damage = physical_damage + 1.25 WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "🥊 (Furious Force) specialization successfully upgraded!");
					editMessageText($queryUserId, $queryMessageId, "🥊 [ <code>FURIOUS FORCE</code> ] (Level <b>" . ($rowUsersSpecializations["furious_force"] + 1) . "</b>/50)\nAffects your (Physical Damage).\n\nℹ️ Each level of this specialization increases your total (Physical Damage) by +<b>1.25%</b>. You have currently gained a total bonus of +<b>" . number_format(($rowUsersSpecializations["furious_force"] + 1) * 1.25) . "</b>% (Physical Damage) from this specialization.", false, '&reply_markup={"inline_keyboard":[[{"text":"⏏️ Upgrade","callback_data":"specialization_furious_force_upgrade"}]],"resize_keyboard":true}');
				} else {
					answerCallbackQuery($queryId, "🫡 You don't have enough (Specializations) points.");
				}
			} else {
				answerCallbackQuery($queryId, "🥳 Congratulations! You have reached the maximum (Specializations) level.");
			}
		} catch (Exception) {
			exit;
		}
	}
}

if ($text === "🏹 Sharpshooter" && $chatType === "private") {
	$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
	$selectUsersUtilities->execute([":user_id" => $userId]);
	$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersUtilities) {
		try {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $userId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			sendMessage($chatId, "🏹 [ <code>SHARPSHOOTER</code> ] (Level <b>" . $rowUsersSpecializations["sharpshooter"] . "</b>/50)\nAffects your (Ranged Damage).\n\nℹ️ Each level of this specialization increases your total (Ranged Damage) by +<b>1.25%</b>. You have currently gained a total bonus of +<b>" . number_format($rowUsersSpecializations["sharpshooter"] * 1.25) . "</b>% (Ranged Damage) from this specialization.", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"⏏️ Upgrade","callback_data":"specialization_sharpshooter_upgrade"}]],"resize_keyboard":true}');
		} catch (Exception) {
			exit;
		}
	}
}

if ($queryData === "specialization_sharpshooter_upgrade") {
	$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
	$selectUsersUtilities->execute([":user_id" => $userId]);
	$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersUtilities) {
		try {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $queryUserId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			if ($rowUsersSpecializations["sharpshooter"] < 50) {
				if ($rowUsersSpecializations["points"] > 0) {
					$updateUsersSpecializations = $pdo->prepare("UPDATE users_specializations SET sharpshooter = sharpshooter + 1, points = points - 1 WHERE user_id = :user_id");
					$updateUsersSpecializations->execute([":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics_bonuses SET ranged_damage = ranged_damage + 1.25 WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "🏹 (Sharpshooter) specialization successfully upgraded!");
					editMessageText($queryUserId, $queryMessageId, "🏹 [ <code>SHARPSHOOTER</code> ] (Level <b>" . ($rowUsersSpecializations["sharpshooter"] + 1) . "</b>/50)\nAffects your (Ranged Damage).\n\nℹ️ Each level of this specialization increases your total (Ranged Damage) by +<b>1.25%</b>. You have currently gained a total bonus of +<b>" . number_format(($rowUsersSpecializations["sharpshooter"] + 1) * 1.25) . "</b>% (Ranged Damage) from this specialization.", false, '&reply_markup={"inline_keyboard":[[{"text":"⏏️ Upgrade","callback_data":"specialization_sharpshooter_upgrade"}]],"resize_keyboard":true}');
				} else {
					answerCallbackQuery($queryId, "🫡 You don't have enough (Specializations) points.");
				}
			} else {
				answerCallbackQuery($queryId, "🥳 Congratulations! You have reached the maximum (Specializations) level.");
			}
		} catch (Exception) {
			exit;
		}
	}
}

if ($text === "🔮 Magic Amplification" && $chatType === "private") {
	$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
	$selectUsersUtilities->execute([":user_id" => $userId]);
	$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersUtilities) {
		try {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $userId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			sendMessage($chatId, "🔮 [ <code>MAGIC AMPLIFICATION</code> ] (Level <b>" . $rowUsersSpecializations["magic_amplification"] . "</b>/50)\nAffects your (Magic Damage).\n\nℹ️ Each level of this specialization increases your total (Magic Damage) by +<b>1.5%</b>. You have currently gained a total bonus of +<b>" . number_format($rowUsersSpecializations["magic_amplification"] * 1.5) . "</b>% (Magic Damage) from this specialization.", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"⏏️ Upgrade","callback_data":"specialization_magic_amplification_upgrade"}]],"resize_keyboard":true}');
		} catch (Exception) {
			exit;
		}
	}
}

if ($queryData === "specialization_magic_amplification_upgrade") {
	$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
	$selectUsersUtilities->execute([":user_id" => $userId]);
	$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersUtilities) {
		try {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $queryUserId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			if ($rowUsersSpecializations["magic_amplification"] < 50) {
				if ($rowUsersSpecializations["points"] > 0) {
					$updateUsersSpecializations = $pdo->prepare("UPDATE users_specializations SET magic_amplification = magic_amplification + 1, points = points - 1 WHERE user_id = :user_id");
					$updateUsersSpecializations->execute([":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics_bonuses SET magic_damage = magic_damage + 1.5 WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "🔮 (Magic Amplification) specialization successfully upgraded!");
					editMessageText($queryUserId, $queryMessageId, "🔮 [ <code>MAGIC AMPLIFICATION</code> ] (Level <b>" . ($rowUsersSpecializations["magic_amplification"] + 1) . "</b>/50)\nAffects your (Magic Damage).\n\nℹ️ Each level of this specialization increases your total (Magic Damage) by +<b>1.5%</b>. You have currently gained a total bonus of +<b>" . number_format(($rowUsersSpecializations["magic_amplification"] + 1) * 1.5) . "</b>% (Magic Damage) from this specialization.", false, '&reply_markup={"inline_keyboard":[[{"text":"⏏️ Upgrade","callback_data":"specialization_magic_amplification_upgrade"}]],"resize_keyboard":true}');
				} else {
					answerCallbackQuery($queryId, "🫡 You don't have enough (Specialization) points.");
				}
			} else {
				answerCallbackQuery($queryId, "🥳 Congratulations! You have reached the maximum (Specializations) level.");
			}
		} catch (Exception) {
			exit;
		}
	}
}

if ($text === "🛡 Armor Master" && $chatType === "private") {
	$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
	$selectUsersUtilities->execute([":user_id" => $userId]);
	$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersUtilities) {
		try {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $userId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			sendMessage($chatId, "🛡 [ <code>ARMOR MASTER</code> ] (Level <b>" . $rowUsersSpecializations["armor_master"] . "</b>/50)\nAffects your (Physical Defense) and (Magic Defense).\n\nℹ️ Each level of this specialization increases your total (Physical Defense) by +<b>1%</b> and your (Magic Defense) by +<b>0.75%</b>. You have currently gained a total bonus of +<b>" . number_format($rowUsersSpecializations["armor_master"] * 1) . "</b>% (Physical Defense) and +<b>" . number_format($rowUsersSpecializations["armor_master"] * 0.75) . "</b>% (Magic Defense) from this specialization.", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"⏏️ Upgrade","callback_data":"specialization_armor_master_upgrade"}]],"resize_keyboard":true}');
		} catch (Exception) {
			exit;
		}
	}
}

if ($queryData === "specialization_armor_master_upgrade") {
	$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
	$selectUsersUtilities->execute([":user_id" => $userId]);
	$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersUtilities) {
		try {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $queryUserId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			if ($rowUsersSpecializations["armor_master"] < 50) {
				if ($rowUsersSpecializations["points"] > 0) {
					$updateUsersSpecializations = $pdo->prepare("UPDATE users_specializations SET armor_master = armor_master + 1, points = points - 1 WHERE user_id = :user_id");
					$updateUsersSpecializations->execute([":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics_bonuses SET physical_defense = physical_defense + 1, magic_defense = magic_defense + 0.75 WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "🛡 (Armor Master) specialization successfully upgraded!");
					editMessageText($queryUserId, $queryMessageId, "🛡 [ <code>ARMOR MASTER</code> ] (Level <b>" . ($rowUsersSpecializations["armor_master"] + 1) . "</b>/50)\nAffects your (Physical Defense) and (Magic Defense).\n\nℹ️ Each level of this specialization increases your total (Physical Defense) by +<b>1%</b> and your (Magic Defense) by +<b>0.75%</b>. You have currently gained a total bonus of +<b>" . number_format(($rowUsersSpecializations["armor_master"] + 1) * 1) . "</b>% (Physical Defense) and +<b>" . number_format(($rowUsersSpecializations["armor_master"] + 1) * 0.75) . "</b>% (Magic Defense) from this specialization.", false, '&reply_markup={"inline_keyboard":[[{"text":"⏏️ Upgrade","callback_data":"specialization_armor_master_upgrade"}]],"resize_keyboard":true}');
				} else {
					answerCallbackQuery($queryId, "🫡 You don't have enough (Specialization) points.");
				}
			} else {
				answerCallbackQuery($queryId, "🥳 Congratulations! You have reached the maximum (Specializations) level.");
			}
		} catch (Exception) {
			exit;
		}
	}
}

if ($text === "💥 Critical Blast" && $chatType === "private") {
	$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
	$selectUsersUtilities->execute([":user_id" => $userId]);
	$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersUtilities) {
		try {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $userId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			sendMessage($chatId, "💥 [ <code>CRITICAL BLAST</code> ] (Level <b>" . $rowUsersSpecializations["critical_blast"] . "</b>/50)\nAffects your (Critical Damage).\n\nℹ️ Each level of this specialization increases your total (Critical Damage) by +<b>0.25%</b>. You have currently gained a total bonus of +<b>" . number_format($rowUsersSpecializations["critical_blast"] * 0.25) . "</b>% (Critical Damage) from this specialization.", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"⏏️ Upgrade","callback_data":"specialization_critical_blast_upgrade"}]],"resize_keyboard":true}');
		} catch (Exception) {
			exit;
		}
	}
}

if ($queryData === "specialization_critical_blast_upgrade") {
	$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
	$selectUsersUtilities->execute([":user_id" => $userId]);
	$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersUtilities) {
		try {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $queryUserId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			if ($rowUsersSpecializations["critical_blast"] < 50) {
				if ($rowUsersSpecializations["points"] > 0) {
					$updateUsersSpecializations = $pdo->prepare("UPDATE users_specializations SET critical_blast = critical_blast + 1, points = points - 1 WHERE user_id = :user_id");
					$updateUsersSpecializations->execute([":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics_bonuses SET critical_damage = critical_damage + 0.25 WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "💥 (Critical Blast) specialization successfully upgraded!");
					editMessageText($queryUserId, $queryMessageId, "💥 [ <code>CRITICAL BLAST</code> ] (Level <b>" . ($rowUsersSpecializations["critical_blast"] + 1) . "</b>/50)\nAffects your (Critical Damage).\n\nℹ️ Each level of this specialization increases your total (Critical Damage) by +<b>0.25%</b>. You have currently gained a total bonus of +<b>" . number_format(($rowUsersSpecializations["critical_blast"] + 1) * 0.25) . "</b>% (Critical Damage) from this specialization.", false, '&reply_markup={"inline_keyboard":[[{"text":"⏏️ Upgrade","callback_data":"specialization_critical_blast_upgrade"}]],"resize_keyboard":true}');
				} else {
					answerCallbackQuery($queryId, "🫡 You don't have enough (Specialization) points.");
				}
			} else {
				answerCallbackQuery($queryId, "🥳 Congratulations! You have reached the maximum (Specializations) level.");
			}
		} catch (Exception) {
			exit;
		}
	}
}

if ($text === "🎯 Precision Shot" && $chatType === "private") {
	$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
	$selectUsersUtilities->execute([":user_id" => $userId]);
	$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersUtilities) {
		try {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $userId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			sendMessage($chatId, "🎯 [ <code>PRECISION SHOT</code> ] (Level <b>" . $rowUsersSpecializations["precision_shot"] . "</b>/50)\nAffects your (Hit Rate).\n\nℹ️ Each level of this specialization increases your total (Hit Rate) by +<b>0.10%</b>. You have currently gained a total bonus of +<b>" . number_format($rowUsersSpecializations["precision_shot"] * 0.10) . "</b>% (Hit Rate) from this specialization.", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"⏏️ Upgrade","callback_data":"specialization_precision_shot_upgrade"}]],"resize_keyboard":true}');
		} catch (Exception) {
			exit;
		}
	}
}

if ($queryData === "specialization_precision_shot_upgrade") {
	$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
	$selectUsersUtilities->execute([":user_id" => $userId]);
	$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersUtilities) {
		try {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $queryUserId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			if ($rowUsersSpecializations["precision_shot"] < 50) {
				if ($rowUsersSpecializations["points"] > 0) {
					$updateUsersSpecializations = $pdo->prepare("UPDATE users_specializations SET precision_shot = precision_shot + 1, points = points - 1 WHERE user_id = :user_id");
					$updateUsersSpecializations->execute([":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics_bonuses SET hit_rate = hit_rate + 0.10 WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "🎯 (Precision Shot) specialization successfully upgraded!");
					editMessageText($queryUserId, $queryMessageId, "🎯 [ <code>PRECISION SHOT</code> ] (Level <b>" . ($rowUsersSpecializations["precision_shot"] + 1) . "</b>/50)\nAffects your (Hit Rate).\n\nℹ️ Each level of this specialization increases your total (Hit Rate) by +<b>0.10%</b>. You have currently gained a total bonus of +<b>" . number_format(($rowUsersSpecializations["precision_shot"] + 1) * 0.10) . "</b>% (Hit Rate) from this specialization.", false, '&reply_markup={"inline_keyboard":[[{"text":"⏏️ Upgrade","callback_data":"specialization_precision_shot_upgrade"}]],"resize_keyboard":true}');
				} else {
					answerCallbackQuery($queryId, "🫡 You don't have enough (Specialization) points.");
				}
			} else {
				answerCallbackQuery($queryId, "🥳 Congratulations! You have reached the maximum (Specializations) level.");
			}
		} catch (Exception) {
			exit;
		}
	}
}

if ($text === "🥾 Shadow Step" && $chatType === "private") {
	$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
	$selectUsersUtilities->execute([":user_id" => $userId]);
	$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersUtilities) {
		try {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $userId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			sendMessage($chatId, "🥾 [ <code>SHADOW STEP</code> ] (Level <b>" . $rowUsersSpecializations["shadow_step"] . "</b>/50)\nAffects your (Evade).\n\nℹ️ Each level of this specialization increases your total (Evade) by +<b>0.10%</b>. You have currently gained a total bonus of +<b>" . number_format($rowUsersSpecializations["shadow_step"] * 0.10) . "</b>% (Evade) from this specialization.", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"⏏️ Upgrade","callback_data":"specialization_shadow_step_upgrade"}]],"resize_keyboard":true}');
		} catch (Exception) {
			exit;
		}
	}
}

if ($queryData === "specialization_shadow_step_upgrade") {
	$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section = 'Specializations' AND user_id = :user_id");
	$selectUsersUtilities->execute([":user_id" => $userId]);
	$rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC);
	if ($rowUsersUtilities) {
		try {
			$selectUsersSpecializations = $pdo->prepare("SELECT * FROM users_specializations WHERE user_id = :user_id");
			$selectUsersSpecializations->execute([":user_id" => $queryUserId]);
			$rowUsersSpecializations = $selectUsersSpecializations->fetch(PDO::FETCH_ASSOC);
			if ($rowUsersSpecializations["shadow_step"] < 50) {
				if ($rowUsersSpecializations["points"] > 0) {
					$updateUsersSpecializations = $pdo->prepare("UPDATE users_specializations SET shadow_step = shadow_step + 1, points = points - 1 WHERE user_id = :user_id");
					$updateUsersSpecializations->execute([":user_id" => $queryUserId]);
					$updateUsersStatistics = $pdo->prepare("UPDATE users_statistics_bonuses SET evade = evade + 0.10 WHERE user_id = :user_id");
					$updateUsersStatistics->execute([":user_id" => $queryUserId]);
					answerCallbackQuery($queryId, "🥾 (Shadow Step) specialization successfully upgraded!");
					editMessageText($queryUserId, $queryMessageId, "🥾 [ <code>SHADOW STEP</code> ] (Level <b>" . ($rowUsersSpecializations["shadow_step"] + 1) . "</b>/50)\nAffects your (Evade).\n\nℹ️ Each level of this specialization increases your total (Evade) by +<b>0.10%</b>. You have currently gained a total bonus of +<b>" . number_format(($rowUsersSpecializations["shadow_step"] + 1) * 0.10) . "</b>% (Evade) from this specialization.", false, '&reply_markup={"inline_keyboard":[[{"text":"⏏️ Upgrade","callback_data":"specialization_shadow_step_upgrade"}]],"resize_keyboard":true}');
				} else {
					answerCallbackQuery($queryId, "🫡 You don't have enough (Specialization) points.");
				}
			} else {
				answerCallbackQuery($queryId, "🥳 Congratulations! You have reached the maximum (Specializations) level.");
			}
		} catch (Exception) {
			exit;
		}
	}
}
