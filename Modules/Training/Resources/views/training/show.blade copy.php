<h3>{{ $training->title }}</h3>
<p>{{ $training->description }}</p>

@if($training->video_path)
    <video width="400" controls>
        <source src="{{ Storage::url($training->video_path) }}" type="video/mp4">
    </video>
@endif

<hr>
<h4>Chat</h4>

@foreach($training->chats as $chat)
    <div>
        <strong>{{ $chat->user->name }}</strong>: {{ $chat->message }}
        @foreach($chat->replies as $reply)
            <div style="margin-left: 20px;">
                <strong>{{ $reply->user->name }}</strong>: {{ $reply->message }}
            </div>
        @endforeach

        <form action="{{ route('training.chat.reply', $chat->id) }}" method="POST">
            @csrf
            <input type="text" name="message" placeholder="Reply...">
            <button>Reply</button>
        </form>
    </div>
@endforeach

<form action="{{ route('training.chat.ask', $training->id) }}" method="POST">
    @csrf
    <input type="text" name="message" placeholder="Ask a question...">
    <button>Send</button>
</form>
