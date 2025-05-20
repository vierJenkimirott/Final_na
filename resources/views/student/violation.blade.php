@extends('layouts.student')

@section('title', 'Violation History')

@section('css')
<link rel="stylesheet" href="{{ asset('css/student-violation.css') }}">
<style>
    /* Basic Styling */
    .no-violations {
        text-align: center;
        padding: 2rem;
        background-color: #f8f9fa;
        border-radius: 8px;
        margin: 1rem 0;
        color: #6c757d;
    }
    
    /* Student Header */
    .student-header {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        padding: 15px 0;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        color: white;
    }
    
    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .student-info {
        display: flex;
        align-items: center;
    }
    
    .student-name {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
    }
    
    .student-id, .real-time-display {
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 500;
        margin-left: 15px;
    }
    
    .real-time-display {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    /* Page Content */
    .page-content {
        margin-top: 20px;
    }
</style>
@endsection

@section('content')
<!-- Student Header with Name and Clock -->
<header class="student-header">
    <div class="container header-container">
        <div class="student-info">
            <h1 class="student-name">{{ auth()->user()->name }}</h1>
            <span class="student-id">ID: {{ auth()->user()->student_id ?? 'N/A' }}</span>
        </div>
        <div class="real-time-display">
            <i class="fas fa-clock"></i> <span id="current-time"></span>
        </div>
    </div>
</header>

<div class="container">
    <div class="page-content">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>My Violations</h2>
            <button id="refreshBtn" class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
        
        <!-- Last Updated Info -->
        <div class="alert alert-info mb-3">
            <small>Last updated: {{ now()->format('M d, Y h:i A') }}</small>
            <p class="mb-0">Click on any violation to see more details.</p>
        </div>
        
        <!-- Violations List -->
        @if($violations->isEmpty())
            <div class="no-violations">
                <p>No violations found.</p>
            </div>
        @else
            @foreach($violations as $violation)
            <div class="violation-card" onclick="this.classList.toggle('open')">
                <div class="violation-main">
                    <div class="title">{{ $violation->violation_name }}</div>
                    <div class="date">{{ \Carbon\Carbon::parse($violation->violation_date)->format('M d, Y') }}</div>
                    <span class="severity {{ strtolower($violation->severity) }}">{{ $violation->severity }}</span>
                    <div class="violation-details">
                        <p>Category: {{ $violation->category_name }}</p>
                        <p>Description: {{ $violation->offense }}</p>
                        <p>Consequence: {{ $violation->consequence }}</p>
                        @php
                            $penaltyText = match($violation->penalty) {
                                'W' => 'Warning',
                                'VW' => 'Verbal Warning',
                                'WW' => 'Written Warning',
                                'Pro' => 'Probation',
                                'Exp' => 'Expulsion',
                                default => $violation->penalty
                            };
                        @endphp
                        <p>Penalty: {{ $penaltyText }}</p>
                        <p class="text-muted"><small>Recorded: {{ $violation->created_at->format('M d, Y') }}</small></p>
                    </div>
                </div>
                <span class="status {{ strtolower($violation->status) }}">{{ ucfirst($violation->status) }}</span>
            </div>
            @endforeach
        @endif
    </div>
</div>

<!-- Clock JavaScript -->
<script>
function updateClock() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
                     hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
    document.getElementById('current-time').textContent = now.toLocaleDateString('en-US', options);
}

updateClock();
setInterval(updateClock, 1000);
</script>
@endsection
