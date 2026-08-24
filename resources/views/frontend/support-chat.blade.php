@extends('frontend.layouts.main')

@section('title', 'ติดต่อสอบถาม - AEG')

@section('content')
    <div class="container">
        <div style="max-width: 800px; margin: 40px auto; height: 620px;">
            @include('frontend.partials.chatbot-widget', ['botUserId' => Auth::id()])
        </div>
    </div>
@endsection
