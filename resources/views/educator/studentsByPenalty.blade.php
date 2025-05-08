@extends('layouts.educator')

@section('css')
    <style>
        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .page-header h2 {
            margin: 0;
            color: #333;
        }
        
        /* Search Bar */
        .search-container {
            margin-bottom: 20px;
        }
        
        .search-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        /* Students Table */
        .students-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .students-table th,
        .students-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .students-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        .students-table tr:last-child td {
            border-bottom: none;
        }
        
        .students-table tr:hover {
            background-color: #f5f5f5;
        }
        
        /* Penalty Badge */
        .penalty-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .penalty-W {
            background-color: #ffeeba;
            color: #856404;
        }
        
        .penalty-VW {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .penalty-WW {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .penalty-Pro {
            background-color: #d4edda;
            color: #155724;
        }
        
        .penalty-Exp {
            background-color: #cce5ff;
            color: #004085;
        }
        
        /* Back Button */
        .back-button {
            display: inline-block;
            padding: 8px 16px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .back-button:hover {
            background-color: #5a6268;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .empty-state p {
            color: #6c757d;
            font-size: 18px;
            margin-bottom: 20px;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="page-header">
            <h2>
                @switch($penalty)
                    @case('W')
                        Warning Students
                        @break
                    @case('VW')
                        Verbal Warning Students
                        @break
                    @case('WW')
                        Written Warning Students
                        @break
                    @case('Pro')
                        Probation Students
                        @break
                    @case('Exp')
                        Expulsion Students
                        @break
                    @default
                        Students by Penalty
                @endswitch
            </h2>
            <a href="{{ route('educator.violation') }}" class="back-button">Back to Violations</a>
        </div>
        
        <div class="search-container">
            <input type="text" id="searchInput" class="search-input" placeholder="Search students...">
        </div>
        
        @if(count($students) > 0)
            <table class="students-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Violation Type</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        @foreach($student->violations as $violation)
                            <tr>
                                <td>{{ $student->fname }} {{ $student->lname }}</td>
                                <td>
                                    @if($violation->violationType)
                                        {{ $violation->violationType->violation_name }}
                                    @else
                                        <span class="text-danger">N/A</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($violation->violation_date)->format('M d, Y') }}</td>
                                <td>
                                    <span class="penalty-badge penalty-{{ $violation->penalty }}">
                                        @switch($violation->penalty)
                                            @case('W')
                                                Warning
                                                @break
                                            @case('VW')
                                                Verbal Warning
                                                @break
                                            @case('WW')
                                                Written Warning
                                                @break
                                            @case('Pro')
                                                Probation
                                                @break
                                            @case('Exp')
                                                Expulsion
                                                @break
                                        @endswitch
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <p>No students found with this penalty type.</p>
                <a href="{{ route('educator.violation') }}" class="back-button">Back to Violations</a>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script>
    // =============================================
    // Search Functionality
    // =============================================
    /**
     * Filter table rows based on search input
     * Matches student information against search text
     */
    document.getElementById('searchInput').addEventListener('input', function() {
        const searchText = this.value.toLowerCase();
        const rows = document.querySelectorAll('.students-table tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchText) ? '' : 'none';
        });
    });
</script>
@endsection