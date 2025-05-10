<?php
function sendMessage($chatId, $text, $disableWebPagePreview = false, $disableNotification = false, $protectContent = false, $keyboard = null): void {
    $url = WEBSITE . '/sendMessage?chat_id=' . $chatId . '&disable_web_page_preview=' . $disableWebPagePreview . '&disable_notification=' . $disableNotification . '&protect_content=' . $protectContent . '&parse_mode=HTML&text=' . urlencode($text);
    if ($keyboard) {
        $url .= $keyboard;
    }
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true,
            'timeout' => 5,
        ],
    ]);
    $response = file_get_contents($url, false, $context);
    if ($response === false || (isset($http_response_header[0]) && str_contains($http_response_header[0], '200') === false)) {
        $errorResponse = json_decode($response, true);
        if (is_array($errorResponse) && isset($errorResponse['description'])) {
            throw new Exception('Telegram API: request failed (sendMessage) ' . $errorResponse['description']);
        }
    }
}

function editMessageText($chatId, $messageId, $text, $disableWebPagePreview = false, $keyboard = null): void {
    $url = WEBSITE . '/editMessageText?chat_id=' . $chatId . '&message_id=' . $messageId . '&disable_web_page_preview=' . $disableWebPagePreview . '&parse_mode=HTML&text=' . urlencode($text);
    if ($keyboard) {
        $url .= $keyboard;
    }
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true,
            'timeout' => 5,
        ],
    ]);
    $response = file_get_contents($url, false, $context);
    if ($response === false || (isset($http_response_header[0]) && str_contains($http_response_header[0], '200') === false)) {
        $errorResponse = json_decode($response, true);
        if (is_array($errorResponse) && isset($errorResponse['description'])) {
            throw new Exception('Telegram API: request failed (editMessageText) ' . $errorResponse['description']);
        }
    }
}

function editMessageReplyMarkup($chatId, $messageId, $keyboard = null): void {
    $url = WEBSITE . '/editMessageReplyMarkup?chat_id=' . $chatId . '&message_id=' . $messageId;
    if ($keyboard) {
        $url .= $keyboard;
    }
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true,
            'timeout' => 5,
        ],
    ]);
    $response = file_get_contents($url, false, $context);
    if ($response === false || (isset($http_response_header[0]) && str_contains($http_response_header[0], '200') === false)) {
        $errorResponse = json_decode($response, true);
        if (is_array($errorResponse) && isset($errorResponse['description'])) {
            throw new Exception('Telegram API: request failed (editMessageReplyMarkup) ' . $errorResponse['description']);
        }
    }
}

function editInlineMessage($inlineMessageId, $text): void {
    $url = WEBSITE . '/editMessageText?inline_message_id=' . $inlineMessageId . '&parse_mode=HTML&text=' . urlencode($text);
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true,
            'timeout' => 5,
        ],
    ]);
    $response = file_get_contents($url, false, $context);
    if ($response === false || (isset($http_response_header[0]) && str_contains($http_response_header[0], '200') === false)) {
        $errorResponse = json_decode($response, true);
        if (is_array($errorResponse) && isset($errorResponse['description'])) {
            throw new Exception('Telegram API: request failed (editInlineMessage) ' . $errorResponse['description']);
        }
    }
}

function sendPhoto($chatId, $fileId, $caption, $keyboard = null): void {
    $url = WEBSITE . '/sendPhoto?chat_id=' . $chatId . '&photo=' . $fileId . '&parse_mode=HTML&caption=' . urlencode($caption);
    if ($keyboard) {
        $url .= $keyboard;
    }
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true,
            'timeout' => 5,
        ],
    ]);
    $response = file_get_contents($url, false, $context);
    if ($response === false || (isset($http_response_header[0]) && str_contains($http_response_header[0], '200') === false)) {
        $errorResponse = json_decode($response, true);
        if (is_array($errorResponse) && isset($errorResponse['description'])) {
            throw new Exception('Telegram API: request failed (sendPhoto) ' . $errorResponse['description']);
        }
    }
}

function answerCallbackQuery($queryId, $text, $showAlert = true): void {
    $url = WEBSITE . '/answerCallbackQuery?callback_query_id=' . $queryId . '&show_alert=' . $showAlert . '&text=' . urlencode($text);
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true,
            'timeout' => 5,
        ],
    ]);
    $response = file_get_contents($url, false, $context);
    if ($response === false || (isset($http_response_header[0]) && str_contains($http_response_header[0], '200') === false)) {
        $errorResponse = json_decode($response, true);
        if (is_array($errorResponse) && isset($errorResponse['description'])) {
            throw new Exception('Telegram API: request failed (answerCallbackQuery) ' . $errorResponse['description']);
        }
    }
}

function leaveChat($chatId): void {
    $url = WEBSITE . '/leaveChat?chat_id=' . $chatId;
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true,
            'timeout' => 5,
        ],
    ]);
    $response = file_get_contents($url, false, $context);
    if ($response === false || (isset($http_response_header[0]) && str_contains($http_response_header[0], '200') === false)) {
        $errorResponse = json_decode($response, true);
        if (is_array($errorResponse) && isset($errorResponse['description'])) {
            throw new Exception('Telegram API: request failed (leaveChat) ' . $errorResponse['description']);
        }
    }
}
