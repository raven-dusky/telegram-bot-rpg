<?php
if ($text === "/settings" && $chatType === "private") {
	try {
		sendMessage($chatId, "⚙️ [ <code>SETTINGS</code> ]\n\n▪️ 💌 <b>Invite Link</b> - Share your personal invitation link to bring friends into the adventure and earn exclusive rewards for both of you!", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"🔗 Get Link","callback_data":"settings_invite_link"}]],"resize_keyboard":true}');
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
		exit;
	}
}

if ($queryData === "settings_invite_link") {
	try {
		editMessageText($queryUserId, $queryMessageId, "🔗 [ <code>INVITE LINK</code> ]\nHere's your personal invitation link! 🎉\nInvite your friends to join the adventure, and each time they start playing, you’ll receive 💎 <b>10</b> (<i>Diamonds</i>) as a reward.\n\n➔ <b>Your Link:</b> https://t.me/NoctisImperivmBot?start=rf$queryUserId\n\n<i>Share the journey and collect rewards together!</i>");
	} catch (Exception $exception) {
		systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $queryId);
		exit;
	}
}
