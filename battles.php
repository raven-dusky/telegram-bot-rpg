<?php
if ($text === "⚔️ Battles" && $chatType === "private") {
	try {
		$selectUsersUtilities = $pdo->prepare("SELECT 1 FROM users_utilities WHERE section IN ('Main Menu', 'Dark Wanderer', 'Expeditions') AND user_id = :user_id");
		$selectUsersUtilities->execute([":user_id" => $userId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
	if ($rowUsersUtilities = $selectUsersUtilities->fetch(PDO::FETCH_ASSOC)) {
		try {
			$updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Battles' WHERE user_id = :user_id");
			$updateUsersUtilities->execute([":user_id" => $userId]);
			sendMessage($chatId, "⚔️ [ <code>BATTLES</code> ]\nChallenge fierce monsters and enemies to earn valuable experience, coins, and items. Prove your strength and rise through the ranks!\n\n📌 <b>Maps</b> - Explore diverse regions, each filled with unique monsters and challenges. Conquer stages to unlock new areas and rewards!", false, false, false, '&reply_markup={"keyboard":[["📌 Maps"],["🔙 Go Back"]],"resize_keyboard":true}');
		} catch (Exception $exception) {
			systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
			exit;
		}
	}
}
