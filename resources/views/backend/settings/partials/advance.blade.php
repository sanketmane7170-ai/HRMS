<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col">
                <h4>{{__trans('website_advance_settings')}}</h4>
            </div>
            <div class="col-auto">

            </div>
        </div>
    </div>
    <div class="card-body grid-structure">
        <form action="{{route('backend.settings.advance.post')}}" method="POST" class="ajax-form-submit">
            @csrf
            <div class="row">
                <div class="col-md-12 grid-container">
                    {{__trans('header_code')}}
                </div>
                <div class="col-md-12">
                    <textarea name="custom_header_code" id="custom_header_code" class="form-control" rows="10">{!! getSetting('custom_header_code')!!}</textarea>
                </div>
                <div class="col-md-12 grid-container mt-2">
                    {{__trans('footer_code')}}
                </div>
                <div class="col-md-12">
                    <textarea name="custom_footer_code" id="custom_footer_code" class="form-control" rows="10">{!! getSetting('custom_footer_code')!!}</textarea>
                </div>
            </div>
            <div class=" text-end mt-4">
                <button type="submit" class="btn btn-primary">{{__trans('update_settings')}} </button>
            </div>
        </form>
    </div>
</div>

@push('css')
<link href='//cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.css' rel='stylesheet'>
<link href='//cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/theme/hopscotch.css' rel='stylesheet'>
<link href='//cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/theme/monokai.css' rel='stylesheet'>
@endpush




@push('scripts')
<!-- The link above loaded the core css -->
<script src='//cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.js'></script>
<script src='//cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/xml/xml.js'></script>
<script src='//cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/css/css.js'></script>
<script src='//cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/javascript/javascript.js'></script>
<script src='//cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/htmlmixed/htmlmixed.js'></script>

<script>
    var headerEditor = CodeMirror.fromTextArea(document.getElementById("custom_header_code"), {
        lineNumbers: true,
        mode: 'htmlmixed',
        theme: 'hopscotch',
    });
    var footerEditor = CodeMirror.fromTextArea(document.getElementById("custom_footer_code"), {
        lineNumbers: true,
        mode: 'htmlmixed',
        theme: 'monokai',

    });
</script>
@endpush
