<?php
declare(strict_types = 1);

ini_set('display_errors', '1');
error_reporting(E_ALL);
require_once(__DIR__ . '/../init.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../utils/security.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/login.php?redirect=' .  urlencode($_SERVER['REQUEST_URI']) . '&message=' . urlencode('Para criar um anúncio, precisa estar logado.'));
}

if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    error_log('CSRF token mismatch or missing for ad creation. IP: ' . $_SERVER['REMOTE_ADDR']);
    header('Location: ../pages/adCreate.php?error=csrf');
    exit();
}
unset($_SESSION['csrf_token']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDatabaseConnection();
        $title = htmlspecialchars(trim($_POST['titulo']));
        $description = htmlspecialchars(trim($_POST['descricao']));
        $serviceTypeId = intval($_POST['service_id']);
        $price = floatval($_POST['preco']);
        $pricePeriod = htmlspecialchars(trim($_POST['preco-por']));
        $username = $_SESSION['username'];
        $animals = $_POST['animais'] ?? [];

        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../resources/';
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imagePath = './resources/' . $fileName;
            }
        }

        $freelancerId = $_SESSION['user_id'];

        $stmt = $db->prepare('
            INSERT INTO Ads (title, freelancer_id, description, price, price_period, service_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$title, $freelancerId, $description, $price, $pricePeriod, $serviceTypeId]);

        $adId = $db->lastInsertId();

        $stmtService = $db->prepare('INSERT INTO ad_services (ad_id, service_id) VALUES (?, ?)');
        $stmtService->execute([$adId, $serviceTypeId]);

        $stmtAnimal = $db->prepare('INSERT INTO ad_animals (ad_id, animal_id) VALUES (?, ?)');
        foreach ($animals as $animal) {
            $stmtAnimal->execute([$adId, intval($animal)]);
        }

        header("Location: ../pages/adDetails.php?id=$adId&success=1");
        exit;
    } catch (Exception $e) {
        error_log("Application Error: " . $e->getMessage() . " - Stack Trace: " . $e->getTraceAsString());
        echo "An unexpected error occurred. Please try again later or contact support.";
        exit;
    }
}
?>
