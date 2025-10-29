# Authentification à base de jeton JWT

## Problématique de l’authentification dans des architectures REST

### Contexte : REST et le principe de “stateless”

Les services REST respectent une règle fondamentale :
> Le serveur **ne conserve pas d’état** entre deux requêtes. Chaque requête HTTP doit être autonome et contenir toutes les informations nécessaires à son traitement.

Dans cette logique :
- Le serveur ne doit pas “se souvenir” de l’utilisateur.
- Il traite chaque requête comme indépendante.

Ce principe est idéal pour la **scalabilité**, mais il complique la **gestion de l’authentification** : comment savoir qu’une requête provient d’un utilisateur déjà connecté sans stocker d’état côté serveur ?

### Le défi du “stateless authentication”

Pour rester cohérent avec REST, une solution doit :
- Éviter tout stockage côté serveur.
- Permettre au client de **présenter une preuve d’identité autonome** à chaque requête.
- Être **sûre cryptographiquement** (impossible à falsifier).

Le principe général repose sur un **“jeton d’authentification”** que le serveur émet après vérification initiale et que le client réutilise tant qu’il est valide.

Les avantages sont significatifs :
- Aucun état serveur → facile à maintenir en cas de **changement d'échelle**.
- Compatible avec tout type de client (navigateur, mobile, autre service).
- Adapté aux **API RESTful** et aux architectures **microservices**.

### Problématiques de sécurité communes

Même avec une approche par jeton, plusieurs défis demeurent :

#### a. Stockage côté client
Où et comment stocker le jeton ?
- `localStorage` → exposé aux attaques **XSS**.
- Cookies HTTP → nécessite attention à **CORS**, **CSRF**, et à l’attribut `SameSite`.

#### b. Expiration et renouvellement
- Un jeton à durée de vie courte limite les risques mais complique l’expérience utilisateur.
- Un **Refresh Token** peut être utilisé, mais introduit une nouvelle surface d’attaque.

#### c. Révocation
Contrairement à une session stockée côté serveur, un jeton valide ne peut pas être retiré à distance sans système externe (liste noire, base centralisée de révocation).

### Résumé

| Approche | Type | Stockage côté serveur | Compatible REST | Sécurisée | Facilité de mise à l’échelle |
|-----------|------|-----------------------|-----------------|------------|------------------------------|
| Session classique | Stateful | Oui | Non | Modérée | Faible |
| HTTP Basic / Digest | Stateless | Non | Oui | Faible / moyenne | Bonne |
| Token (JWT) | Stateless | Non | Oui | Élevée (si bien configurée) | Excellente |

## Définition et structure d’un jeton JWT

### Qu’est-ce qu’un JWT ?

Un **JWT (JSON Web Token)** est un **moyen standardisé** (défini par la norme RFC 7519 [1]) utilisé pour représenter et transmettre de manière **sécurisée** des informations entre un client et un serveur sous forme **compacte et auto-suffisante**.

#### Caractéristiques principales :
- Format **stateless** : le serveur n’a pas besoin de stocker d’état ou de session.
- Encodé en **Base64URL** et composé de **trois parties**.
- Généralement transmis via l’en-tête HTTP `Authorization: Bearer <token>`.
- Vérifiable grâce à une **signature cryptographique** (HMAC ou RSA).

Un JWT permet donc au serveur de **faire confiance** aux informations qu’il contient, sans consulter une base de données, tant que la **signature est valide**.

### Structure d’un JWT

Un JWT se compose de **trois segments distincts** séparés par des points :

```
HEADER.PAYLOAD.SIGNATURE
```

Exemple de jeton (tronqué pour la lisibilité) :

```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.
eyJzdWIiOiIxMjM0NTYiLCJyb2xlIjoiYWRtaW4iLCJleHAiOjE3MDA5ODQwMDB9.
TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ
```

Chaque partie est encodée séparément en **Base64URL**.

### 1re partie : l’en-tête (Header)

L’en-tête indique au serveur **comment interpréter et vérifier** le jeton.
C’est un petit objet JSON contenant deux informations principales :

```json
{
  "alg": "HS256",
  "typ": "JWT"
}
```

- `"alg"` : algorithme utilisé pour la signature du jeton
  - On retrouve fréquemment : `HS256` (HMAC SHA-256) ou `RS256` (RSA SHA-256).
- `"typ"` : type de jeton, presque toujours `"JWT"`.

Encodé en Base64URL, cet en-tête devient le premier segment du jeton.

### 2e partie : la charge utile (payload)

La **charge utile** contient les données (appelées **claims**) que le jeton transporte.
Ces _claims_ représentent des assertions sur l’utilisateur ou sur le contexte d’utilisation.

Exemple :
```json
{
  "sub": "42",
  "name": "Alice Dupont",
  "role": "admin",
  "iat": 1729852000,
  "exp": 1729855600
}
```

#### Types de claims :
- **Registered claims (standardisés)** :
  - `iss` : *issuer*, émetteur du token.
  - `sub` : *subject*, identifiant du sujet (ex : ID utilisateur).
  - `aud` : *audience*, destinataire du token.
  - `exp` : *expiration time*, timestamp d’expiration.
  - `iat` : *issued at*, date d’émission.
- **Public claims** : informations personnalisées définies par l’application (`role`, `permissions`, `email`).
- **Private claims** : données spécifiques à un échange particulier entre deux systèmes.

> **Attention**
>
> Le **contenu de la charge utile n’est pas chiffré** : il est simplement encodé.
> Toute personne accédant au jeton peut lire son contenu une fois décodé.
> → Ne jamais y placer de mots de passe ni d’informations sensibles.

### 3e partie : la signature

C’est la partie qui **garantit l’intégrité et l’authenticité** du jeton.
Elle permet au serveur de vérifier que :
1. Le jeton n’a pas été modifié.
2. Il a bien été émis par une entité de confiance.

Le mécanisme de signature dépend de l’algorithme spécifié dans le header.

#### Signature avec un secret partagé (HS256)

Pour l’algorithme **HMAC-SHA256**, la signature est calculée ainsi :

$$
Signature = HMAC\_SHA256(base64url(header) + "." + base64url(payload), secret)
$$

Le serveur, connaissant le même `secret`, peut régénérer la signature et la comparer à celle reçue.

#### Signature avec clé publique/privée (RS256)

Pour **RS256**, le principe est différent :
- Le serveur **émetteur** signe le jeton avec sa **clé privée**.
- Tout autre service peut **vérifier** l’authenticité avec la **clé publique**.

> Cette approche est très utilisée dans les systèmes d’authentification distribués (par ex. OAuth2, OpenID Connect).

### Cycle de vie d’un JWT

1. **Connexion** : le client s’authentifie avec ses identifiants (via `/login`).
2. **Génération** : le serveur crée le JWT et le retourne dans la réponse.
3. **Stockage** : le client garde le jeton (dans un cookie sécurisé ou le *localStorage*).
4. **Utilisation** : le client envoie le jeton dans chaque requête protégée :
   ```
   Authorization: Bearer <jwt>
   ```
5. **Vérification** : le serveur valide la signature et vérifie que le jeton n’a pas expiré.
6. **Expiration** : après la date `exp`, le jeton devient invalide.

### Exemple complet

En-tête :
```json
{ "alg": "HS256", "typ": "JWT" }
```

Payload :
```json
{ "sub": "42", "name": "Alice", "admin": true, "iat": 1729852000 }
```

Clé secrète :
```
"MaCleSuperSecrete123"
```

Signature :
```
HMACSHA256(
  base64UrlEncode(header) + "." + base64UrlEncode(payload),
  MaCleSuperSecrete123
)
```

Jeton final (concaténé et encodé) :
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.
eyJzdWIiOiI0MiIsIm5hbWUiOiJBbGljZSIsImFkbWluIjp0cnVlLCJpYXQiOjE3Mjk4NTIwMDB9.
fV_UkKF7B7g0ZruQ3sFYyFVVOs4iHmnK52CXZVldW4s
```

### Avantages du JWT

- Compatible avec les **architectures REST et microservices**.
- Ne nécessite pas de **stockage côté serveur**.
- Facile à transmettre dans les en-têtes HTTP.
- Vérifiable localement, sans accès à une base de données.
- Format universel **lisible et interopérable** (basé sur JSON).

### Limites et précautions

- La **taille du jeton** peut devenir importante dans certains cas.
- Aucune possibilité native de **révocation immédiate**.
- Le contenu n’est **pas chiffré**, seulement signé.
- Une **clé compromise** invalide la sécurité de tous les jetons émis.
- Nécessite une politique rigoureuse de **rotation de clés** et de gestion de l’expiration (`exp`).

## Exemple d'utilisation d’un jeton JWT en PHP

### Préparation de l’environnement

Avant tout, installer la bibliothèque **firebase/php-jwt**, une référence stable et conforme à la norme RFC 7519.

```bash
composer require firebase/php-jwt
```

Cette dépendance fournit les classes `JWT` et `Key` permettant de créer, signer et vérifier des jetons de manière simple et sécurisée.[2]

### Génération d’un JWT en PHP

```php
<?php
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Clé secrète utilisée pour signer le jeton
$secretKey = "MaCleSecrete123";

// Données à inclure dans le payload
$issuedAt = time();
$expirationTime = $issuedAt + 3600; // valide pendant 1h
$payload = [
    'sub' => '42',
    'name' => 'Fidji Dupont',
    'role' => 'admin',
    'iat' => $issuedAt,
    'exp' => $expirationTime
];

// Encodage du jeton
$jwt = JWT::encode($payload, $secretKey, 'HS256');

echo "Votre jeton : " . $jwt;
```

Chaque champ du tableau `$payload` correspond à un **claim** :
- `sub` : identifiant du sujet (ici l’utilisateur).
- `iat` : date d’émission.
- `exp` : date d’expiration.

Le résultat est une chaîne **Header.Payload.Signature** encodée en **Base64URL**.[1]

### Vérification et décodage du jeton

Pour vérifier un JWT reçu d’un client (par exemple dans une requête API REST), on procède ainsi :

```php
<?php
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "MaCleSecrete123";
$jwt = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

try {
    // Décodage et validation du jeton
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

    echo "Utilisateur authentifié : " . $decoded->name;
    echo " (Rôle : " . $decoded->role . ")";
} catch (Exception $e) {
    http_response_code(401);
    echo "Erreur : " . $e->getMessage();
}
```

Cette fonction :
- **décode** le jeton,
- **vérifie la signature**,
- **valide les dates** (`iat`, `exp`).

Si le jeton est invalide ou expiré, une exception est générée.


## Implémentation manuelle (sans bibliothèque externe)

Pour bien comprendre la logique interne, il est intéressant de coder la génération d’un JWT “à la main”  :

```php
<?php
/**
 * Encode une chaîne de caractères au format Base64URL
 *
 * @param string $data Une chaîne de caractères
 *
 * @return string La chaîne de caractères encodée
 */
function base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Construit le jeton JWT en utilisant un chiffrment symétrique
 *
 * @param array $payload La charge utile à encapsuler dans le jeton
 * @param string $secret La clef de chiffrement du jeton
 *
 * @return string Le jeton JWT complet
 */
function create_jwt($payload, $secret) {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $header_enc = base64url_encode(json_encode($header));
    $payload_enc = base64url_encode(json_encode($payload));

    $signature = hash_hmac('sha256', "$header_enc.$payload_enc", $secret, true);
    $signature_enc = base64url_encode($signature);

    return "$header_enc.$payload_enc.$signature_enc";
}
```

Voici maintenant comment utiliser le jeton :
```php
<?php
$payload = ['user' => 'alice@example.com', 'role' => 'admin', 'iat' => time()];
$token = create_jwt($payload, 'CLE_SUPER_SECRETE');
echo $token;
```

### Intégration dans une API REST (exemple simplifié)

#### Route `/login.php`
L’utilisateur s’authentifie, puis reçoit un jeton signé :

```php
<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if ($email === 'alice@example.com' && $password === 'secret123') {
    $payload = ['sub' => $email, 'exp' => time() + 3600];
    $token = JWT::encode($payload, 'CLE_SUPER_SECRETE', 'HS256');
    echo json_encode(['token' => $token]);
} else {
    http_response_code(401);
    echo json_encode(['message' => 'Identifiants invalides']);
}
```

#### Route `/protected.php`
Le client doit présenter un **JWT valide** :

```php
<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    exit(json_encode(['message' => 'Jeton manquant']));
}

$token = str_replace('Bearer ', '', $headers['Authorization']);
try {
    $decoded = JWT::decode($token, new Key('CLE_SUPER_SECRETE', 'HS256'));
    echo json_encode(['message' => 'Bienvenue ' . $decoded->sub]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['message' => 'Token invalide ou expiré']);
}
```


- Protéger la clé (`CLE_SUPER_SECRETE`) : la stocker dans un fichier d’environnement.
- Toujours utiliser **HTTPS** pour limiter les interceptions.
- Prévoir un système d’expiration (`exp`) et de rafraîchissement des tokens.
- Ne pas inclure de données sensibles dans le payload (non chiffré).

## Flux d’authentification

### Vue d’ensemble du flux JWT

L’authentification avec un **JSON Web Token** suit une séquence claire et stateless :

1. **Connexion** : l’utilisateur envoie ses identifiants via `POST /login`.
2. **Vérification** : le serveur authentifie l’utilisateur (en base de données).
3. **Création du jeton** : le serveur génère et signe un JWT.
4. **Transmission** : le jeton est renvoyé au client.
5. **Utilisation** : le client inclut le jeton dans chaque requête protégée (`Authorization: Bearer <token>`).
6. **Vérification côté serveur** : le serveur décode et valide le jeton sans état de session.

### Exemple de base de données simulée

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Et ajoutons un utilisateur d’exemple :
```sql
INSERT INTO users (email, password) VALUES ('alice@example.com', 'secret123');
```

*(Dans un cas réel, les mots de passe seraient hachés avec `password_hash()`.)*

### Étape 1 : Authentification et génération du JWT

La première étape du flux de communication commence par la « _connexion_ » de l'utilisateur.
Celui-ci doit d'identifier auprès du service qu'il consulte.
Cela se fait, comme d'habitude par l'envoi d'un identifiant et d'un mot de passe.

```php
<?php
/* login.php */

require 'vendor/autoload.php';
use Firebase\JWT\JWT;

$secretKey = "CLE_SUPER_SECRETE";
$pdo = new PDO("mysql:host=localhost;dbname=jwt_demo;charset=utf8", "root", "");

// 1. Récupération du JSON envoyé
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

// 2. Vérification des identifiants
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['password'] !== $password) {
    http_response_code(401);
    echo json_encode(["message" => "Identifiants invalides"]);
    exit;
}

// Construction du payload JWT
$issuedAt = time();
$expire = $issuedAt + 3600; // validité 1h
$payload = [
    "iss" => "http://localhost",
    "aud" => "http://localhost",
    "iat" => $issuedAt,
    "exp" => $expire,
    "data" => [
        "user_id" => $user["id"],
        "email" => $user["email"]
    ]
];

// 3. Encodage du token
$jwt = JWT::encode($payload, $secretKey, 'HS256');

// 4. Réponse au client
echo json_encode([
    "message" => "Connexion réussie",
    "token" => $jwt
]);
```

*Résultat* → Le client reçoit un JWT qu’il devra joindre aux futures requêtes.

### Étape 2 : Accès à une ressource protégée

Imaginons maintnenant que l'utilisateur souhaite accéder à une ressource protégée.
Il doit envoyer le jeton pour demander au serveur l'autorisation de faire cela.
En général, un service attend que le jeton soit transmis dans l'entête HTTP "Authorization" , évoqué plus haut, sous le format :
```
Authorization: Bearer <jwt>
```
Exemple :
```php
<?php
/* protected.php */

require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "CLE_SUPER_SECRETE";

// 5. Lecture de l’en-tête Authorization
$headers = apache_request_headers();

if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["message" => "Token manquant"]);
    exit;
}

$token = str_replace("Bearer ", "", $headers['Authorization']);

// 6. Vérification et décodage du JWT
try {
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
    echo json_encode([
        "message" => "Accès autorisé",
        "user" => $decoded->data->email,
        "expire" => date("Y-m-d H:i:s", $decoded->exp)
    ]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["message" => "Token invalide ou expiré"]);
}
```

Le serveur vérifie maintenant la signature et l’expiration du jeton, sans interroger la base de données.

### Utilisation via un client en ligne de commande

Les clients ne se limitent naturellement pas aux navigateurs web.
Voici un exemple d'interaction depuis la ligne de commande.
Un programme quelconque pourrait utiliser la même technique, par exemple une application en Python avec la bibliothèque `requests`ou `httpx`.

**Pour se connecter :**
```bash
curl -X POST http://localhost/login.php \
-H "Content-Type: application/json" \
-d '{"email": "alice@example.com", "password": "secret123"}'
```

**Réponse obtenue :**
```json
{
  "message": "Connexion réussie",
  "token": "eyJhbGciOiJIUzI1NiIsInR5c..."
}
```

**Pour accéder à la ressource :**
```bash
curl -X GET http://localhost/protected.php \
-H "Authorization: Bearer eyJhbGciOiJIUz..."
```

**Réponse :**
```json
{
  "message": "Accès autorisé",
  "user": "alice@example.com",
  "expire": "2025-10-25 11:45:00"
}
```

### Schéma du flux JWT

```
   +----------+                     +----------+
   |  Client  |                     |  Serveur |
   +----------+                     +----------+
          |   1. POST /login (identifiants)         |
          |---------------------------------------->|
          |   2. Vérifie en base + génère JWT       |
          |<----------------------------------------|
          |   3. Stocke “token” côté client         |
          |                                          |
          |   4. GET /protected (avec Bearer Token) |
          |---------------------------------------->|
          |   5. Vérifie la signature JWT           |
          |<----------------------------------------|
          |   6. Envoie ressource sécurisée          |
```

### Points clés à retenir

- Le JWT **évite la dépendance serveur** aux sessions.
- Chaque requête est **autonome** (l’identification n’est pas conservée côté serveur).
- Le serveur se contente de vérifier **cryptographiquement** la signature.
- Le client contrôle la durée de validité via le **claim `exp`**.

### Erreurs courantes à éviter

1. **Jeton sans expiration (`exp`)** → risque de réutilisation indéfinie.
2. **Jeton stocké en clair dans `localStorage`** → vulnérable aux attaques XSS.
3. **Clé secrète exposée dans le code source** → compromet la sécurité de toute l’application.
4. **Mauvaise configuration CORS** → empêche ou fragilise l’accès API côté navigateur.

## Sécurisation de l’application

### Pourquoi renforcer la sécurité ?

L’émission d’un JWT n’est pas suffisante pour garantir une sécurité complète.
Les risques majeurs incluent :
- **Vol de jeton** (via XSS, interception réseau, ou vol de cookie).
- **Réutilisation indéfinie** d’un jeton sans expiration ni révocation.
- **Partage de clés** mal maîtrisé.
- **Fuites de refresh tokens** non révoqués.

Le but de cette section est donc d’assurer :
- **Confidentialité** (transaction chiffrée via HTTPS),
- **Intégrité** (signature JWT vérifiée),
- **Gestion du cycle de vie du token** (expiration + refresh).

### Gestion du jeton dans un navigateur web

Dans le cas des applications web, les jetons JWT peuvent être transmis au navigateur de plusieurs manières, mais chaque méthode a des implications de sécurité importantes.
Les deux pratiques principales sont le stockage dans le localStorage/sessionStorage ou dans les cookies.

#### Stockage dans localStorage/sessionStorage

Il est facile de stocker et récupérer un JWT en JavaScript avec ces API :
```js
// Stocker
localStorage.setItem('jwt', token);

// Récupérer
const token = localStorage.getItem('jwt');
```
Cependant, cette méthode est vulnérable aux attaques XSS : tout script malveillant ayant accès au JavaScript du site pourra lire ce jeton et l’utiliser.

#### Stockage dans un cookie HttpOnly

La méthode la plus sécurisée est de demander au backend d’envoyer le JWT dans un cookie HttpOnly :
```php
setcookie("jwt", $jwt, [
    "httponly" => true,
    "secure" => true,
    "samesite" => "Strict"
]);
```
Ce cookie est automatiquement envoyé avec chaque requête HTTP, mais il n’est pas accessible à JavaScript (protection contre XSS). Il reste toutefois vulnérable aux attaques CSRF, qu’il faut contrer avec des jetons CSRF complémentaires.

### La gestion du cycle de vie d’un token JWT

Un bon système repose sur deux types de jetons :

| Type | Durée de vie | Stockage | Usage |
|------|---------------|----------|-------|
| **Access token** | court (5–60 min) | Mémoire / cookie | Accès aux ressources |
| **Refresh token** | long (jours/semaines) | Base de données | Renouveler les accès |

### Création d’un Refresh Token

Un **refresh token** est un jeton longue durée stocké côté serveur (ou dans une base).
Lorsqu’un **access token** expire, le client envoie le **refresh token** pour obtenir un nouveau JWT sans se reconnecter.

**Table SQL exemple** :
```sql
CREATE TABLE refresh_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Exemple PHP : génération d’un Refresh Token

```php
<?php
/* login.php */
require 'vendor/autoload.php';
use Firebase\JWT\JWT;

$pdo = new PDO("mysql:host=localhost;dbname=jwt_demo;charset=utf8", "root", "");
$secret = "CLE_SUPER_SECRETE";

// Identité validée (après comparaisons email/mot de passe)
$user_id = 1;
$access_exp = time() + 900;    // 15 minutes
$refresh_exp = time() + 2592000; // 30 jours

// Génération du jeton d'accès
$access_payload = [
    "sub" => $user_id,
    "iat" => time(),
    "exp" => $access_exp
];
$access_token = JWT::encode($access_payload, $secret, 'HS256');

// Génération et stockage du refresh token
$refresh_token = bin2hex(random_bytes(32)); // aléatoire et non réutilisable
$hash = hash('sha256', $refresh_token);

$stmt = $pdo->prepare("INSERT INTO refresh_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
$stmt->execute([$user_id, $hash, date('Y-m-d H:i:s', $refresh_exp)]);

echo json_encode([
    "access_token" => $access_token,
    "refresh_token" => $refresh_token
]);
```

Ici, le *refresh token* est stocké **haché** pour éviter toute exploitation directe s’il est compromis.

### Endpoint de rafraîchissement du jeton

```php
<?php
/* refresh.php */
require 'vendor/autoload.php';
use Firebase\JWT\JWT;

$pdo = new PDO("mysql:host=localhost;dbname=jwt_demo;charset=utf8", "root", "");
$secret = "CLE_SUPER_SECRETE";

$data = json_decode(file_get_contents("php://input"), true);
$refresh_token = $data["refresh_token"] ?? "";
$hash = hash("sha256", $refresh_token);

$stmt = $pdo->prepare("SELECT user_id, expires_at FROM refresh_tokens WHERE token_hash = ?");
$stmt->execute([$hash]);
$tokenRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tokenRow || strtotime($tokenRow["expires_at"]) < time()) {
    http_response_code(401);
    echo json_encode(["message" => "Refresh token invalide ou expiré"]);
    exit;
}

// Génération d’un nouveau JWT
$new_payload = [
    "sub" => $tokenRow["user_id"],
    "iat" => time(),
    "exp" => time() + 900 // 15 min
];
$new_access_token = JWT::encode($new_payload, $secret, "HS256");

echo json_encode(["access_token" => $new_access_token]);
```

Cette logique suit la pratique recommandée :
- les refresh tokens sont **stockés côté serveur**,
- chaque requête `/refresh` émet un **nouvel access token**,
- possibilité d’invalider le refresh token après usage.

### Révocation et nettoyage automatique

Pour limiter le risque, un *refresh token* doit être **supprimé après usage** :

```php
$stmt = $pdo->prepare("DELETE FROM refresh_tokens WHERE token_hash = ?");
$stmt->execute([$hash]);
```

On peut également automatiser le nettoyage quotidien des tokens expirés :
```sql
DELETE FROM refresh_tokens WHERE expires_at < NOW();
```

### Sécurisation des requêtes client

Pour protéger les échanges :
- Utiliser **HTTPS partout**.
- Interdire le stockage des jetons sensibles dans le **localStorage**.
- Préférer les **cookies sécurisés** :
  - `Secure` → transmis uniquement en HTTPS
  - `HttpOnly` → inaccessible au JavaScript
  - `SameSite=Strict` → empêche les requêtes inter-origines involontaires (CSRF).

Exemple d’envoi côté serveur :
```php
setcookie("refresh_token", $refresh_token, [
    "expires" => $refresh_exp,
    "httponly" => true,
    "secure" => true,
    "samesite" => "Strict"
]);
```

### Rotation des clés et surveillance

- Renouveler régulièrement la **clé secrète JWT**.
- Mettre en place une **liste de révocation** (blacklist) temporaire pour les tokens compromis.
- Journaliser les connexions (adresse IP, agent navigateur).
- Détecter les usages simultanés d’un même token pour bloquer un compte exposé.

### Bonnes pratiques de sécurité à retenir

| Risque | Mesure préventive |
|--------|-------------------|
| Jeton intercepté | Utiliser HTTPS et cookies `HttpOnly` |
| Jeton volé/distribué | Durée de vie courte + révocation |
| Fuite de clé serveur | Stockage dans `.env` + rotation planifiée |
| Attaque XSS | Interdire token dans JavaScript |
| Jeton non expirant | Toujours inclure `exp` |
| Attaque CSRF | Attribut `SameSite=Strict` sur les cookies d’authentification |

### Sécurisation globale du flux JWT

```
+----------------+          +----------------+
|  Client (SPA)  |          |   Serveur PHP  |
+----------------+          +----------------+
   |--- POST /login -----> crée access + refresh tokens
   |<-- reçoit tokens -------------------------------|
   |--- GET /api/data ----(Authorization: Bearer)--> |
   |--- POST /refresh ----(refresh_token)----------->|
   |<-- reçoit nouveau access token -----------------|
   |--- Déconnexion -----> refresh_token supprimé ---|
```

Voici le **support de cours complet pour le point 8 : Limites des jetons JWT**, rédigé pour un niveau **licence informatique**. Cette section s’appuie sur les analyses techniques et recommandations issues de sources spécialisées en sécurité web.

## Limites des jetons JWT

### Nature sans état : un atout… mais une faiblesse

Les JWT sont par essence **sans état** : leur validité est autonome, sans interroger le serveur à chaque requête.
Cependant, cette qualité engendre aussi un **problème de révocation** :

- Une fois un jeton émis, **il reste valide jusqu’à expiration**, même si l’utilisateur se déconnecte ou qu’un administrateur révoque son accès.
- Pour invalider un JWT avant son `exp`, il faut mettre en place un mécanisme complémentaire :
  - une **liste noire (blacklist)** de jetons révoqués,
  - ou réduire fortement la **durée de vie (`exp`)** du jeton.

**Impact :** cela complique la gestion d’un véritable « logout » sécurisé.

### Absence de chiffrement des données

Un JWT **n’est pas chiffré**, il est simplement **encodé** en Base64URL.
Cela signifie que toute personne ayant accès au token peut en lire le contenu :

```json
{
  "sub": "42",
  "role": "admin",
  "exp": 1735288800
}
```

Ainsi :
- Il **ne faut jamais inclure d’informations sensibles** (mots de passe, emails, clés API).
- Si des données confidentielles doivent transiter dans le jeton, il est nécessaire d’utiliser un **JWE (JSON Web Encryption)**, bien plus complexe à implémenter.

### Taille importante et surcharge réseau

Contrairement à un cookie de session, un JWT embarque toutes les informations de contexte : ID, rôle, dates, signature…
- Cela rend le jeton **plus volumineux** (souvent 500 à 1500 octets).
- À chaque requête, ce jeton est renvoyé dans l’en-tête HTTP : cela augmente la **bande passante consommée** dans des applications à fort trafic.

> **Exemple**
>
> Un site SPA effectuant 50 requêtes/minute, avec un token de 1 Ko, transfère 50 Ko/minute rien que pour l’authentification.

### Difficulté de rotation des clés de signature

Les algorithmes JWT (HS256, RS256, etc.) nécessitent une **clé de signature** (secrète ou privée).
Si cette clé est compromise :
- tous les jetons générés avec celle-ci deviennent falsifiables ;
- il faut **invalider manuellement** tous les tokens encore actifs.

Or, le processus de **rotation de clé** (renouvellement périodique) est rarement implémenté :
- complexité dans les systèmes distribués ;
- nécessité de synchroniser les nouvelles clés publiques/privées entre services.

**Bonne pratique :** utiliser un **système de clés versionnées (kid)**, permettant de vérifier quel couple de clés a signé chaque jeton.

### Vulnérabilités connues et erreurs d’implémentation

#### a. Mauvaise gestion de l’algorithme

Une vulnérabilité historique de JWT (2015) :
- Des serveurs acceptaient des jetons marqués `alg: none`, donc **non signés** !
- Certains parseurs ne vérifiaient pas la cohérence entre le header et la configuration serveur.

> **Correctif :**
>
> Toujours **forcer côté serveur** l’algorithme utilisé (`HS256`, `RS256`) sans se fier au header venant du client.

#### b. Attaques par falsification de signature

- Si la clé secrète (`HS256`) est faible (ex : “123456”), elle peut être **devinée** par force brute.
- Pour les signatures asymétriques (`RS256`), certains attaquants tentent une **substitution de clé publique dans le header** (`jku`, `kid`) pour rediriger vers une clé contrôlée par eux.

#### c. Réutilisation ou vol de jeton

Les JWT interceptés peuvent être **réutilisés sur un autre appareil** jusqu’à leur expiration : c’est ce qu’on appelle une attaque de **rejeu**.

> **Contre-mesures :**
>
> - Activer le HTTPS systématiquement.
> - Associer le jeton à l’IP ou l’empreinte du navigateur.
> - Inclure un identifiant unique (`jti`) et vérifier qu’il n’a pas déjà été utilisé.

### Compatibilité limitée avec certains flux d’authentification

- Inadapté aux environnements nécessitant **révocation instantanée** (banque, back-office).
- Moins flexible pour les applications qui changent fréquemment de statut utilisateur (rôles, permissions).
- Peu pratique dans les systèmes où le **serveur doit centraliser l’état** (ex. : applications multi-périphériques synchronisées).

Dans ces cas, une **authentification basée sur des sessions** ou une solution **OAuth2/OpenID Connect** est préférable.

### Bonnes pratiques pour limiter les risques

D’après les recommandations Auth0, Microsoft et Vaadata  :

| Risque | Bonne pratique |
|--------|----------------|
| Jeton intercepté en transit | Utiliser **HTTPS** sans exception |
| Jeton volé en local | Stocker le token dans un **cookie `HttpOnly` + `Secure`** |
| Jeton non expirant | Toujours inclure un `exp` court (ex. : 15 min) |
| Révoquer avant `exp` | Utiliser une **liste noire** ou un **Refresh Token** |
| Algorithme `none` ou incohérent | Forcer la validation du `alg` côté serveur |
| Données sensibles visibles | Ne placer dans le `payload` que les claims essentiels |
| Compromission de clé | Mettre en place la **rotation périodique** et la gestion par identifiant de clé (`kid`) |
| Attaques CSRF | Restreindre les cookies par **SameSite=Strict** |

### Quand éviter complètement JWT ?

Selon  et , il est préférable **de ne pas utiliser JWT** si :
- Vous avez besoin d’une **révocation immédiate** des jetons (back-office, sécurité critique).
- Vos serveurs peuvent stocker un **état de session simple**.
- Vous traitez des **informations confidentielles** qui ne doivent jamais transiter (données médicales, financières).

Dans ces contextes, une approche basée sur **sessions traditionnelles** ou **OAuth2 avec tokens courts** est plus appropriée.

## Conclusion et ouverture

### Synthèse : apports du JWT

Les **JSON Web Tokens (JWT)** ont profondément transformé la gestion de l’identification et de l’autorisation dans les architectures web modernes.

Les principaux **avantages** sont :

- **Compatibilité universelle** : la norme RFC 7519 est supportée dans tous les langages et frameworks.
- **Approche stateless** : aucun stockage de session serveur, ce qui facilite la **scalabilité** et le **déploiement distribué**.
- **Interopérabilité** : un même jeton peut circuler entre plusieurs microservices, applications mobiles ou API sans dépendance centrale.
- **Robustesse cryptographique** : les signatures HMAC (HS256) ou RSA (RS256/RS512) garantissent l’intégrité du jeton.

JWT s’impose donc comme une **solution d’authentification performante**, particulièrement adaptée aux **API REST**, **SPA**, et **microservices**.

### 9.3 Bonnes pratiques essentielles pour le déploiement

D’après Auth0, Curity et Vaadata  :

1. **Protéger la clé de signature**
   - Stocker la clé dans un dépôt sécurisé (vault, `.env` hors web).
   - Ne la partager qu’avec les services qui doivent signer ou vérifier des JWT.

2. **Définir une durée de vie courte (`exp`)**
   - Access token : 5 à 15 minutes.
   - Refresh token : 1 jour à 30 jours maximum.

3. **Utiliser HTTPS partout**
   - Interdit l’interception des tokens (attaque “man-in-the-middle”).

4. **Valider systématiquement les claims**
   - Vérifier `iss` (émetteur), `aud` (audience), `iat`, `exp`, `sub`.
   - Refuser tout token dont les claims sortent du cadre attendu.

5. **Empêcher le stockage risqué coté client**
   - Éviter `localStorage`.
   - Privilégier un cookie `HttpOnly`, `Secure`, `SameSite=Strict`.

6. **Mettre en place la rotation et le rafraîchissement des tokens**
   - Renouveler périodiquement les jetons d’accès via un **refresh token**.
   - Supprimer les jetons suspects (blacklist).

7. **Se conformer à la spécification JWT et JWA**
   - Ne pas modifier les formats standards ou inventer de nouveaux algorithmes.
   - Éviter les implémentations “maison” : utiliser des bibliothèques **auditées et maintenues**.

8. **Tester les vulnérabilités connues**
   - Se référer à la section *Security Misconfiguration* de l’OWASP Top 10.
   - Vérifier la résistance aux attaques : *token sidejacking*, *brute forcing*, *header injection*, *replay attacks*.

### 9.4 JWT dans les protocoles modernes

Les JWT ne sont pas isolés : ils sont devenus **la brique de base des protocoles d’identité modernes**.

#### a. OAuth 2.0
Utilisé pour **l’autorisation déléguée**, OAuth 2 émet souvent des **access tokens** au format JWT.
Exemple : les APIs Google ou GitHub.
- Le serveur d’autorisation signe le jeton.
- Les microservices du client le valident sans dépendance externe.

#### b. OpenID Connect (OIDC)
C’est une **extension d’OAuth 2** dédiée à **l’authentification**.
Le serveur fournit un **ID Token** (au format JWT) contenant les informations d’identité (`sub`, `email`, `name`).
OpenID Connect ajoute donc une **couche d’identification utilisateur** au mécanisme JWT.

#### c. SSO (Single Sign-On)
Les systèmes SSO d’entreprise et les plateformes cloud utilisent très largement les JWT pour porter l’identité de l’utilisateur entre applications :
- Exemples : Azure AD, Google Identity, Keycloak.
- Le jeton sert de **passeport d’accès sécurisé** transférable entre domaines et services.

### Ouverture : sécurité et évolutions

Le JWT est une technologie **mature mais mouvante**.
Ses mécanismes d’intégrité et de distribution continuent d’évoluer vers :
- le **JWE (JSON Web Encryption)** : version chiffrée du JWT pour les données sensibles ;
- le **PASETO (Platform-Agnostic Security Token)** : alternative moderne plus stricte, supprimant certaines failles historiques ;
- des systèmes basés sur **Zero Trust** et **Proof-of-Possession Tokens**, limitant l’usage d’un token volé à son contexte d’origine (IP, device).

Les futures architectures d’authentification tendront à combiner :
- **sécurité renforcée** (par signature et chiffrement adaptatifs),
- **expérience utilisateur fluide**,
- et **interopérabilité multi-service** pour le cloud et les API distribuées.

## Ressources

1. [RFC 7519](https://datatracker.ietf.org/doc/html/rfc7519)
2. [Introduction aux jeton JWT | jwt.io](https://www.jwt.io/introduction#what-is-json-web-token)
