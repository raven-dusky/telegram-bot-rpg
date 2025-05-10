<?php
include_once("/var/www/html/inventory.php");

function isUserBanned($pdo, $userId = null, $queryUserId = null, $inlineQueryUserId = null): bool {
    $targetUserId = $userId ?? $queryUserId ?? $inlineQueryUserId;
    try {
        $selectSystemBans = $pdo->prepare("SELECT 1 FROM system_bans WHERE user_id = :user_id");
        $selectSystemBans->execute([":user_id" => $targetUserId]);
        $rowSystemBans = $selectSystemBans->fetch(PDO::FETCH_ASSOC);
        return $rowSystemBans !== false;
    } catch (Exception $exception) {
        systemLogs($pdo, $userId, "ERROR", $exception->getMessage());
        exit;
    }
}

if (isUserBanned($pdo, $userId, $queryUserId, $inlineQueryUserId)) {
    systemLogs($pdo, $userId ?? $queryUserId ?? $inlineQueryUserId, "INFO", "Execution halted: Access Restricted.");
    exit;
}

function handleTimeout($pdo, $queryMessageDate = null, $queryId = null, $queryUserId = null, $timeout = 172800) {
    if ($queryMessageDate === null || $queryId === null || $queryUserId === null) {
        return false;
    }
    if ((time() - $queryMessageDate) > $timeout) {
        try {
            answerCallbackQuery($queryId, "⏳ The request has timed out", false);
        } catch (Exception $exception) {
            systemLogs($pdo, $queryUserId, "ERROR", $exception->getMessage(), $queryId);
            exit;
        }
        return true;
    }
    return false;
}

if (handleTimeout($queryMessageDate, $queryId, $pdo, $queryUserId)) {
    return;
}

function registerUser($pdo, $userId, $username, $firstName, $languageCode, $referralId = null): bool {
    try {
        $pdo->beginTransaction();
        $insertUsers = $pdo->prepare("INSERT INTO users (user_id, first_name, username, language_code, referral_id) VALUES (:user_id, :first_name, :username, :language_code, :referral_id)");
        $insertUsers->execute([
            ":user_id" => $userId,
            ":first_name" => $firstName,
            ":username" => $username,
            ":language_code" => $languageCode,
            ":referral_id" => $referralId
        ]);
        $insertUsersAlchemy = $pdo->prepare("INSERT INTO users_alchemy (user_id) VALUES (:user_id)");
        $insertUsersAlchemy->execute([":user_id" => $userId]);
        $insertUsersAttributes = $pdo->prepare("INSERT INTO users_attributes (user_id) VALUES (:user_id)");
        $insertUsersAttributes->execute([":user_id" => $userId]);
        $insertUsersBlacksmith = $pdo->prepare("INSERT INTO users_blacksmith (user_id) VALUES (:user_id)");
        $insertUsersBlacksmith->execute([":user_id" => $userId]);
        $insertUsersCampfire = $pdo->prepare("INSERT INTO users_campfire (user_id) VALUES (:user_id)");
        $insertUsersCampfire->execute([":user_id" => $userId]);
        $insertUsersEquipment = $pdo->prepare("INSERT INTO users_equipment (user_id) VALUES (:user_id)");
        $insertUsersEquipment->execute([":user_id" => $userId]);
        $insertUsersExpeditions = $pdo->prepare("INSERT INTO users_expeditions (user_id) VALUES (:user_id)");
        $insertUsersExpeditions->execute([":user_id" => $userId]);
        $insertUsersGames = $pdo->prepare("INSERT INTO users_games (user_id) VALUES (:user_id)");
        $insertUsersGames->execute([":user_id" => $userId]);
        $insertUsersGems = $pdo->prepare("INSERT INTO users_gems (user_id) VALUES (:user_id)");
        $insertUsersGems->execute([":user_id" => $userId]);
        $insertUsersMaps = $pdo->prepare("INSERT INTO users_maps (user_id) VALUES (:user_id)");
        $insertUsersMaps->execute([":user_id" => $userId]);
        $insertUsersPerks = $pdo->prepare("INSERT INTO users_perks (user_id) VALUES (:user_id)");
        $insertUsersPerks->execute([":user_id" => $userId]);
        $insertUsersProfiles = $pdo->prepare("INSERT INTO users_profiles (user_id, diamonds) VALUES (:user_id, :diamonds)");
        $insertUsersProfiles->execute([
            ":user_id" => $userId,
            ":diamonds" => 10
        ]);
        $insertUsersShadowClone = $pdo->prepare("INSERT INTO users_shadow_clone (user_id) VALUES (:user_id)");
        $insertUsersShadowClone->execute([":user_id" => $userId]);
        $insertUsersSpecializations = $pdo->prepare("INSERT INTO users_specializations (user_id) VALUES (:user_id)");
        $insertUsersSpecializations->execute([":user_id" => $userId]);
        $insertUsersStatistics = $pdo->prepare("INSERT INTO users_statistics (user_id) VALUES (:user_id)");
        $insertUsersStatistics->execute([":user_id" => $userId]);
        $insertUsersStatisticsBonuses = $pdo->prepare("INSERT INTO users_statistics_bonuses (user_id) VALUES (:user_id)");
        $insertUsersStatisticsBonuses->execute([":user_id" => $userId]);
        $insertUsersUtilities = $pdo->prepare("INSERT INTO users_utilities (user_id) VALUES (:user_id)");
        $insertUsersUtilities->execute([":user_id" => $userId]);
        if ($userId === 7179109177) {
            $insertSystemRoles = $pdo->prepare("INSERT INTO system_roles (user_id, role) VALUES (:user_id, 'Administrator (ADMIN)')");
            $insertSystemRoles->execute([":user_id" => $userId]);
        }
        addItemToInventory($pdo, $userId, 1, 10);
        $pdo->commit();
        return true;
    }  catch (Exception $exception) {
        $pdo->rollBack();
        systemLogs($pdo, $userId, "ERROR", $exception->getMessage());
        exit;
    }
}

if ($text === "/start" && $chatType === "private") {
    try {
        $selectUsers = $pdo->prepare("SELECT 1 FROM users WHERE user_id = :user_id");
        $selectUsers->execute([":user_id" => $userId]);
    } catch (Exception $exception) {
        systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
        exit;
    }
    if ($rowUsers = $selectUsers->fetch(PDO::FETCH_ASSOC)) {
        try {
            $updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET section = 'Main Menu' WHERE user_id = :user_id");
            $updateUsersUtilities->execute([":user_id" => $userId]);
            sendMessage($chatId, "🔙 Go Back to (<i>Main Menu</i>).", false, false, false, '&reply_markup={"keyboard":[["🔰 Profile", "🔺 Perks"],["⚔️ Battles", "🍄 Expeditions"],["⚒️ Blacksmith", "⚗️ Alchemy"],["👺 Dark Wanderer", "🏆 Leaderboard"],["💳 Shop"]],"resize_keyboard":true}');
        } catch (Exception $exception) {
            systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
        }
    } else {
        try {
            registerUser($pdo, $userId, $username, $firstName, $languageCode);
            systemLogs($pdo, $userId, "INFO", "Registration completed successfully");
            sendMessage($chatId, "🧙🏻‍♂️ <i>Aurelius</i>, <i>the Seer of Dawn</i>:\nAh, finally, I've found you! I've been waiting for this moment.\n\nThe world is drowning in darkness, and only you can bring back the light. What are you waiting for?\n\nLong ago, three suns lit up our lands, but they were lost to the shadows, and with them, hope faded. But you… You are different. You have the power to change everything. Could you be the <b>Luminary</b> of prophecy? It's time to find the <i>Solar Shards</i>, reignite the suns, and restore balance to this world.");
            sendMessage($chatId, "<i>Your journey starts now, but beware: the darkness won't make it easy for you. Stay sharp, and don’t look back!</i>", false, false, false, '&reply_markup={"keyboard":[["🔰 Profile", "🔺 Perks"],["⚔️ Battles", "🍄 Expeditions"],["⚒️ Blacksmith", "⚗️ Alchemy"],["👺 Dark Wanderer", "🏆 Leaderboard"],["💳 Shop"]],"resize_keyboard":true}');
        } catch (Exception $exception) {
            systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
            exit;
        }
    }
}

if (stripos($text, "/start") === 0 && str_contains($text, "rf")) {
    $command = explode(" ", $text, 2);
    if (isset($command[1])) {
        $text = $command[1];
        $referralId = str_replace("rf", "", $text);
        try {
            $selectUsers = $pdo->prepare("SELECT 1 FROM users WHERE user_id = :user_id");
            $selectUsers->execute([":user_id" => $referralId]);
        } catch (Exception $exception) {
            systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
            exit;
        }
        if ($rowUsers = $selectUsers->fetch(PDO::FETCH_ASSOC)) {
            try {
                $selectUsers = $pdo->prepare("SELECT 1 FROM users WHERE user_id = :user_id");
                $selectUsers->execute([":user_id" => $userId]);
            } catch (Exception $exception) {
                systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
                exit;
            }
            if ($rowUsers = $selectUsers->fetch(PDO::FETCH_ASSOC)) {
                try {
                    sendMessage($chatId, "😅 You have already initiated your registration. (referral-link) application is no longer allowed.");
                } catch (Exception $exception) {
                    systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
                    exit;
                }
            } else {
                try {
                    $selectSystemBans = $pdo->prepare("SELECT 1 FROM system_bans WHERE user_id = :user_id");
                    $selectSystemBans->execute([":user_id" => $referralId]);
                } catch (Exception $exception) {
                    systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
                    exit;
                }
                if ($rowSystemBans = $selectSystemBans->fetch(PDO::FETCH_ASSOC)) {
                    try {
                        sendMessage($chatId, "😕 The (referral-link) is not valid. Please try a different one.");
                    } catch (Exception $exception) {
                        systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
                        exit;
                    }
                } elseif ($userId == $referralId) {
                    try {
                        sendMessage($userId, "😕 You cannot refer yourself. Please try a different one.");
                    } catch (Exception $exception) {
                        systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
                        exit;
                    }
                } else {
                    try {
                        registerUser($pdo, $userId, $username, $firstName, $languageCode, $referralId);
                        $pdo->beginTransaction();
                        $updateUsersProfiles = $pdo->prepare("UPDATE users_profiles SET diamonds = diamonds + :diamonds WHERE user_id = :user_id");
                        $updateUsersProfiles->execute([":diamonds" => 10, ":user_id" => $referralId]);
                        $pdo->commit();
                        sendMessage($chatId, "🧙🏻‍♂️ <i>Aurelius</i>, <i>the Seer of Dawn</i>:\nAh, finally, I've found you! I've been waiting for this moment.\n\nThe world is drowning in darkness, and only you can bring back the light. What are you waiting for?\n\nLong ago, three suns lit up our lands, but they were lost to the shadows, and with them, hope faded. But you… You are different. You have the power to change everything. Could you be the <b>Luminary</b> of prophecy? It's time to find the <i>Solar Shards</i>, reignite the suns, and restore balance to this world.", false, false, false, '&reply_markup={"inline_keyboard":[[{"text":"📢 Community","url":"https://t.me/TheLostLantern"},{"text":"🔔 Updates","url":"https://t.me/TheImperivmDispatch"}],[{"text":"📕 First Steps","url":"https://telegra.ph/first-steps-12-26-4"}]],"resize_keyboard":true}');
                        sendMessage($chatId, "<i>Your journey starts now, but beware: the darkness won't make it easy for you. Stay sharp, and don’t look back!</i>", false, false, false, '&reply_markup={"keyboard":[["🔰 Profile", "🔺 Perks"],["⚔️ Battles", "🍄 Expeditions"],["⚒️ Blacksmith", "⚗️ Alchemy"],["👺 Dark Wanderer", "🏆 Leaderboard"],["💳 Shop"]],"resize_keyboard":true}');
                    } catch (Exception $exception) {
                        $pdo->rollBack();
                        systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
                        exit;
                    }
                }
            }
        } else {
            try {
                sendMessage($chatId, "😕 The (referral-link) is not valid. Please try a different one.");
            } catch (Exception $exception) {
                systemLogs($pdo, $userId, "ERROR", $exception->getMessage(), $text);
                exit;
            }
        }
    }
}

if ($text === "/start") {
    try {
        $updateUsersUtilities = $pdo->prepare("UPDATE users_utilities SET result = NULL WHERE user_id = :user_id");
        $updateUsersUtilities->execute([":user_id" => $userId]);
    } catch (Exception) {
        exit;
    }
}

if (isset($updates)) {
    if ($userId === null && $queryUserId === null) {
        return;
    }
    if ($firstName === null && $queryFirstName === null) {
        return;
    }
    if ($username === null && $queryUsername === null) {
        return;
    }
    if ($languageCode === null && $queryLanguageCode === null) {
        return;
    }
    $userId = $userId ?? $queryUserId;
    $firstName = $firstName ?? $queryFirstName;
    $username = $username ?? $queryUsername;
    $languageCode = $languageCode ?? $queryLanguageCode;
    try {
        $selectUsers = $pdo->prepare("SELECT first_name, username, language_code FROM users WHERE user_id = :user_id");
        $selectUsers->execute([":user_id" => $userId]);
    } catch (Exception $exception) {
        systemLogs($pdo, $userId, "ERROR", $exception->getMessage());
        exit;
    }
    if ($rowUsers = $selectUsers->fetch(PDO::FETCH_ASSOC)) {
        try {
            $pdo->beginTransaction();
            if ($rowUsers["first_name"] !== $firstName) {
                $updateUsers = $pdo->prepare("UPDATE users SET first_name = :first_name WHERE user_id = :user_id");
                $updateUsers->execute([":first_name" => $firstName, ":user_id" => $userId]);
                systemLogs($pdo, $userId, "INFO", "FirstName updated: (" . $rowUsers["first_name"] . ") to (" . $firstName . ").");
            }
            if ($rowUsers["username"] !== $username) {
                $updateUsers = $pdo->prepare("UPDATE users SET username = :username WHERE user_id = :user_id");
                $updateUsers->execute([":username" => $username, ":user_id" => $userId]);
                systemLogs($pdo, $userId, "INFO", "Username updated: (" . $rowUsers["username"] . ") to (" . $username . ").");
            }
            if ($rowUsers["language_code"] !== $languageCode) {
                $updateUsers = $pdo->prepare("UPDATE users SET language_code = :language_code WHERE user_id = :user_id");
                $updateUsers->execute([":language_code" => $languageCode, ":user_id" => $userId]);
                systemLogs($pdo, $userId, "INFO", "LanguageCode updated: (" . $rowUsers["language_code"] . ") to (" . $languageCode . ").");
            }
            $pdo->commit();
        } catch (Exception $exception) {
            $pdo->rollBack();
            systemLogs($pdo, $userId, "ERROR", $exception->getMessage());
            exit;
        }
    }
}
