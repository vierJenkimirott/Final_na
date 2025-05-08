@extends('layouts.student')

@section('content')
<div class="container">
    <h1 class="mb-4">Student Reward History</h1>
    <div class="card">
        <div class="card-header">
            Reward History
        </div>
        <div class="card-body">
          
            @php
                $rewards = [
                    ['name' => '6 hours of phone without going out',  'created_at' => now()->subDays(10)],
                    ['name' => '6 hours going out without phone', 'created_at' => now()->subDays(20)],
                     ];
            @endphp

            @if(empty($rewards))
                <p>No rewards found for this student.</p>
            @else
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Reward Name</th>
                            <th>Date Earned</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rewards as $index => $reward)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $reward['name'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($reward['created_at'])->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection

