<?php
header('Content-Type: application/json');
require_once 'db_config.php'; // Inclure les informations de connexion à la base de données

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'initialize':
        initialize();
        break;

    case 'login':
        login();
        break;

    default:
        echo json_encode(['message' => 'Invalid action']);
        break;
}

function initialize() {
    global $pdo;

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
}

function login() {
    global $pdo;

    $encryptedDiscordID = $_POST['discordID'] ?? '';
    $encryptedHWID = $_POST['hwid'] ?? '';

    $discordID = decrypt(base64_decode($encryptedDiscordID));
    $hwid = decrypt(base64_decode($encryptedHWID));

    try {
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
}

function decrypt($text) {
    $key = '9Q8D166Yq9RW88c24jAmwb3luf4Mdg78';
    $iv = 'FXkjAXD3R2H5L9cB';
    
    $cipher = "aes-256-cbc";
    $decrypted = openssl_decrypt($text, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    
    return $decrypted;
}
