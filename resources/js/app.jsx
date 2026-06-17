import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';
import HapticThemeToggle from './components/HapticThemeToggle';

// Initialize React components
document.addEventListener('DOMContentLoaded', () => {
    const themeToggleContainer = document.getElementById('theme-toggle-react');
    if (themeToggleContainer) {
        const root = createRoot(themeToggleContainer);
        root.render(<HapticThemeToggle />);
    }
});
// import Echo from 'laravel-echo';
// import io from 'socket.io-client';

// window.io = io;

// window.Echo = new Echo({
//     broadcaster: 'socket.io',
//     host: 'http://127.0.0.1:8080',
//     transports: ['websocket', 'polling'],
// });
// window.io.on('connect', () => {
//     console.log('Connected to Reverb server');
// });

// window.io.on('disconnect', () => {
//     console.log('Disconnected from Reverb server');
// });

// window.Echo.connector.socket.on('connect', () => {
//     console.log('Connected to Reverb server');
// });

// window.Echo.connector.socket.on('disconnect', () => {
//     console.log('Disconnected from Reverb server');
// });
// // Handle incoming chat messages
// window.Echo.channel('chat')
//     .listen('.message.sent', (event) => {
//         console.log('New message received:', event.message);

//         // Play sound on new message
//         let audio = new Audio('/sounds/message-notification.mp3');
//         audio.play();

//         // Add message to chat body
//         const chatBody = document.getElementById("chat-body");
//         if (chatBody) {
//             const messageElement = document.createElement("div");
//             messageElement.textContent = event.message.message;
//             chatBody.appendChild(messageElement);
//             chatBody.scrollTop = chatBody.scrollHeight;  // Auto-scroll to the bottom
//         }
//     });

// // Handle online user status
// window.Echo.channel('user-status')
//     .listen('.user.status.changed', (event) => {
//         console.log(event.user.name + ' is now ' + (event.user.online ? 'online' : 'offline'));
//         updateOnlineUserList(event.user);
//     });

// // Update online user list
function updateOnlineUserList(user) {
    const userList = document.getElementById('user-list');
    let userItem = document.getElementById('user-' + user.id);

    if (user.online) {
        if (!userItem) {
            userItem = document.createElement('li');
            userItem.id = 'user-' + user.id;
            userItem.textContent = user.name;
            userList.appendChild(userItem);
        }
    } else {
        if (userItem) {
            userItem.remove();
        }
    }
}

// Toggle chat window
document.addEventListener("DOMContentLoaded", function () {
    const chatIcon = document.getElementById("chat-icon");
    const chatWindow = document.getElementById("chat-window");
    const chatButton = document.getElementById("chat-floating-button");

    if (chatIcon) {
        chatIcon.addEventListener("click", toggleChatWindow);
    }

    if (chatButton) {
        chatButton.addEventListener("click", toggleChatWindow);
    }
});

// Toggle Chat Window Function
function toggleChatWindow() {
    const chatWindow = document.getElementById("chat-window");
    if (chatWindow) {
        chatWindow.style.display = chatWindow.style.display === "none" ? "block" : "none";
    }
}

// Close chat window
function closeChatWindow() {
    const chatWindow = document.getElementById("chat-window");
    if (chatWindow) {
        chatWindow.style.display = "none";
    }
}

// Send message on Enter key press
function sendMessageOnEnter(event) {
    if (event.key === "Enter") {
        sendMessage();
    }
}

// Send message to server
function sendMessage() {
    const messageInput = document.getElementById("chat-input");
    const message = messageInput.value.trim();

    if (message !== "") {
        fetch('/send-message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                message: message,
                receiver_id: 2 // Replace with dynamic receiver ID
            })
        }).then(response => response.json()).then(data => {
            console.log('Message sent:', data);
            messageInput.value = "";
        });
    }
}
