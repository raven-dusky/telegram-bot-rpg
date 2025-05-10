<?php
function systemLogs($pdo, $userId, $logLevel, $logMessage, $text = null, $queryData = null, $queryId = null): void {
    try {
        $backtrace = debug_backtrace();
        $errorFile = $backtrace[0]["file"];
        $errorLine = $backtrace[0]["line"];
        $insertSystemLogs = $pdo->prepare("INSERT INTO system_logs (user_id, level, message, file, line) VALUES (:user_id, :level, :message, :file, :line)");
        $insertSystemLogs->execute([
            ":user_id" => $userId,
            ":level"   => $logLevel,
            ":message" => $logMessage,
            ":file"    => $errorFile,
            ":line"    => $errorLine
        ]);
        if ($text !== null) {
            sendMessage($userId, "<code>[</code>❗️<code>]</code> An unexpected error occurred. It’s been reported and the admin has been notified. Please try again later.");
        } elseif ($queryData !== null && $queryId !== null) {
            answerCallbackQuery($queryId, "[❗️] An unexpected error occurred. It’s been reported and the admin has been notified. Please try again later.", true);
        }
    } catch (Exception) {
        exit;
    }
}
