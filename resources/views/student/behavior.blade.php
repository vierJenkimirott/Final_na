@extends('layouts.student')

@section('title', 'Behavior Report')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/student-violation.css') }}">
    @endsection

@section('content')
<h2 style="text-align: left; color: navy;">Behavior Report</h2>
    <div class="container">
        <div class="filter-buttons">
            <button>12 Months</button>
            <button class="active">6 Months</button>
        </div>
        <div class="chart-container" style="position: relative; height: 400px; width: 100%;">
            <canvas id="behaviorChart"></canvas>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>   
    <script>
        const ctx = document.getElementById('behaviorChart').getContext('2d');
        const behaviorChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [
                    {
                        label: 'Good',
                        data: [20, 40, 30, 10, 20, 40],
                        borderColor: 'cyan',
                        backgroundColor: 'cyan',
                        fill: false,
                    },
                    {
                        label: 'Bad',
                        data: [-10, -20, -30, -40, -30, -20],
                        borderColor: 'magenta',
                        backgroundColor: 'magenta',
                        fill: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
    @endpush
@endsection