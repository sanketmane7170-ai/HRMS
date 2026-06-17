<!-- Chat Icon -->
<a href="javascript:void(0);" id="chat-icon" class="nav-link chat-icon">
    <i class="fas fa-comments"></i>
    <span>Chat</span>
    <span class="badge" id="unread-count" style="display:none;"></span>
</a>

<!-- Chat Window -->
<div id="chat-window" class="chat-window" style="display:none;">
    <div class="chat-header">
        <h4>Chat</h4>
        <button onclick="closeChatWindow()">X</button>
    </div>
    <div id="chat-body" class="chat-body"></div>
    <div class="chat-footer">
        <input type="text" id="chat-input" placeholder="Type your message..." onkeydown="sendMessageOnEnter(event)" />
        <button onclick="sendMessage()">Send</button>
    </div>
</div>
