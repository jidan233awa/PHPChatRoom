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
$success = '';

// 处理注册请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($username) || empty($password) || empty($confirmPassword)) {
        $error = '所有字段都必须填写';
    } elseif ($password !== $confirmPassword) {
        $error = '两次输入的密码不一致';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $error = '用户名长度必须在3-20个字符之间';
    } elseif (strlen($password) < 6) {
        $error = '密码长度必须至少为6个字符';
    } else {
        $users = json_decode(file_get_contents($usersFile), true);
        
        // 检查用户名是否已存在
        $userExists = false;
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                $userExists = true;
                break;
            }
        }
        
        if ($userExists) {
            $error = '该用户名已被使用';
        } else {
            // 创建新用户
            $users[] = [
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'created_at' => time(),
                'last_active' => time(),
                'online' => true
            ];
            
            file_put_contents($usersFile, json_encode($users));
            $success = '注册成功，请登录';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册 - 实时多人聊天室</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>注册新账号</h2>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
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
                <div class="form-group">
                    <label for="confirm_password">确认密码</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">注册</button>
                </div>
                <div class="auth-links">
                    <p>已有账号？<a href="login.php">立即登录</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>