@extends('training::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('training.name') !!}</p>
@endsection
