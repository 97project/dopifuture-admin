@extends('layouts.guest')

@section('title', __('admin.account_deleted_title'))

@section('content')
    <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="mt-4 text-lg font-semibold dark:text-white">{{ __('admin.account_deleted_title') }}</h3>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('admin.account_deleted_body') }}</p>
    </div>
@endsection