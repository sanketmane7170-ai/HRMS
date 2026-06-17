<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h3>User Scores for Training: {{ $training->title }}</h3>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>


        <table class="table table-bordered table-striped light">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Score</th>
                    <th>Total Questions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($userScores as $index => $item)
                <tr style="background-color: white;">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['user']->name }}</td>
                    <td>{{ $item['user']->email }}</td>
                    <td>{{ $item['score'] }}</td>
                    <td>{{ $item['total'] }}</td>
                </tr>
                @empty
                <tr style="background-color: white;">
                    <td colspan="5"  class="text-center">No attempts yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
