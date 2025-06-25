@extends('layouts.student')

@section('title', 'Student Violation')

@section('css')
<link rel="stylesheet" href="{{ asset('css/student/student-violation.css') }}">
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
<!-- Student Header (Educator Style) -->
<div class="d-flex justify-content-between align-items-center mb-4" style="background: linear-gradient(135deg, #1e3c72, #2a5298); padding: 18px 32px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.10); color: #fff;">
    <div style="display: flex; flex-direction: column;">
        <span class="fw-bold" style="font-size: 1.5rem;">{{ auth()->user()->name }}</span>
        <span style="font-size: 1rem; opacity: 0.9;">ID: {{ auth()->user()->student_id ?? 'N/A' }}</span>
        @php
            // Define penalty hierarchy (higher value = more severe)
            $penaltyPriority = [
                'Exp' => 5,    // Expulsion
                'T'   => 4,    // Termination of Contract
                'Pro' => 3,    // Probation (long code)
                'P'   => 3,    // Probation (short code)
                'WW'  => 2,    // Written Warning
                'W'   => 1,    // Warning
                'VW'  => 0,    // Verbal Warning (long)
                'V'   => 0,    // Verbal Warning (short)
            ];

            // Human-readable labels for each penalty code
            $penaltyLabels = [
                'Exp' => 'Expulsion',
                'T'   => 'Termination of Contract',
                'Pro' => 'Probation',
                'P'   => 'Probation',
                'WW'  => 'Written Warning',
                'W'   => 'Warning',
                'VW'  => 'Verbal Warning',
                'V'   => 'Verbal Warning',
            ];

            // Color mapping for quick visual cues
            $penaltyColors = [
                'Exp' => '#e74c3c',   // red
                'T'   => '#c0392b',   // dark red
                'Pro' => '#e67e22',   // orange
                'P'   => '#e67e22',   // orange
                'WW'  => '#f1c40f',   // yellow
                'W'   => '#f1c40f',   // yellow
                'VW'  => '#3498db',   // blue
                'V'   => '#3498db',   // blue
            ];
            $maxPenalty = null;
            foreach ($violations as $violation) {
                if (!$maxPenalty || ($penaltyPriority[$violation->penalty] ?? -1) > ($penaltyPriority[$maxPenalty] ?? -1)) {
                    $maxPenalty = $violation->penalty;
                }
            }
        @endphp
        @if($maxPenalty)
            <span style="font-size: 1rem; margin-top: 2px; color: {{ $penaltyColors[$maxPenalty] ?? '#fff' }}; font-weight: 600;">
                Status: {{ $penaltyLabels[$maxPenalty] ?? $maxPenalty }}
            </span>
        @else
            <span style="font-size: 1rem; margin-top: 2px; color: #2ecc71; font-weight: 600;">
                Status: Good Standing
            </span>
        @endif
    </div>
    <div class="real-time-display" style="font-size: 1.2rem; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-clock"></i> <span id="current-time"></span>
    </div>
</div>

<div class="container">
    <div class="page-content">
        <!-- Page Header -->
        <div class="mb-3">
        <h2>My Violations</h2>
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
                    <span class="badge {{ $violation->status === 'active' ? 'bg-success' : 'bg-secondary' }}" style="margin-left: 10px;">
                        {{ ucfirst($violation->status) }}
                    </span>
                    <div class="violation-details">
                        <p>Category: {{ $violation->category_name }}</p>
                                                <p>Consequence: {{ $violation->consequence }}</p>
                        @php
                            $penaltyText = match($violation->penalty) {
                                'W' => 'Written Warning',
                                'V' => 'Verbal Warning',
                                'VW' => 'Verbal Warning',
                                'WW' => 'Written Warning',
                                'P' => 'Probation',
                                'Pro' => 'Probation',
                                'T' => 'Termination of Contract',
                                'Exp' => 'Expulsion',
                                default => $violation->penalty
                            };
                        @endphp
                        <p>Penalty: {{ $penaltyText }}</p>
@php
    $resolvedDate = $violation->resolved_date ?? ($violation->updated_at ?? null);
@endphp
@if($violation->status === 'resolved' && $resolvedDate)
    <p>Resolved: {{ \Carbon\Carbon::parse($resolvedDate)->format('M d, Y') }}</p>
@endif
                        <p class="text-muted"><small>Recorded: {{ $violation->created_at->format('M d, Y') }}</small></p>
                    </div>
                </div>
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
