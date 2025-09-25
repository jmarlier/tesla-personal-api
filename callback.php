<?php
$client_id = 'TON_CLIENT_ID';
$client_secret = 'TON_CLIENT_SECRET';
$redirect_uri = 'https://tondomaine.com/callback.php';
$audience = 'https://fleet-api.prd.na.vn.cloud.tesla.com';

$code = $_GET['code'] ?? null;

if (!$code) {
    die('Code manquant');
}

$data = http_build_query([
    'grant_type' => 'authorization_code',
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'code' => $code,
    'audience' => $audience,
    'redirect_uri' => $redirect_uri,
]);

$ch = curl_init('https://fleet-auth.prd.vn.cloud.tesla.com/oauth2/v3/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);
curl_close($ch);

if (!$response) {
    die('Erreur lors de la requête token');
}

$tokens = json_decode($response, true);
if (!isset($tokens['access_token'])) {
    die('Erreur de récupération du token: ' . $response);
}

// Sauvegarder dans ta session ou base de données
$_SESSION['access_token'] = $tokens['access_token'];
$_SESSION['refresh_token'] = $tokens['refresh_token'];

echo "Authentification réussie. Tokens obtenus.";
?>