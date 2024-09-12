<?php
header('Content-Type: application/json');

// Connexion à la base de données
$host = 'mysql4.ouiheberg.com';
$user = 'u8774_NOhMmG1JEe';
$pass = 'D.@gUMyPi3Jkq+NCVtJULl@E';
$dbname = 's8774_jrk';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['message' => 'Database connection failed']);
    exit;
}

// Fonction pour déchiffrer les données
function decrypt($data) {
    $key = '9Q8D166Yq9RW88c24jAmwb3luf4Mdg78';
    $iv = 'FXkjAXD3R2H5L9cB';

    $data = hex2bin($data);
    return openssl_decrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
}

// Endpoint pour l'initialisation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], '/api/loader/initialize') !== false) {
    try {
        // Compter le nombre d'utilisateurs et de produits
        $stmt = $pdo->query('SELECT COUNT(*) AS totalUsers FROM users');
        $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['totalUsers'];

        $stmt = $pdo->query('SELECT COUNT(*) AS totalProducts FROM products');
        $productCount = $stmt->fetch(PDO::FETCH_ASSOC)['totalProducts'];

        $config = [
            'version' => '1.0',
            'statistics' => [
                'users' => $userCount,
                'products' => $productCount
            ]
        ];

        echo json_encode([
            'message' => 'Initialization successful',
            'config' => $config
        ]);
    } catch (Exception $e) {
        echo json_encode(['message' => 'Error initializing']);
    }
    exit;
}

// Endpoint pour le login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], '/api/loader/login') !== false) {
    try {
        // Lire les données POST
        $discordIDEncrypted = $_POST['discordID'] ?? '';
        $hwidEncrypted = $_POST['hwid'] ?? '';

        $discordID = decrypt(base64_decode($discordIDEncrypted));
        $hwid = decrypt(base64_decode($hwidEncrypted));

        // Rechercher l'utilisateur dans la base de données
        $stmt = $pdo->prepare('SELECT * FROM users WHERE discordID = ? AND hwid = ?');
        $stmt->execute([$discordID, $hwid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Récupérer les produits de l'utilisateur
            $stmt = $pdo->prepare('SELECT name, expiry FROM products WHERE user_id = ?');
            $stmt->execute([$user['id']]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'message' => 'Login successful',
                'user' => [
                    'username' => $user['username'],
                    'DiscordID' => $user['discordID'],
                    'hwid' => $user['hwid'],
                    'products' => $products
                ]
            ]);
        } else {
            echo json_encode(['message' => 'Invalid credentials']);
        }
    } catch (Exception $e) {
        echo json_encode(['message' => 'Error during login']);
    }
    exit;
}

// Si aucune des routes ci-dessus n'est atteinte
echo json_encode(['message' => 'Invalid endpoint']);
