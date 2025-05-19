<?php
$botToken = '8165742842:AAHPFR2jvcXmkhCNYH2MBtlufF_8O0oejUk';
$chatId = $_POST['chatId'];
$message = $_POST['message'];

$url = "https://api.telegram.org/bot{$botToken}/sendMessage?chat_id={$chatId}&text=" . urlencode($message) . "&parse_mode=HTML";

$response = file_get_contents($url);
echo $response;
?>