@extends('layouts.app')

@section('content')
<div id="chat-app">
    <div>
        <h3>Chat with User</h3>
        <ul id="messages"></ul>
        <input type="text" id="message" placeholder="Type a message...">
        <button onclick="sendMessage()">Send</button>
    </div>
</div>

<!-- Correct Vite Asset Loading -->
<script src="{{ ('resource/js/app.js') }}" defer></script>

@endsection
