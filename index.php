<?php
declare(strict_types=1);
session_start();

function generate_jwt(array $payload, string $secret): string {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $base64UrlHeader = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
    $base64UrlPayload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
    $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $secret, true);
    $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
}

function verify_jwt(string $jwt, string $secret): bool {
    [$header, $payload, $signature] = explode('.', $jwt);
    $expected_signature = rtrim(strtr(base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true)), '+/', '-_'), '=');
    return hash_equals($expected_signature, $signature);
}

// Demo: identifiant et mdp en dur
const DEMO_USER = 'user';
const DEMO_PASS = 'pass';
const JWT_SECRET = 'supersecretkey';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['identifiant'] ?? '';
    $mdp = $_POST['motdepasse'] ?? '';
    if ($id === DEMO_USER && $mdp === DEMO_PASS) {
        $payload = [
            'user' => $id,
            'exp' => time() + 10
        ];
        $jwt = generate_jwt($payload, JWT_SECRET);
        setcookie('jwt', $jwt, [
            'expires' => time() + 10,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        $message = "Connecté ! Cookie envoyé.";
    } else {
        $message = "Identifiant ou mot de passe incorrect.";
    }
}

$jwt_cookie = $_COOKIE['jwt'] ?? '';
$cookie_status = $jwt_cookie ? 
    (verify_jwt($jwt_cookie, JWT_SECRET) ? 'Cookie JWT valide !' : 'JWT invalide !')
    : 'Cookie JWT absent.';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
</head>
<body>
    <h2>Connexion utilisateur</h2>
    <form method="post">
        <label>Identifiant : <input type="text" name="identifiant" required></label><br>
        <label>Mot de passe : <input type="password" name="motdepasse" required></label><br>
        <button type="submit">Se connecter</button>
    </form>
    <?php if (!empty($message)): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <p><?= $cookie_status ?></p>
</body>
</html>