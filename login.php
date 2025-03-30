<?php
session_start();

// 如果用户已登录，直接跳转到聊天室
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// 确保data目录存在
if (!file_exists('data')) {
    mkdir('data', 0777, true);
}

// 确保用户文件存在
$usersFile = 'data/users.json';
if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([]));
}

$error = '';

// 处理登录请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = '用户名和密码不能为空';
    } else {
        $users = json_decode(file_get_contents($usersFile), true);
        $authenticated = false;
        
        foreach ($users as &$user) {
            if ($user['username'] === $username && password_verify($password, $user['password'])) {
                $authenticated = true;
                $user['last_active'] = time();
                $user['online'] = true;
                break;
            }
        }
        
        if ($authenticated) {
            $_SESSION['username'] = $username;
            file_put_contents($usersFile, json_encode($users));
            header("Location: index.php");
            exit();
        } else {
            $error = '用户名或密码错误';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 实时多人聊天室</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>登录聊天室</h2>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="post" action="">
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">登录</button>
                </div>
                <div class="auth-links">
                    <p>还没有账号？<a href="register.php">立即注册</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>