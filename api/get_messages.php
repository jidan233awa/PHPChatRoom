<?php
session_start();

// 检查用户是否已登录
if (!isset($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => '未登录']);
    exit();
}

// 获取最后接收的消息ID
$lastId = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

// 确保消息文件存在
$messagesFile = '../data/messages.json';
if (!file_exists($messagesFile)) {
    file_put_contents($messagesFile, json_encode([]));
    header('Content-Type: application/json');
    echo json_encode(['messages' => []]);
    exit();
}

// 获取所有消息
$messages = json_decode(file_get_contents($messagesFile), true);

// 过滤出新消息
$newMessages = [];
foreach ($messages as $message) {
    if ($message['id'] > $lastId) {
        $newMessages[] = $message;
    }
}

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

// 返回新消息
header('Content-Type: application/json');
echo json_encode(['messages' => $newMessages]);