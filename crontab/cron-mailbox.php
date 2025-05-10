<?php
require_once("/var/www/html/index.php");

try {
	$selectMailbox = $pdo->prepare("SELECT * FROM mailbox WHERE expiration_datetime <= NOW()");
	$selectMailbox->execute();
} catch (Exception) {
	exit;
}

while ($rowMailbox = $selectMailbox->fetch(PDO::FETCH_ASSOC)) {
	if ($rowMailbox["item_id"] !== null) {
		try {
			$selectItems = $pdo->prepare("SELECT * FROM items WHERE id = :id");
			$selectItems->execute([":id" => $rowMailbox["item_id"]]);
			$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
			$pdo->beginTransaction();
			if ($rowItems["is_stackable"] == 1) {
				addItemToInventory($pdo, $rowMailbox["sender_id"], $rowMailbox["item_id"], $rowMailbox["quantity"]);
			} else {
				for ($i = 0; $i < $rowMailbox["quantity"]; $i++) {
					addItemToInventory($pdo, $rowMailbox["sender_id"], $rowMailbox["item_id"], $rowMailbox["quantity"]);
				}
			}
			$pdo->commit();
		} catch (Exception) {
			$pdo->rollBack();
		}
	} else {
		try {
			$pdo->beginTransaction();
			$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET coins = coins + :coins WHERE user_id = :user_id");
			$updateUsersProfiles->execute([":coins" => $rowMailbox["item_quantity"],":user_id" => $rowMailbox["sender_id"]]);
			$deleteMailbox = $pdo->prepare("DELETE FROM mailbox WHERE mailbox_id = :mailbox_id");
			$deleteMailbox->execute([":mailbox_id" => $rowMailbox["mailbox_id"]]);
			$pdo->commit();
		} catch (Exception) {
			$pdo->rollBack();
			exit;
		}
	}
}
