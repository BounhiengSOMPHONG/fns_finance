@extends('layouts.admin')

@section('title', __('Profile'))
@section('page-title', __('Profile'))

@section('content')
<div class="max-w-7xl space-y-6">
    <div class="rounded-lg bg-white p-4 shadow sm:rounded-lg sm:p-8">
        <div class="max-w-xl">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    <div class="rounded-lg bg-white p-4 shadow sm:rounded-lg sm:p-8">
        <div class="max-w-xl">
            @include('profile.partials.update-password-form')
        </div>
    </div>

    <div class="rounded-lg bg-white p-4 shadow sm:rounded-lg sm:p-8">
        <div class="max-w-xl">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection
