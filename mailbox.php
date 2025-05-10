<?php
include_once("/var/www/html/inventory.php");

if (isset($inlineQuery)) {
	$selectUsers = $pdo->prepare("SELECT 1 FROM users WHERE user_id = :user_id");
	$selectUsers->execute([":user_id" => $inlineQueryUserId]);
	if ($rowUsers = $selectUsers->fetch(PDO::FETCH_ASSOC)) {
		if (preg_match("/(.+?)\s+([\d\.]+)(?:\s+([\d\.]+))?/", $inlineQuery, $matches)) {
			$itemName = strtolower(trim($matches[1]));
			if ($itemName == strtolower("coins")) {
				$itemQuantity = floor((float)$matches[2] * 100) / 100;
				if ($itemQuantity >= 0.01) {
					$selectUsersProfiles = $pdo->prepare("SELECT * FROM users_profiles WHERE coins >= :coins AND user_id = :user_id");
					$selectUsersProfiles->execute([":coins" => $itemQuantity, ":user_id" => $inlineQueryUserId]);
					if ($rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC)) {
						$messageText = "[ <code>TRANSACTION SUMMARY</code> ]\n<code>#$inlineQueryId</code>\n\n💰 Amount: <b>" . number_format($itemQuantity, 2, ".", "") . "</b> (<i>Coins</i>)\n\n<i>This transaction will expire automatically in 24 hours.</i>";
						$resultId = "{$inlineQueryId}_{$inlineQueryUserId}_Coins_{$itemQuantity}";
						$results = [
							[
								"type" => "article",
								"id" => $resultId,
								"title" => "\u{200B}x" . number_format($itemQuantity, 2, ".", "") . " (Coins)",
								"input_message_content" => [
									"message_text" => $messageText,
									"parse_mode" => "HTML"
								],
								"reply_markup" => [
									"inline_keyboard" => [
										[
											[
												"text" => "[ APPROVE TRANSFER ]",
												"callback_data" => "mailbox_{$inlineQueryId}"
											]
										]
									]
								],
							]
						];
						answerInlineQuery($inlineQueryId, $results);
					}
				}
			} else {
				$itemQuantity = (int)$matches[2];
				$itemPrice = isset($matches[3]) ? (float)$matches[3] : null;
			}
			$selectItems = $pdo->prepare("SELECT * FROM items WHERE name LIKE :name");
			$selectItems->execute([":name" => $itemName]);
			if ($rowItems = $selectItems->fetch(PDO::FETCH_ASSOC)) {
				if ($rowItems["is_tradable"] == 1) {
					if ($itemQuantity <= 0 || $itemQuantity > 2147483647 || ($itemPrice !== null && ($itemPrice <= 0.01 || $itemPrice > 1.7976931348623157E+308))) {
						$results = [];
					} else {
						if ($rowItems["is_stackable"] == 1) {
							$selectUsersInventory = $pdo->prepare("SELECT * FROM users_inventory WHERE item_id = :item_id AND quantity >= :quantity AND user_id = :user_id");
							$selectUsersInventory->execute([
								":item_id" => $rowItems["id"],
								":quantity" => $itemQuantity,
								":user_id" => $inlineQueryUserId
							]);
							$rowUsersInventory = $selectUsersInventory->fetch(PDO::FETCH_ASSOC);
						} else {
							$selectUsersInventory = $pdo->prepare("SELECT * FROM users_inventory WHERE item_id = :item_id AND user_id = :user_id");
							$selectUsersInventory->execute([":item_id" => $rowItems["id"], ":user_id" => $inlineQueryUserId]);
							$rowUsersInventory = $selectUsersInventory->rowCount();
							if ($rowUsersInventory >= $itemQuantity) {
								$rowUsersInventory = true;
							} else {
								$rowUsersInventory = false;
							}
						}
						if ($rowUsersInventory) {
							$messageText = "[ <code>TRANSACTION SUMMARY</code> ]\n<code>#$inlineQueryId</code>\n\n{$rowItems["icon"]} Item: <i>{$rowItems["name"]}</i>\n📦 Quantity: x<b>" . number_format($itemQuantity) . "</b>";
							$description = $itemPrice !== null ? number_format($itemPrice, 2, ".", "") . " (Coins)" : null;
							if ($itemPrice !== null) {
								$pricePerUnit = $itemPrice / $itemQuantity;
								$messageText .= "\n💰 Price: " . number_format($itemPrice, 2, ".", "") . " (Coins)";
								$messageText .= "\n🪙 " . number_format($pricePerUnit, 2, ".", "") . " (ea)";
							}
							$messageText .= "\n\n<i>This transaction will expire automatically in 24 hours.</i>";
							$resultId = "{$inlineQueryId}_{$inlineQueryUserId}_{$rowItems["name"]}_{$itemQuantity}_{$itemPrice}";
							$results = [
								[
									"type" => "article",
									"id" => $resultId,
									"title" => "\u{200B}x" . number_format($itemQuantity) . " ({$rowItems["name"]})",
									"input_message_content" => [
										"message_text" => $messageText,
										"parse_mode" => "HTML"
									],
									"reply_markup" => [
										"inline_keyboard" => [
											[
												[
													"text" => "[ APPROVE TRANSFER ]",
													"callback_data" => "mailbox_{$inlineQueryId}"
												]
											]
										]
									],
								]
							];
							if ($itemPrice !== null) {
								$results[0]["description"] = $description;
							}
						} else {
							$results = [];
						}	
					}
					answerInlineQuery($inlineQueryId, $results);
				}
			}
		}
	}
}

if (isset($chosenResult)) {
	$chosenResult = trim($chosenResultId, "_");
	$chosenParts = explode("_", $chosenResult);
	if (count($chosenParts) < 4) {
		systemLogs($pdo, 7777, "ERROR", "Malformed chosenResultId: $chosenResultId");
		exit;
	}
	$inlineQueryId = $chosenParts[0];
	$senderId = $chosenParts[1];
	$itemName = $chosenParts[2];
	$itemQuantity = (int)$chosenParts[3];
	$itemPrice = isset($chosenParts[4]) && $chosenParts[4] !== "" ? (float)$chosenParts[4] : null;
	try {
		$selectUsers = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
		$selectUsers->execute([":user_id" => $senderId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $senderId, "ERROR", $exception->getMessage());
		exit;
	}
	if ($rowUsers = $selectUsers->fetch(PDO::FETCH_ASSOC)) {
		if ($itemName === "Coins") {
			try {
				$pdo->beginTransaction();
				$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET coins = coins - :coins WHERE user_id = :user_id");
				$updateUsersProfiles->execute([":coins" => $itemQuantity, ":user_id" => $senderId]);
				$insertMailbox = $pdo->prepare("INSERT INTO mailbox (mailbox_id, sender_id, quantity, expiration_datetime) VALUES (:mailbox_id, :sender_id, :quantity, :expiration_datetime)");
				$insertMailbox->execute([
					":mailbox_id" => $inlineQueryId,
					":sender_id" => $senderId,
					":quantity" => $itemQuantity,
					":expiration_datetime" => date("Y-m-d H:i:s", strtotime("+1 days"))
				]);
				$pdo->commit();
				systemLogs($pdo, $senderId, "INFO", "[ TRANSFERRED AMOUNT ]: $itemQuantity (Coins) from ($senderId).");
			} catch (Exception $exception) {
				$pdo->rollBack();
				systemLogs($pdo, $senderId, "ERROR", $exception->getMessage());
				exit;
			}
		} else {
			$itemPrice = $itemPrice === "" ? null : $itemPrice;
			$selectItems = $pdo->prepare("SELECT * FROM items WHERE name LIKE :name");
			$selectItems->execute([":name" => $itemName]);
			if ($rowItems = $selectItems->fetch(PDO::FETCH_ASSOC)) {
				try {
					$pdo->beginTransaction();
					if ($rowItems["is_stackable"] == 1) {
						removeItemFromInventory($pdo, $senderId, $rowItems["id"], $itemQuantity);
					} else {
						for ($i = 0; $i < $itemQuantity; $i++) {
							removeItemFromInventory($pdo, $senderId, $rowItems["id"]);
						}
					}
					$insertMailbox = $pdo->prepare("INSERT INTO mailbox (mailbox_id, sender_id, item_id, quantity, price, expiration_datetime) VALUES (:mailbox_id, :sender_id, :item_id, :quantity, :price, :expiration_datetime)");
					$insertMailbox->execute([
						":mailbox_id" => $inlineQueryId,
						":sender_id" => $senderId,
						":item_id" => $rowItems["id"],
						":quantity" => $itemQuantity,
						":price" => $itemPrice,
						":expiration_datetime" => date("Y-m-d H:i:s", strtotime("+1 day"))
					]);
					$pdo->commit();
				} catch (Exception $exception) {
					$pdo->rollBack();
					systemLogs($pdo, $senderId, "ERROR", $exception->getMessage());
					exit;
				}
			}
		}
	}
}

if (isset($queryData) && str_contains($queryData, "mailbox_")) {
	try {
		$selectUsers = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
		$selectUsers->execute([":user_id" => $queryUserId]);
	} catch (Exception $exception) {
		systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage());
		exit;
	}
	if ($rowUsers = $selectUsers->fetch(PDO::FETCH_ASSOC)) {
		$inlineQueryId = str_replace("mailbox_", "", $queryData);
		try {
			$selectMailbox = $pdo->prepare("SELECT * FROM mailbox WHERE mailbox_id = :mailbox_id AND expiration_datetime >= NOW()");
			$selectMailbox->execute([":mailbox_id" => $inlineQueryId]);
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage());
			exit;
		}
		if ($rowMailbox = $selectMailbox->fetch(PDO::FETCH_ASSOC)) {
			if ($rowMailbox["sender_id"] !== $queryUserId) {
				if ($rowMailbox["item_id"] !== null) {
					if ($rowMailbox["price"] !== null) {
						try {
							$selectUsersProfiles = $pdo->prepare("SELECT * FROM users_profiles WHERE coins >= :coins AND user_id = :user_id");
							$selectUsersProfiles->execute([":coins" => $rowMailbox["price"], ":user_id" => $queryUserId]);
							$selectItems = $pdo->prepare("SELECT * FROM items WHERE id = :id");
							$selectItems->execute([":id" => $rowMailbox["item_id"]]);
							$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
						} catch (Exception $exception) {
							systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage());
							exit;
						}
						if ($rowUsersProfiles = $selectUsersProfiles->fetch(PDO::FETCH_ASSOC)) {
							try {
								$pdo->beginTransaction();
								if ($rowItems["is_stackable"] == 1) {
									addItemToInventory($pdo, $queryUserId, $rowMailbox["item_id"], $rowMailbox["quantity"]);
								} else {
									for ($i = 0; $i < $rowMailbox["quantity"]; $i++) {
										addItemToInventory($pdo, $queryUserId, $rowMailbox["item_id"], $rowMailbox["quantity"]);
									}
								}
								$pdo->commit();
								$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET coins = coins - :coins WHERE user_id = :user_id");
								$updateUsersProfiles->execute([":coins" => $rowMailbox["price"], ":user_id" => $queryUserId]);
								$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET coins = coins + :coins WHERE user_id = :user_id");
								$updateUsersProfiles->execute([":coins" => $rowMailbox["price"], ":user_id" => $rowMailbox["sender_id"]]);
								$deleteMailbox = $pdo->prepare("DELETE FROM mailbox WHERE mailbox_id = :mailbox_id");
								$deleteMailbox->execute([":mailbox_id" => $rowMailbox["mailbox_id"]]);
								editInlineMessage($queryInlineMessageId, "🟢 [ <code>TRANSACTION COMPLETE</code> ]\n<code>#$inlineQueryId</code>\n\n<i>The requested items have been successfully transferred.</i>");
								answerCallbackQuery($queryId, "🤑 Transaction successful! The item has been added to your inventory!");
								sendMessage($rowMailbox["sender_id"], "🟢 [ <code>TRANSACTION COMPLETE</code> ]\n<code>#$inlineQueryId</code>\n\n<i>The requested amount of " . number_format($rowMailbox["price"], 2, ".", "") . " (Coins) has been successfully transferred to your account</i>.");
							} catch (Exception $exception) {
								$pdo->rollBack();
								systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage());
								exit;
							}
						} else {
							try {
								answerCallbackQuery($queryId, "😨 You don’t have enough (Coins) to proceed with this (Transaction).");
							} catch (Exception $exception) {
								systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage());
								exit;
							}
						}
					} else {
						try {
							$selectItems = $pdo->prepare("SELECT * FROM items WHERE id = :id");
							$selectItems->execute([":id" => $rowMailbox["item_id"]]);
							$rowItems = $selectItems->fetch(PDO::FETCH_ASSOC);
						} catch (Exception $exception) {
							systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage());
							exit;
						}
						try {
							$pdo->beginTransaction();
							if ($rowItems["is_stackable"] == 1) {
								addItemToInventory($pdo, $queryUserId, $rowMailbox["item_id"], $rowMailbox["quantity"]);
							} else {
								for ($i = 0; $i < $rowMailbox["quantity"]; $i++) {
									addItemToInventory($pdo, $queryUserId, $rowMailbox["item_id"], $rowMailbox["quantity"]);
								}
							}
							$deleteMailbox = $pdo->prepare("DELETE FROM mailbox WHERE mailbox_id = :mailbox_id");
							$deleteMailbox->execute([":mailbox_id" => $rowMailbox["mailbox_id"]]);
							$pdo->commit();
							editInlineMessage($queryInlineMessageId, "🟢 [ <code>TRANSACTION COMPLETE</code> ]\n<code>#$inlineQueryId</code>\n\n<i>The requested items have been successfully transferred.</i>");
							answerCallbackQuery($queryId, "🤑 Transaction successful! The item has been added to your inventory!");
						} catch (Exception $exception) {
							$pdo->rollBack();
							systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage());
							exit;
						}
					}
				} else {
					try {
						$pdo->beginTransaction();
						$updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET coins = coins + :coins WHERE user_id = :user_id");
						$updateUsersProfiles->execute([":coins" => $rowMailbox["quantity"], ":user_id" => $queryUserId]);
						$deleteMailbox = $pdo->prepare("DELETE FROM mailbox WHERE mailbox_id = :mailbox_id");
						$deleteMailbox->execute([":mailbox_id" => $rowMailbox["mailbox_id"]]);
						$pdo->commit();
						systemLogs($pdo, $queryUserId, "INFO", "[ APPROVED TRANSATION ]: ($queryUserId) has approved the transaction and successfully received: " . $rowMailbox["quantity"]);
						editInlineMessage($queryInlineMessageId, "🟢 [ <code>TRANSACTION COMPLETE</code> ]\n<code>#$inlineQueryId</code>\n\n<i>The requested items have been successfully transferred.</i>");
						answerCallbackQuery($queryId, "🤑 Transaction successful! The coins have been added to your balance.");
					} catch (Exception $exception) {
						$pdo->rollBack();
						systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage());
						exit;
					}
				}
			} else {
				try {
					answerCallbackQuery($queryId, "😵‍💫 You cannot complete a (Transaction) with yourself.");
				} catch (Exception $exception) {
					systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage());
					exit;
				}
			}
		} else {
			try {
				answerCallbackQuery($queryId, "😞 The (Transaction) has expired and is no longer available.");
			} catch (Exception $exception) {
				systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage());
				exit;
			}
		}
	} else {
		try {
			answerCallbackQuery($queryId, "📋 Only registered players can complete (Transactions).");
		} catch (Exception $exception) {
			systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage());
			exit;
		}
	}
}
