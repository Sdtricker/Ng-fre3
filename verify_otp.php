<?php
$data = json_decode(file_get_contents("php://input"), true);
$mobile = $data['mobile'];
$otp = $data['otp'];
$name = $data['name'];
$password = $data['password'];
$instagram = $data['instagram'];
$category = $data['category'];

$lines = file("otps.txt");
$valid = false;

foreach ($lines as $line) {
    list($m, $o) = explode(":", trim($line));
    if ($m == $mobile && $o == $otp) {
        $valid = true;
        break;
    }
}

if ($valid) {
    $entry = "$name|$mobile|$password|$instagram|$category\n";
    file_put_contents("brands.txt", $entry, FILE_APPEND);
    echo "Signup successful!";
} else {
    echo "Invalid OTP.";
}
