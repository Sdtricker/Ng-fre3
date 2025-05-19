<?php
// Send HTTP POST to Telegram API
function apiRequest($method, $parameters = []) {
    if (!is_string($method)) {
        error_log("Method name must be a string\n");
        return false;
    }

    if (!$parameters) {
        $parameters = [];
    } else if (!is_array($parameters)) {
        error_log("Parameters must be an array\n");
        return false;
    }

    $url = API_URL . $method;

    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);
    curl_setopt($handle, CURLOPT_POST, true);
    curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($parameters));

    $response = curl_exec($handle);
    if ($response === false) {
        error_log('Curl error: ' . curl_error($handle));
        curl_close($handle);
        return false;
    }

    curl_close($handle);
    $response = json_decode($response, true);
    return $response;
}

// Send message helper
function sendMessage($chat_id, $text, $parse_mode = 'HTML', $reply_markup = null) {
    $params = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => $parse_mode,
        'disable_web_page_preview' => true,
    ];
    if ($reply_markup !== null) {
        $params['reply_markup'] = json_encode($reply_markup);
    }
    return apiRequest('sendMessage', $params);
}

// Edit message text helper
function editMessageText($chat_id, $message_id, $text, $parse_mode = 'HTML', $reply_markup = null) {
    $params = [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text,
        'parse_mode' => $parse_mode,
        'disable_web_page_preview' => true,
    ];
    if ($reply_markup !== null) {
        $params['reply_markup'] = json_encode($reply_markup);
    }
    return apiRequest('editMessageText', $params);
}

// Kick user from group
function kickUser($chat_id, $user_id) {
    return apiRequest('kickChatMember', ['chat_id' => $chat_id, 'user_id' => $user_id]);
}

// Restrict user (mute)
function restrictUser($chat_id, $user_id, $until_date = 0) {
    $permissions = [
        'can_send_messages' => false,
        'can_send_media_messages' => false,
        'can_send_polls' => false,
        'can_send_other_messages' => false,
        'can_add_web_page_previews' => false,
        'can_change_info' => false,
        'can_invite_users' => false,
        'can_pin_messages' => false,
    ];
    return apiRequest('restrictChatMember', [
        'chat_id' => $chat_id,
        'user_id' => $user_id,
        'permissions' => json_encode($permissions),
        'until_date' => $until_date
    ]);
}

// Promote user to admin
function promoteUser($chat_id, $user_id) {
    return apiRequest('promoteChatMember', [
        'chat_id' => $chat_id,
        'user_id' => $user_id,
        'can_change_info' => true,
        'can_post_messages' => true,
        'can_edit_messages' => true,
        'can_delete_messages' => true,
        'can_invite_users' => true,
        'can_restrict_members' => true,
        'can_pin_messages' => true,
        'can_promote_members' => false,
        'can_manage_voice_chats' => true,
    ]);
}

// Save JSON data to file
function saveData($filename, $data) {
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    file_put_contents(DATA_DIR.'/'.$filename, json_encode($data, JSON_PRETTY_PRINT));
}

// Load JSON data from file
function loadData($filename) {
    $path = DATA_DIR.'/'.$filename;
    if (!file_exists($path)) return [];
    $json = file_get_contents($path);
    return json_decode($json, true) ?? [];
}

// Check if user is admin in group (we keep our own admin list per group)
function isAdmin($chat_id, $user_id) {
    $admins = loadData("admins_$chat_id.json");
    return in_array($user_id, $admins);
}

// Add admin
function addAdmin($chat_id, $user_id) {
    $admins = loadData("admins_$chat_id.json");
    if (!in_array($user_id, $admins)) {
        $admins[] = $user_id;
        saveData("admins_$chat_id.json", $admins);
    }
}

// Remove admin
function removeAdmin($chat_id, $user_id) {
    $admins = loadData("admins_$chat_id.json");
    $admins = array_filter($admins, fn($a) => $a != $user_id);
    saveData("admins_$chat_id.json", $admins);
}

// Send log message to LOG_CHANNEL
function logAction($text) {
    sendMessage(LOG_CHANNEL, "<b>LOG:</b> ".$text);
}

// Broadcast message (for admin only)
function broadcastMessage($text, $reply_markup = null) {
    $users = loadData('users.json');
    foreach ($users as $chat_id) {
        sendMessage($chat_id, $text, 'HTML', $reply_markup);
        usleep(500000); // to avoid flood limit
    }
}

// Save new user to users.json if not exists
function saveUser($chat_id) {
    $users = loadData('users.json');
    if (!in_array($chat_id, $users)) {
        $users[] = $chat_id;
        saveData('users.json', $users);
    }
}
