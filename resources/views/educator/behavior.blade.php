@extends('layouts.educator')


@section('css')
    <link rel="stylesheet" href="{{ asset('css/behavior.css') }}">
@endsection

@section('content')

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card">
                    <p class="title">Total Students <img src="{{ asset('images/student-icon.png') }}" alt="" class="icon"></p>
                    <h3>120</h3>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <p class="title">Need Attention <img src="{{ asset('images/warning-removebg-preview.png') }}" alt="" class="icon"></p>
                    <h3>5</h3>
                </div>
            </div>
        </div>
        
        <div class="row g-3 mt-3">
            <div class="col-12">
                <div class="card">
                    <h2>Behavior Status Overview</h2>
                    <div class="behavior-report-controls">
                        <div class="behavior-labels">
                            <span class="behavior-label" style="background: blue;"></span>
                            <span>Boys Behavior</span>
                            <span class="behavior-label" style="background: pink;"></span>
                            <span>Girls Behavior</span>
                        </div>
                        <div class="time-select-container">
                            <select class="form-select">
                                <option value="monthly" selected>Monthly</option>
                            </select>
                        </div>
                    </div>
                    <div class="behavior-report">
                        <canvas id="behaviorChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const monthlyData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Boys Behavior',
                data: [65, 59, 80, 81, 56, 55, 40, 60, 50, 70, 80, 90],
                fill: false,
                borderColor: 'blue',
                backgroundColor: 'blue',
                tension: 0.1
            },
            {
                label: 'Girls Behavior',
                data: [28, 48, 40, 19, 86, 27, 90, 30, 40, 50, 60, 70],
                fill: false,
                borderColor: 'pink',
                backgroundColor: 'pink',
                tension: 0.1
            }]
        };

        const ctx = document.getElementById('behaviorChart').getContext('2d');

        const config = {
            type: 'line',
            data: monthlyData,  
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            usePointStyle: true,  
                            pointStyle: 'circle',
                            padding: 20  
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: true
                        }
                    },
                    y: {
                        grid: {
                            display: true
                        },
                        min: 0,
                        max: 100,
                        ticks: {
                            stepSize: 10
                        }
                    }
                }
            }
        };

        const behaviorChart = new Chart(ctx, config);

        document.getElementById('timeSelect').addEventListener('change', function() {
            const selectedValue = this.value;
            let selectedData;

            switch (selectedValue) {
                case 'monthly':
                    selectedData = monthlyData;
                    break;
                default:
                    selectedData = monthlyData;
            }

            behaviorChart.data = selectedData;
            behaviorChart.update();
        });
    </script>
    <script src="scripts.js"></script>
</body>
</html>  
@endpush