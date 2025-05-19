<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Sending Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .report { text-align: center; margin-top: 20%; }
        .status { font-size: 2em; }
    </style>
    <script>
        async function fetchReport() {
            const response = await fetch('sent_count.txt');
            const sentCount = await response.text();

            const responseTotal = await fetch('data.json');
            const users = await responseTotal.json();
            const totalUsers = users.length;

            document.getElementById('status').innerText = `${sentCount}/${totalUsers}`;

            if (parseInt(sentCount) < totalUsers) {
                setTimeout(fetchReport, 1000); // Refresh every second
            }
        }

        window.onload = fetchReport;
    </script>
</head>
<body>
    <div class="report">
        <div class="status" id="status">0/0</div>
    </div>

    <?php
    // Telegram Bot Script
    $botToken = '8165742842:AAHPFR2jvcXmkhCNYH2MBtlufF_8O0oejUk';
    $channelMessageId = '37';
    $channelUsername = '@NG_B0TS';
    $dataFile = 'data.json';
    $users = json_decode(file_get_contents($dataFile), true);

    $apiUrl = "https://api.telegram.org/bot$botToken";
    $totalUsers = count($users);
    $sentCount = 0;

    foreach ($users as $userId) {
        $params = [
            'chat_id' => $userId,
            'from_chat_id' => $channelUsername,
            'message_id' => $channelMessageId,
        ];

        $url = $apiUrl . '/forwardMessage?' . http_build_query($params);
        file_get_contents($url);

        $sentCount++;
        file_put_contents('sent_count.txt', $sentCount);

        // Update live report
        echo '<script>document.getElementById("status").innerText = "' . $sentCount . '/' . $totalUsers . '";</script>';
        echo str_repeat(' ', 1024*64); // Flush output buffer
        flush(); // Ensure output is sent immediately to the browser
        
    }
    ?>

</body>
</html>