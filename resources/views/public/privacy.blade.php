@extends('layouts.app')
@section('title', 'Privacy Policy')
@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 3rem; max-width: 700px;">
    <div class="page-header"><h1>Privacy Policy</h1></div>
    <div class="card"><div class="card-body">
        {!! nl2br(e($siteSettings['privacy_content'] ?? 'Privacy policy will be displayed here. The admin can update this content from the settings panel.')) !!}
    </div></div>
</div>
@endsection
