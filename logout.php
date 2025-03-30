<?php
session_start();

// 更新用户状态为离线
$usersFile = 'data/users.json';
if (file_exists($usersFile) && isset($_SESSION['username'])) {
    $users = json_decode(file_get_contents($usersFile), true);
    foreach ($users as &$user) {
        if ($user['username'] === $_SESSION['username']) {
            $user['online'] = false;
            $user['last_active'] = time();
            break;
        }
    }
    file_put_contents($usersFile, json_encode($users));
}

// 清除所有会话变量
$_SESSION = [];

// 如果需要，销毁会话cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 销毁会话
session_destroy();

// 重定向到登录页面
header("Location: login.php");
exit();
?>