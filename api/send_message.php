<?php
session_start();

// 检查用户是否已登录
if (!isset($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => '未登录']);
    exit();
}

// 检查是否是POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => '请求方法不正确']);
    exit();
}

// 检查消息内容是否存在
if (!isset($_POST['message']) || empty(trim($_POST['message']))) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => '消息不能为空']);
    exit();
}

// 确保data目录存在
if (!file_exists('../data')) {
    mkdir('../data', 0777, true);
}

// 确保消息文件存在
$messagesFile = '../data/messages.json';
if (!file_exists($messagesFile)) {
    file_put_contents($messagesFile, json_encode([]));
}

// 获取当前消息
$messages = json_decode(file_get_contents($messagesFile), true);

// 获取新消息ID
$newId = 1;
if (!empty($messages)) {
    $lastMessage = end($messages);
    $newId = $lastMessage['id'] + 1;
}

// 创建新消息
$newMessage = [
    'id' => $newId,
    'username' => $_SESSION['username'],
    'content' => htmlspecialchars(trim($_POST['message'])),
    'timestamp' => time()
];

// 添加新消息到消息列表
$messages[] = $newMessage;

// 保存消息到文件
file_put_contents($messagesFile, json_encode($messages));

// 更新用户最后活动时间
$usersFile = '../data/users.json';
if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true);
    foreach ($users as &$user) {
        if ($user['username'] === $_SESSION['username']) {
            $user['last_active'] = time();
            break;
        }
    }
    file_put_contents($usersFile, json_encode($users));
}

// 返回成功响应
header('Content-Type: application/json');
echo json_encode(['success' => true]);