<?php
header('Content-Type: application/json');
$host = 'localhost'; $db = 'accounting_db'; $user = 'root'; $pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8", $user, $pass);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $db");
    $pdo->exec("USE $db");
} catch (PDOException $e) { die(json_encode(['error' => $e->getMessage()])); }

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

// --- إنشاء حساب جديد ---
if ($action == 'manual_signup') {
    $email = trim($input['email']);
    $hashed_pass = password_hash($input['pass'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (email, full_name, password, is_approved) VALUES (?, ?, ?, 0)");
    try {
        $stmt->execute([$email, $input['name'], $hashed_pass]);
        echo json_encode(['status' => 'success', 'message' => 'تم إرسال طلبك للمالك']);
    } catch (Exception $e) { echo json_encode(['status' => 'error', 'message' => 'البريد مسجل مسبقاً']); }
}

// --- طلب استعادة كلمة السر ---
if ($action == 'forgot_password') {
    $stmt = $pdo->prepare("INSERT INTO password_requests (email) VALUES (?)");
    $stmt->execute([trim($input['email'])]);
    echo json_encode(['status' => 'success', 'message' => 'تم إرسال طلب الاستعادة للمالك']);
}

// --- دخول جوجل ---
if ($action == 'google_auth') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$input['email']]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($u) {
        if ($u['is_approved'] == 1) echo json_encode(['status' => 'success', 'role' => $u['role']]);
        else echo json_encode(['status' => 'wait', 'message' => 'بانتظار التفعيل']);
    } else {
        $pdo->prepare("INSERT INTO users (email, full_name, is_approved) VALUES (?, ?, 0)")->execute([$input['email'], $input['full_name']]);
        echo json_encode(['status' => 'wait', 'message' => 'تم تسجيلك، بانتظار تفعيل المالك']);
    }
}

// --- الدخول التقليدي ---
if ($action == 'normal_login') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$input['email']]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($u && password_verify($input['pass'], $u['password'])) {
        if ($u['is_approved'] == 1) echo json_encode(['status' => 'success', 'role' => $u['role']]);
        else echo json_encode(['status' => 'error', 'message' => 'حسابك بانتظار التفعيل']);
    } else echo json_encode(['status' => 'error', 'message' => 'بيانات الدخول خاطئة']);
}

// --- لوحة التحكم (للمالك) ---
if ($action == 'get_admin_data') {
    $pending = $pdo->query("SELECT id, email, full_name FROM users WHERE is_approved = 0")->fetchAll(PDO::FETCH_ASSOC);
    $pass_reqs = $pdo->query("SELECT * FROM password_requests")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['pending' => $pending, 'pass_reqs' => $pass_reqs]);
}

if ($action == 'approve_user') {
    $pdo->prepare("UPDATE users SET is_approved = 1 WHERE id = ?")->execute([$input['id']]);
    echo json_encode(['status' => 'done']);
}

if ($action == 'del_req') {
    $pdo->prepare("DELETE FROM password_requests WHERE id = ?")->execute([$input['id']]);
    echo json_encode(['status' => 'done']);
}
?>