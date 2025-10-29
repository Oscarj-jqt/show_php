<?php
declare(strict_types=1);
session_start();

const JWT_SECRET = 'supersecretkey'; // même clé que dans index.php

function verify_jwt(string $jwt, string $secret): array|null {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) return null;
    [$header, $payload, $signature] = $parts;
    $expected_signature = rtrim(strtr(base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true)), '+/', '-_'), '=');
    if (!hash_equals($expected_signature, $signature)) return null;

    $payload_json = base64_decode(strtr($payload, '-_', '+/'));
    $payload_data = json_decode($payload_json, true);
    if (!is_array($payload_data)) return null;

    // Vérifier expiration
    if (isset($payload_data['exp']) && $payload_data['exp'] < time()) return null;

    return $payload_data;
}

// Vérification du cookie JWT
$jwt_cookie = $_COOKIE['jwt'] ?? '';
$user_data = $jwt_cookie ? verify_jwt($jwt_cookie, JWT_SECRET) : null;

// Réponse d’échec si non identifié
if (!$user_data) {
    http_response_code(401);
    echo "<!DOCTYPE html>
    <html lang='fr'><head><meta charset='UTF-8'><title>Accès refusé</title></head>
    <body>
        <h2>Accès refusé !</h2>
        <p style='color:red;'>Vous devez être connecté pour accéder à cette page.</p>
        <a href='index.php'>Retour à la page de connexion</a>
    </body></html>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Page protégée</title>
</head>
<body>
    <h2>Page protégée</h2>
    <p>Accès autorisé ! Bienvenue, <?= htmlspecialchars($user_data['user'] ?? 'inconnu') ?>.</p>
    <a href="index.php">Retour à la page de connexion</a>
</body>
</html>