<?php
// 启动会话
session_start();

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 数据文件路径
define('DATA_DIR', __DIR__ . '/data');
define('USERS_FILE', DATA_DIR . '/users.json');
define('MESSAGES_FILE', DATA_DIR . '/messages.json');
define('SESSIONS_FILE', DATA_DIR . '/sessions.json');

// 确保数据目录存在
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0777, true);
}

// 初始化数据文件
function initDataFiles() {
    // 用户文件
    if (!file_exists(USERS_FILE)) {
        file_put_contents(USERS_FILE, json_encode([
            ['id' => 1, 'name' => '系统', 'online' => true, 'last_active' => time()]
        ]));
    }
    
    // 消息文件
    if (!file_exists(MESSAGES_FILE)) {
        file_put_contents(MESSAGES_FILE, json_encode([
            [
                'id' => 1, 
                'sender' => '系统', 
                'content' => '欢迎来到聊天室！', 
                'time' => date('H:i'), 
                'timestamp' => time(),
                'isSystem' => true
            ]
        ]));
    }
    
    // 会话文件
    if (!file_exists(SESSIONS_FILE)) {
        file_put_contents(SESSIONS_FILE, json_encode([]));
    }
}

// 初始化数据文件
initDataFiles();

// 当前用户信息
$currentUser = null;
if (isset($_SESSION['username'])) {
    $users = json_decode(file_get_contents(USERS_FILE), true);
    foreach ($users as $user) {
        if ($user['username'] === $_SESSION['username']) {
            $currentUser = $user;
            break;
        }
    }
}

// 检查用户是否已登录，如果没有则重定向到登录页面
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 更新用户在线状态
$users = json_decode(file_get_contents(USERS_FILE), true);
$currentUser = null;
foreach ($users as &$user) {
    if ($user['username'] === $_SESSION['username']) {
        $user['last_active'] = time();
        $user['online'] = true;
        $currentUser = $user;
        break;
    }
}

// 如果找不到用户，重定向到登录页面
if (!$currentUser) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// 更新用户状态
file_put_contents(USERS_FILE, json_encode($users));
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>实时多人聊天室</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="chat-container">
        <div class="sidebar">
            <div class="user-info">
                <div class="avatar"><?php echo substr($_SESSION['username'], 0, 1); ?></div>
                <div class="username"><?php echo $_SESSION['username']; ?></div>
            </div>
            <div class="online-users">
                <h3>在线用户</h3>
                <button id="refresh-users" class="refresh-btn">刷新用户列表</button>
                <ul id="users-list">
                    <!-- 在线用户列表将通过JavaScript动态加载 -->
                </ul>
            </div>
            <div class="actions">
                <a href="logout.php" class="logout-btn">退出登录</a>
            </div>
        </div>
        <div class="chat-area">
            <div class="chat-header">
                <h2>聊天室</h2>
            </div>
            <div class="messages" id="messages">
                <!-- 消息将通过JavaScript动态加载 -->
            </div>
            <div class="message-input">
                <form id="message-form">
                    <input type="text" id="message" placeholder="输入消息..." autocomplete="off">
                    <button type="submit">发送</button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/chat.js"></script>
</body>
</html>