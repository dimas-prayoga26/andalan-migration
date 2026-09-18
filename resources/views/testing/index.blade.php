@extends('layouts.main')

@section('title', 'Testing')

@section('navbarTitle', 'Testing')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Testing',
    'current' => 'Testing',
    'homeRoute' => 'dashboard',
])

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header border-0">
                <div>
                    <h4 class="card-title mb-1">Testing Page</h4>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
