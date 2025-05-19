<?php
// === CONFIGURATION ===
define('BOT_TOKEN', '7227277223:AAEvgDTTfT1AqNYQsGqb5ukGsa1thV0Cv_E'); // Telegram Bot Token
define('CHANNEL_FORCE_JOIN', '@NG_B0TS'); // Force Join Channel (hidden)
define('CHANNELS', [
    ['text' => 'Channel 1', 'url' => 'https://t.me/channel1'],
    ['text' => 'Channel 2', 'url' => 'https://t.me/channel2'],
    ['text' => 'Channel 3', 'url' => 'https://t.me/channel3'],
    ['text' => 'Channel 4', 'url' => 'https://t.me/channel4'],
]);
define('ADMIN_CHAT_ID', 7499604230); // Apka Telegram ID for admin commands
define('LOG_CHANNEL_CHAT_ID', '@birooeosowllwwnsnnsnen'); // Jahan user logs jayenge

// === HELPER FUNCTIONS ===

function apiRequest($method, $params = []) {
    $url = "https://api.telegram.org/bot".BOT_TOKEN."/".$method;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    if ($params) {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    }
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true);
}

function sendMessage($chat_id, $text, $reply_markup = null) {
    $params = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true,
    ];
    if ($reply_markup) $params['reply_markup'] = json_encode($reply_markup);
    return apiRequest('sendMessage', $params);
}

function sendDocument($chat_id, $file_path, $caption = '') {
    return apiRequest('sendDocument', [
        'chat_id' => $chat_id,
        'document' => new CURLFile($file_path),
        'caption' => $caption,
        'parse_mode' => 'HTML'
    ]);
}

function editMessageText($chat_id, $message_id, $text, $reply_markup = null) {
    $params = [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true,
    ];
    if ($reply_markup) $params['reply_markup'] = json_encode($reply_markup);
    return apiRequest('editMessageText', $params);
}

// Check if user joined force join channel (silently)
function checkUserJoined($user_id) {
    $res = apiRequest('getChatMember', [
        'chat_id' => CHANNEL_FORCE_JOIN,
        'user_id' => $user_id
    ]);
    if (isset($res['result']['status'])) {
        $status = $res['result']['status'];
        return in_array($status, ['member','creator','administrator']);
    }
    return false;
}

// --- Recursive Remove Directory ---
function rrmdir($dir) {
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = "$dir/$file";
        is_dir($path) ? rrmdir($path) : unlink($path);
    }
    rmdir($dir);
}

// Make absolute url for resources
function makeAbsoluteUrl($relative, $base) {
    if (parse_url($relative, PHP_URL_SCHEME) != '') return $relative;
    if (strpos($relative, '//') === 0) {
        $scheme = parse_url($base, PHP_URL_SCHEME) ?: 'http';
        return $scheme . ':' . $relative;
    }
    $baseParts = parse_url($base);
    $baseScheme = $baseParts['scheme'] ?? 'http';
    $baseHost = $baseParts['host'] ?? '';
    $basePath = $baseParts['path'] ?? '/';
    $basePath = preg_replace('#/[^/]*$#', '/', $basePath);
    if ($relative[0] == '/') {
        $path = $relative;
    } else {
        $path = $basePath . $relative;
    }
    $segments = explode('/', $path);
    $resolved = [];
    foreach ($segments as $segment) {
        if ($segment == '..') array_pop($resolved);
        elseif ($segment != '.' && $segment != '') $resolved[] = $segment;
    }
    $finalPath = '/' . implode('/', $resolved);
    return $baseScheme . '://' . $baseHost . $finalPath;
}

function downloadFile($url, $saveTo) {
    $ctx = stream_context_create(['http' => ['timeout' => 20]]);
    $content = @file_get_contents($url, false, $ctx);
    if ($content) {
        file_put_contents($saveTo, $content);
        return true;
    }
    return false;
}

// Extract all resource links from html (img, video, css, js etc)
function getResourcesFromHtml($html, $base_url) {
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);

    $tags = [
        'img' => 'src',
        'video' => 'src',
        'source' => 'src',
        'audio' => 'src',
        'link' => 'href',
        'script' => 'src',
    ];
    $resources = [];

    foreach ($tags as $tag => $attr) {
        $elements = $dom->getElementsByTagName($tag);
        foreach ($elements as $el) {
            $link = $el->getAttribute($attr);
            if ($link && !preg_match('#^(data:|javascript:)#i', $link)) {
                $abs = makeAbsoluteUrl($link, $base_url);
                $resources[] = $abs;
            }
        }
    }
    return array_unique($resources);
}

// SCRAPE AND ZIP
function scrapeAndZip($url, $chat_id) {
    $tempDir = __DIR__ . '/temp_' . time() . rand(1000,9999);
    mkdir($tempDir);

    $html = @file_get_contents($url);
    if (!$html) {
        sendMessage($chat_id, "❌ Unable to download the page.");
        rrmdir($tempDir);
        return;
    }
    file_put_contents("$tempDir/index.html", $html);

    $resources = getResourcesFromHtml($html, $url);
    $total = count($resources);
    $downloaded = 0;
    $errors = [];

    // Progress message
    $progress_msg = sendMessage($chat_id, "⏳ Download started: 0 / $total");

    foreach ($resources as $resUrl) {
        $parsed = parse_url($resUrl);
        $filename = basename($parsed['path'] ?? '') ?: 'file_' . rand(1000,9999);
        $savePath = "$tempDir/$filename";

        if (downloadFile($resUrl, $savePath)) {
            $downloaded++;
            if ($downloaded % 3 == 0 || $downloaded == $total) {
                // Update progress
                editMessageText($chat_id, $progress_msg['result']['message_id'], "⏳ Downloading: $downloaded / $total");
            }
        } else {
            $errors[] = $resUrl;
        }
    }

    // Zip the folder
    $zipFile = __DIR__ . '/website_' . time() . '.zip';
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
        foreach (scandir($tempDir) as $file) {
            if ($file != '.' && $file != '..') {
                $zip->addFile("$tempDir/$file", $file);
            }
        }
        $zip->close();

        sendDocument($chat_id, $zipFile, "📦 Here is your scraped website archive.");

        unlink($zipFile);
        rrmdir($tempDir);

        if ($errors) {
            sendMessage($chat_id, "⚠️ Some files could not be downloaded:\n" . implode("\n", $errors));
        }
    } else {
        sendMessage($chat_id, "❌ Failed to create zip file.");
        rrmdir($tempDir);
    }
}

// BROADCAST SYSTEM
function broadcastMessage($text) {
    // Load users from a file or database (for demo we assume users.txt contains user chat_ids)
    $usersFile = __DIR__.'/users.txt';
    if (!file_exists($usersFile)) return;
    $users = file($usersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($users as $user_id) {
        sendMessage($user_id, $text);
        usleep(300000); // 0.3 sec delay so Telegram doesn't block
    }
}

// Store user id for broadcast
function addUserForBroadcast($chat_id) {
    $usersFile = __DIR__.'/users.txt';
    $users = [];
    if (file_exists($usersFile)) {
        $users = file($usersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }
    if (!in_array($chat_id, $users)) {
        file_put_contents($usersFile, $chat_id.PHP_EOL, FILE_APPEND);
    }
}

// --- START ---

// Read incoming update
$content = file_get_contents("php://input");
$update = json_decode($content, true);
if (!$update) exit;

$message = $update['message'] ?? null;
$chat_id = $message['chat']['id'] ?? null;
$user_id = $message['from']['id'] ?? null;
$text = trim($message['text'] ?? '');

if (!$chat_id || !$user_id) exit;

// Add user for broadcast
addUserForBroadcast($chat_id);

// Check force join on /start
if ($text == '/start') {
    // Check if user joined force channel
    if (!checkUserJoined($user_id)) {
        $joinBtn = [
            'inline_keyboard' => [[
                ['text' => "Join Channel First", 'url' => "https://t.me/".ltrim(CHANNEL_FORCE_JOIN, '@')]
            ]]
        ];
        sendMessage($chat_id, "🚫 You must join our main channel first to use this bot.", $joinBtn);
        exit;
    }

    // Show welcome with 4 channels + hidden force join inline keyboard
    $keyboard = [];
    foreach (CHANNELS as $ch) {
        $keyboard[] = [['text' => $ch['text'], 'url' => $ch['url']]];
    }
    $keyboard[] = [['text' => 'Force Join Channel', 'url' => 'https://t.me/'.ltrim(CHANNEL_FORCE_JOIN,'@')]];
    $replyMarkup = ['inline_keyboard' => $keyboard];

    $welcomeText = "<b>Welcome to the Ultimate Scraper Bot!</b>\n\n"
        . "Send me any URL, and I'll scrape the whole webpage including images, videos, CSS, and JS files, then send you a zipped archive.\n\n"
        . "Use the channels below to stay updated!";

    sendMessage($chat_id, $welcomeText, $replyMarkup);
    exit;
}

// Admin commands
if ($user_id == ADMIN_CHAT_ID) {
    if (strtolower($text) == '/broadcast') {
        sendMessage($chat_id, "Send me the message to broadcast to all users.");
        // Save admin in file/session for broadcast mode? (for simplicity ignoring state management here)
        file_put_contents(__DIR__.'/broadcast_mode.txt', $chat_id);
        exit;
    }

    if (file_exists(__DIR__.'/broadcast_mode.txt') && file_get_contents(__DIR__.'/broadcast_mode.txt') == $chat_id) {
        // This message is broadcast content
        broadcastMessage($text);
        sendMessage($chat_id, "✅ Broadcast sent.");
        unlink(__DIR__.'/broadcast_mode.txt');
        exit;
    }
}

// If message is a URL, start scraping
if (filter_var($text, FILTER_VALIDATE_URL)) {
    sendMessage($chat_id, "🔍 Starting to scrape: $text");
    scrapeAndZip($text, $chat_id);

    // Log user and URL to log channel
    $logText = "<b>New Scrape Request</b>\nUser: <a href='tg://user?id=$user_id'>$user_id</a>\nURL: $text";
    sendMessage(LOG_CHANNEL_CHAT_ID, $logText);

    exit;
}

// Default fallback message
sendMessage($chat_id, "Send me a valid URL to scrape.");

?>
