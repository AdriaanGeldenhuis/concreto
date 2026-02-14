@extends('layouts.app')
@section('title', 'Terms & Conditions')
@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 3rem; max-width: 700px;">
    <div class="page-header"><h1>Terms & Conditions</h1></div>
    <div class="card"><div class="card-body">
        {!! nl2br(e($siteSettings['terms_content'] ?? 'Terms and conditions will be displayed here. The admin can update this content from the settings panel.')) !!}
    </div></div>
</div>
@endsection
