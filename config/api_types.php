<?php
$text = $updates['message']['text'] ?? null;
$messageId = $updates['message']['message_id'] ?? null;
$userId = $updates['message']['from']['id'] ?? null;
$username = $updates['message']['from']['username'] ?? null;
$firstName = $updates['message']['from']['first_name'] ?? null;
$languageCode = $updates['message']['from']['language_code'] ?? null;
$chatId = $updates['message']['chat']['id'] ?? null;
$chatType = $updates['message']['chat']['type'] ?? null;
$fileId = $updates['message']['file_id'] ?? null;
$caption = $updates['message']['caption'] ?? null;

$newChatMembers = $updates['message']['new_chat_members'] ?? null;
$newChatMembersFirstName = $updates['message']['new_chat_members'][0]['first_name'] ?? null;
$newChatMembersLastName = $updates['message']['new_chat_members'][0]['last_name'] ?? null;
$newChatMembersUsername = $updates['message']['new_chat_members'][0]['username'] ?? null;
$newChatMembersId = $updates['message']['new_chat_members'][0]['id'] ?? null;
$newChatMembersLanguageCode = $updates['message']['new_chat_members'][0]['language_code'] ?? null;
$leftChatMember = $updates['message']['left_chat_member'] ?? null;

$queryData = $updates['callback_query']['data'] ?? null;
$queryText = $updates['callback_query']['message']['text'] ?? null;
$queryId = $updates['callback_query']['id'] ?? null;
$queryUserId = $updates['callback_query']['from']['id'] ?? null;
$queryMessageId = $updates['callback_query']['message']['message_id'] ?? null;
$queryMessageDate = $updates['callback_query']['message']['date'] ?? null;
$queryUsername = $updates['callback_query']['from']['username'] ?? null;
$queryFirstName = $updates['callback_query']['from']['first_name'] ?? null;
$queryLanguageCode = $updates['callback_query']['from']['language_code'] ?? null;
$queryInlineMessageId = $updates['callback_query']['inline_message_id'] ?? null;
$queryChatInstance = $updates['callback_query']['chat_instance'] ?? null;

$inlineQuery = $updates['inline_query']['query'] ?? null;
$inlineQueryId = $updates['inline_query']['id'] ?? null;
$inlineQueryUserId = $updates['inline_query']['from']['id'] ?? null;
$inlineQueryFirstName = $updates['inline_query']['from']['first_name'] ?? null;
$inlineQueryUsername = $updates['inline_query']['from']['username'] ?? null;
$inlineQueryLanguageCode = $updates['inline_query']['from']['language_code'] ?? null;

$chosenResult = $updates['chosen_inline_result'] ?? null;
$chosenResultId = $updates['chosen_inline_result']['result_id'] ?? null;
$chosenResultUserId = $updates['chosen_inline_result']['from']['id'] ?? null;

$diceEmoji = $updates['message']['dice']['emoji'] ?? null;
$diceValue = $updates['message']['dice']['value'] ?? null;

$preCheckoutQueryId = $updates['pre_checkout_query']['id'] ?? null;
$successfulPayment = $updates['message']['successful_payment'] ?? null;
$successfulPaymentTotalAmount = $updates['message']['successful_payment']['total_amount'] ?? null;
$telegramPaymentChargeId = $updates['message']['successful_payment']['telegram_payment_charge_id'] ?? null;
