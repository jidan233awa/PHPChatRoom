<?php
session_start();

// 检查用户是否已登录
if (!isset($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => '未登录']);
    exit();
}

// 获取上次更新时间
$lastUpdate = isset($_GET['last_update']) ? intval($_GET['last_update']) : 0;

// 确保用户文件存在
$usersFile = '../data/users.json';
if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([]));
    header('Content-Type: application/json');
    echo json_encode(['updated' => true, 'users' => [], 'timestamp' => time()]);
    exit();
}

// 获取所有用户
$users = json_decode(file_get_contents($usersFile), true);

// 清理离线用户并更新当前用户的最后活动时间
$currentTime = time();
$timeout = 120; // 2分钟超时

foreach ($users as $key => &$user) {
    if ($user['username'] === $_SESSION['username']) {
        $user['last_active'] = $currentTime;
        $user['online'] = true;
    } else {
        // 检查用户是否超时
        if ($currentTime - $user['last_active'] > $timeout) {
            $user['online'] = false;
        }
    }
}
unset($user); // 解除引用

// 保存更新后的用户数据
file_put_contents($usersFile, json_encode($users));

// 过滤出在线用户
$onlineUsers = array_filter($users, function($user) {
    return $user['online'] === true;
});

// 格式化在线用户数据
$formattedUsers = array_map(function($user) {
    return [
        'username' => $user['username'],
        'online' => true,
        'last_active' => $user['last_active']
    ];
}, $onlineUsers);

// 按用户名排序
usort($formattedUsers, function($a, $b) {
    return strcmp($a['username'], $b['username']);
});

// 返回在线用户列表
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
echo json_encode([
    'updated' => true,
    'users' => $formattedUsers,
    'timestamp' => $currentTime
]);