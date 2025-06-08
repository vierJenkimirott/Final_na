@extends('layouts.student')

@section('title', 'Notifications')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/student/student.css') }}">
    <style>
        .notification-card {
            margin-bottom: 15px;
            border-left: 4px solid #007bff;
        }
        .notification-card.unread {
            background-color: #f8f9fa;
            border-left: 4px solid #dc3545;
        }
        .notification-time {
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
@endsection

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Notifications</h4>
                </div>
                <div class="card-body">
                    @if(count($notifications ?? []) > 0)
                        @foreach($notifications as $notification)
                            <div class="card notification-card {{ $notification->is_read ? '' : 'unread' }}">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $notification->title }}</h5>
                                    <p class="card-text">{{ $notification->message }}</p>
                                    <p class="notification-time">{{ $notification->created_at->diffForHumans() }}</p>
                                    @if($notification->type == 'violation')
                                        <a href="{{ route('student.violation') }}" class="btn btn-sm btn-primary">View Violation</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-info">
                            You have no notifications at this time.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
