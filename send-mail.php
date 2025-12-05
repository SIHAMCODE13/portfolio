<?php
/**
 * Script d'envoi d'email via Formspree
 * Service cloud gratuit - 100% fiable
 */
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
$subject = isset($_POST["subject"]) ? trim($_POST["subject"]) : "";
$message = isset($_POST["message"]) ? trim($_POST["message"]) : "";

if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email invalide']);
    exit;
}

if (strlen($message) > 5000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le message est trop long']);
    exit;
}

$formspree_id = "xgvgzzdb";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://formspree.io/f/$formspree_id");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'name' => $name,
    'email' => $email,
    'subject' => $subject,
    'message' => $message,
    '_subject' => "Nouveau message: $subject"
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code == 200) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => ' Message envoyé avec succès! Merci de votre contact.'
    ]);
    error_log("[] Email reçu de: $email | Sujet: $subject");
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => ' Erreur lors de l\'envoi. Réessayez dans quelques moments.'
    ]);
    error_log("[] Formspree erreur HTTP $http_code: $error");
}
?>
