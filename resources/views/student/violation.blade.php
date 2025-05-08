@extends('layouts.student')

@section('title', 'Violation History')

@section('css')
<link rel="stylesheet" href="{{ asset('css/student-violation.css') }}">
<style>
    .no-violations {
        text-align: center;
        padding: 2rem;
        background-color: #f8f9fa;
        border-radius: 8px;
        margin: 1rem 0;
    }
    .no-violations p {
        color: #6c757d;
        font-size: 1.1rem;
        margin: 0;
    }
</style>
@endsection

@section('content')
<div class="container">
    <h2>Violation History</h2>

    @if($violations->isEmpty())
        <div class="no-violations">
            <p>No violations found.</p>
        </div>
    @else
        @foreach($violations as $violation)
        <div class="violation-card" onclick="this.classList.toggle('open')">
            <div class="violation-main">
                <div class="title">⚠️ {{ $violation->violation_name }}</div>
                <div class="date">{{ \Carbon\Carbon::parse($violation->violation_date)->format('m-d-y') }}</div>
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
                </div>
            </div>
            <span class="status {{ strtolower($violation->status) }}">{{ ucfirst($violation->status) }}</span>
        </div>
        @endforeach
    @endif
</div>
@endsection