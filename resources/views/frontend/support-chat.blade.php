@extends('frontend.layouts.main')

@section('title', __('ติดต่อสอบถาม') . ' - AEG')

@section('content')
    <div class="container">
        <div style="max-width: 800px; margin: 40px auto; height: 620px;">
            {{-- $topic มาจาก ?topic= (null = ให้ลูกค้าเลือกหัวข้อในหน้าจอ) --}}
            @include('frontend.partials.chatbot-widget', ['botUserId' => Auth::id(), 'chatTopic' => $topic ?? null])
        </div>
    </div>
@endsection
