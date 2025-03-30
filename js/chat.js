// 聊天室JavaScript功能

// 全局变量
let lastMessageId = 0;
let lastUserUpdate = 0;

// DOM元素
const messagesContainer = document.getElementById('messages');
const messageForm = document.getElementById('message-form');
const messageInput = document.getElementById('message');
const usersList = document.getElementById('users-list');
const refreshUsersBtn = document.getElementById('refresh-users');

// 初始化
document.addEventListener('DOMContentLoaded', () => {
    // 加载初始消息
    loadMessages();
    
    // 加载在线用户
    loadOnlineUsers();
    
    // 设置定时器，定期检查新消息和在线用户
    setInterval(loadMessages, 2000); // 每2秒检查一次新消息
    setInterval(loadOnlineUsers, 5000); // 每5秒更新一次在线用户列表
    
    // 消息发送事件监听
    messageForm.addEventListener('submit', sendMessage);
    
    // 刷新用户列表按钮事件监听
    refreshUsersBtn.addEventListener('click', () => {
        lastUserUpdate = 0; // 重置上次更新时间，强制刷新
        loadOnlineUsers(true); // 传入true表示这是手动刷新
    });
});

// 加载消息
async function loadMessages() {
    try {
        const response = await fetch(`api/get_messages.php?last_id=${lastMessageId}`);
        const data = await response.json();
        
        if (data.messages && data.messages.length > 0) {
            // 添加新消息到聊天区域
            data.messages.forEach(message => {
                appendMessage(message);
                if (message.id > lastMessageId) {
                    lastMessageId = message.id;
                }
            });
            
            // 滚动到最新消息
            scrollToBottom();
        }
    } catch (error) {
        console.error('加载消息失败:', error);
    }
}

// 加载在线用户
async function loadOnlineUsers(isManualRefresh = false) {
    try {
        const response = await fetch(`api/get_users.php?last_update=${lastUserUpdate}`);
        const data = await response.json();
        
        // 无论是否有更新，都更新用户列表
        if (data.users) {
            updateUsersList(data.users, isManualRefresh);
            lastUserUpdate = data.timestamp;
        }
    } catch (error) {
        console.error('加载用户列表失败:', error);
    }
}

// 发送消息
async function sendMessage(event) {
    event.preventDefault();
    
    const messageText = messageInput.value.trim();
    if (!messageText) return;
    
    try {
        const formData = new FormData();
        formData.append('message', messageText);
        
        const response = await fetch('api/send_message.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // 清空输入框
            messageInput.value = '';
            
            // 立即加载新消息
            loadMessages();
        }
    } catch (error) {
        console.error('发送消息失败:', error);
    }
}

// 添加消息到聊天区域
function appendMessage(message) {
    const messageElement = document.createElement('div');
    messageElement.classList.add('message');
    
    // 判断是自己发送的还是接收的消息
    const isSentByMe = message.username === currentUsername;
    messageElement.classList.add(isSentByMe ? 'sent' : 'received');
    
    // 创建消息内容
    let messageHTML = '';
    
    // 如果不是自己发送的，显示发送者名称
    if (!isSentByMe) {
        messageHTML += `<div class="message-sender">${message.username}</div>`;
    }
    
    // 消息内容
    messageHTML += `<div class="message-content">${message.content}</div>`;
    
    // 消息时间
    const messageTime = new Date(message.timestamp * 1000).toLocaleTimeString();
    messageHTML += `<div class="message-time">${messageTime}</div>`;
    
    messageElement.innerHTML = messageHTML;
    messagesContainer.appendChild(messageElement);
}

// 更新用户列表
function updateUsersList(users, isManualRefresh = false) {
    // 清空当前列表
    usersList.innerHTML = '';
    
    // 过滤出在线用户
    const onlineUsers = users.filter(user => user.online);
    
    // 添加在线用户
    onlineUsers.forEach(user => {
        const userElement = document.createElement('li');
        
        // 用户头像（显示用户名首字母）
        const avatarLetter = user.username.charAt(0).toUpperCase();
        
        userElement.innerHTML = `
            <div class="user-avatar">${avatarLetter}</div>
            <span>${user.username}</span>
        `;
        
        usersList.appendChild(userElement);
    });

    // 只在手动刷新时显示提示
    if (isManualRefresh) {
        showRefreshNotification(true, onlineUsers.length);
    }
}

// 显示刷新提示
function showRefreshNotification(success, userCount) {
    // 创建或获取提示元素
    let notification = document.getElementById('refresh-notification');
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'refresh-notification';
        notification.classList.add('glass-effect');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 10px 20px;
            border-radius: 8px;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        document.body.appendChild(notification);
    }

    // 设置提示内容和样式
    notification.textContent = success ? `刷新成功，当前共有 ${userCount} 人在线` : '刷新失败';
    notification.style.backgroundColor = success ? 'rgba(75, 181, 67, 0.9)' : 'rgba(255, 76, 76, 0.9)';
    notification.style.color = '#fff';

    // 显示提示
    notification.style.opacity = '1';

    // 3秒后隐藏提示
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// 滚动到最新消息
function scrollToBottom() {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// 获取当前登录用户名
const currentUsername = document.querySelector('.username').textContent.trim();