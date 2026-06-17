<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('edit_training') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.training.update',$training)}}" datatable="true" method="POST"
            class="ajax-form-submit reset">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="department_id">Select Department</label>
                        <select name="department_id" class="form-control" required>
                            <option value="">-- Select Department --</option>
                            @foreach($departments as $department)
                            <option value="{{ $department->id }}"
                                {{ $training->department_id == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="title">Training Title</label>
                        <input type="text" name="title" class="form-control" value="{{ $training->title }}" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="description">Training Description</label>
                        <textarea name="description" class="form-control" rows="4"
                            required>{{ $training->description }}</textarea>
                    </div>

                    <!-- <div class="col-md-12 mb-3">
                        <label for="videos">Add More Training Videos</label>
                        <input type="file" name="videos[]" class="form-control" accept="video/*" multiple>
                    </div>

                    @if ($training->videos->count())
                    <div class="col-md-12 mb-3">
                        <label>Existing Videos:</label>
                        @foreach ($training->videos as $video)
                        <video width="100%" height="300" controls class="mb-2">
                            <source src="{{ Storage::url($video->video_path) }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        @endforeach
                    </div>
                    @endif -->
                    <div class="col-md-12 mb-3">
                        <label for="files">Add More Training Files</label>
                        <input type="file" name="files[]" class="form-control" multiple>
                    </div>

                    @if ($training->videos->count())
                    <div class="col-md-12 mb-3">
                        <label>Existing Files:</label>

                        @foreach ($training->videos as $file)
                        @php
                        $extension = pathinfo($file->video_path, PATHINFO_EXTENSION);
                        $url = Storage::url($file->video_path);
                        @endphp

                        <div class="mb-3">

                            {{-- Show Video --}}
                            @if(in_array($extension, ['mp4','avi','mkv','webm']))
                            <video width="100%" height="300" controls>
                                <source src="{{ $url }}">
                                Your browser does not support the video tag.
                            </video>

                            {{-- Show Image --}}
                            @elseif(in_array($extension, ['jpg','jpeg','png','gif']))
                            <img src="{{ $url }}" width="100%" height="300" class="img-fluid">

                            {{-- Show Other Files --}}
                            @else
                            <a href="{{ $url }}" target="_blank" class="btn btn-primary">
                                View / Download {{ strtoupper($extension) }} File
                            </a>
                            @endif

                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __trans('close') }}</button>
                <button type="submit" class="btn btn-primary">{{ __trans('save') }}</button>
            </div>
        </form>

    </div>
</div>