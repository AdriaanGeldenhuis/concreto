@extends('layouts.app')
@section('title', 'Request a Quote')
@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 3rem; max-width: 600px;">
    <div class="page-header"><h1>Request a Quote</h1><p class="text-muted">Tell us what you need and we'll get back to you with a competitive quote.</p></div>
    <div class="card"><div class="card-body">
        <form>
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="tel" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Products needed</label>
                <textarea class="form-control" rows="4" placeholder="E.g. 10 tons river sand, 5 tons 19mm stone..." required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Delivery address</label>
                <input type="text" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit Quote Request</button>
        </form>
    </div></div>
</div>
@endsection
