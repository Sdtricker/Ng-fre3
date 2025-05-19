<?php
// Telegram Bot Token
define('BOT_TOKEN', '7919293891:AAEXmRLr4Rpy0oKg0LfQ37pG-FoltmTosiQ');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');

// Temporary folder for files
define('TMP_DIR', __DIR__.'/temp/');

// Make sure temp folder exists
if (!file_exists(TMP_DIR)) mkdir(TMP_DIR, 0777, true);

// Get update from Telegram
$update = json_decode(file_get_contents('php://input'), true);

function sendMessage($chat_id, $text) {
    file_get_contents(API_URL."sendMessage?chat_id=$chat_id&text=".urlencode($text));
}

function sendDocument($chat_id, $file_path) {
    $url = API_URL . "sendDocument?chat_id=$chat_id";
    $post_fields = [
        'document' => new CURLFile(realpath($file_path))
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function downloadFile($file_id) {
    $file_info = json_decode(file_get_contents(API_URL."getFile?file_id=$file_id"), true);
    if ($file_info['ok']) {
        $file_path = $file_info['result']['file_path'];
        $url = "https://api.telegram.org/file/bot".BOT_TOKEN."/".$file_path;
        $local_path = TMP_DIR . basename($file_path);
        file_put_contents($local_path, file_get_contents($url));
        return $local_path;
    }
    return false;
}

function extractZip($zip_path, $extract_to) {
    $zip = new ZipArchive;
    $res = $zip->open($zip_path);
    if ($res === TRUE) {
        $zip->extractTo($extract_to);
        $zip->close();
        return true;
    }
    return false;
}

function getFilesRecursive($dir) {
    $files = [];
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            $files = array_merge($files, getFilesRecursive($path));
        } else {
            $files[] = $path;
        }
    }
    return $files;
}

function readFileContent($file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (in_array($ext, ['txt', 'csv', 'xml'])) {
        return file_get_contents($file);
    } elseif ($ext == 'pdf') {
        // Simple placeholder: just say PDF can't be parsed (you can add pdf parser lib here)
        return "[PDF content not extracted: $file]\n";
    } else {
        return "[Unsupported file type: $file]\n";
    }
}

// Main Logic

if (isset($update['message'])) {
    $chat_id = $update['message']['chat']['id'];
    
    if (isset($update['message']['document'])) {
        // User sent a file
        $file_id = $update['message']['document']['file_id'];
        sendMessage($chat_id, "File received, processing...");

        $local_file = downloadFile($file_id);

        if (!$local_file) {
            sendMessage($chat_id, "Failed to download file.");
            exit;
        }

        $ext = strtolower(pathinfo($local_file, PATHINFO_EXTENSION));

        $output_txt = TMP_DIR . 'output.txt';
        file_put_contents($output_txt, ""); // clear file
        
        if ($ext == 'zip') {
            $extract_folder = TMP_DIR . 'extracted/';
            if (file_exists($extract_folder)) {
                // Clear old extracted files
                system("rm -rf ".escapeshellarg($extract_folder));
            }
            mkdir($extract_folder, 0777, true);
            if (extractZip($local_file, $extract_folder)) {
                $all_files = getFilesRecursive($extract_folder);
                foreach ($all_files as $f) {
                    $content = readFileContent($f);
                    file_put_contents($output_txt, $content . "\n", FILE_APPEND);
                }
                sendDocument($chat_id, $output_txt);
            } else {
                sendMessage($chat_id, "Failed to extract ZIP file.");
            }
        } else {
            // For normal files, just read and send
            $content = readFileContent($local_file);
            file_put_contents($output_txt, $content);
            sendDocument($chat_id, $output_txt);
        }
    } else {
        sendMessage($chat_id, "Please send me a file to process.");
    }
}
?>
