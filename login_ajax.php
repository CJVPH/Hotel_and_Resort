<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

header('Content-Type: application/json');

$resp = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $resp['message'] = 'Please provide username and password.';
        echo json_encode($resp);
        exit;
    }

    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, password, full_name FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->bind_param('ss', $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // set session values
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];

            $resp['success'] = true;
            $resp['message'] = 'Login successful.';
        } else {
            $resp['message'] = 'Invalid username or password.';
        }
    } else {
        $resp['message'] = 'Invalid username or password.';
    }

    $stmt->close();
    $conn->close();
}

echo json_encode($resp);
exit;
?>
