@extends('layouts.student')

@section('title', 'Student Account')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/student.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="profile-header">
                    <img src="{{ asset('images/newprof.png') }}" alt="Profile Picture" class="profile-picture">
                    <div class="profile-info">
                        <h1>{{ $user->name }}</h1>
                        {{-- <div class="student-details">
                            <span>Student ID: {{ $studentId }}</span>
                            <span class="status-badge">{{ $status }}</span>
                        </div>
                        <p class="grade">Grade {{ $gradeLevel }}</p>
                    </div> --}}
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <h2>Contact Information</h2>
                <div class="info-section">
                    <label>Email Address</label>
                    <div class="info-value">{{ $user->email }}</div>
                </div>

                <div class="info-section">
                    <label>Role</label>
                    <div class="info-value">{{ $user->roles->first() ? ucfirst($user->roles->first()->name) : 'Student' }}</div>
                </div>

                <div class="info-section">
                    <label>Parent Contact</label>
                    <div class="info-value">Sarah Johnson</div>
                </div>

                <div class="info-section">
                    <label>Parent Phone</label>
                    <div class="info-value">(555) 987-6543</div>
                </div>
            </div>
        </div>
    </div>
@endsection