<script src="{{asset('assets/backend/plugins/toastr/toastr.min.js')}}"></script>
<link rel="stylesheet" href="{{asset('assets/backend/plugins/toastr/toastr.min.css')}}" />
@if ($message = Session::get('success'))
<script>
    toastr.success('{{$message}}');
</script>
@elseif ($message = Session::get('error'))
<script>
    toastr.error('{{$message}}');
</script>
@elseif ($message = Session::get('warning'))
<script>
    toastr.warning('{{$message}}');
</script>
@elseif ($message = Session::get('info'))
<script>
    toastr.info('{{$message}}');
</script>
@endif

@if ($errors->any())
<script>
    @foreach ($errors->all() as $error)
        toastr.error('{{ $error }}');
    @endforeach
</script>
@endif

<script>
    function toggleLoader() {
        $('#loader').toggleClass('d-none');
    }

    function showAlert(message, type = "success") {
        toastr[type](message);
    }
</script>
