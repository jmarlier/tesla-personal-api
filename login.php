<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();

$clientId    = $_ENV['TESLA_CLIENT_ID'];
$redirectUri = $_ENV['TESLA_REDIRECT_URI'];
$scope = urlencode('openid offline_access vehicle_device_data vehicle_cmds vehicle_charging_cmds');
$state = bin2hex(random_bytes(16));

// Anti-CSRF
$_SESSION['oauth_state'] = $state;

// PKCE
$codeVerifier = bin2hex(random_bytes(32));

$url = "https://auth.tesla.com/oauth2/v3/authorize?" .
    "client_id={$clientId}&" .
    "redirect_uri={$redirectUri}&" .
    "response_type=code&" .
    "scope={$scope}&" .
    "state={$state}&" .
    "prompt=login";

header("Location: $url");
exit;
?>