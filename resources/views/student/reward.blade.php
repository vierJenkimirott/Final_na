@extends('layouts.student')

@section('title', 'Rewards')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/student-violation.css') }}">
@endsection

@section('content')
<h2 style="text-align: left; color: navy;">Reward Points</h2>
<div class="balance-box">
    Your behavior points balance <br>
    <span class="points">100 points</span>
</div>

<div class="earn-history-box">
    <button onclick="location.href='{{ route('student.earn_points') }}'">Earn more points</button>
    <button onclick="location.href='{{ route('student.redemption') }}'">History</button>

</div>

<div class="rewards-container">
    <div class="reward-item">
        <h4>6 hours going out without phone</h4>
        <span class="points">100 Points</span>
        <button>redeem</button>
    </div>
    <div class="reward-item">
        <h4>6 hours of phone without going out</h4>
        <span class="points">100 Points</span>
        <button>redeem</button>
    </div>

    </div> 
</div>
@endsection