@extends('layouts.educator')

@section('css')
    <!-- External CSS and Script Dependencies -->
    <link rel="stylesheet" href="{{ asset('css/violation.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom Styles -->
    <style>
        /* Main Layout Styles */
        main {
            padding: 20px 0;
        }
        
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        
        /* Action Buttons */
        .top-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .btn {
            background-color: #4e73df;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        
        .btn:hover {
            background-color: #2e59d9;
        }
        
        /* Warning Box Styling */
        .warning-section {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .column {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .column.center {
            justify-content: center;
        }
        
        .warning-box {
            background-color: #f8f9fc;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        
        .warning-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .warning-box.tall {
            height: 120px;
        }
        
        .warning-box.wide {
            height: 100%;
        }
        
        .warning-box span {
            font-size: 24px;
            font-weight: bold;
            margin-top: 10px;
            color: #4e73df;
        }
        
        /* Violation Table Styles */
        .violation-table {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }
        
        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .search-bar input, .search-bar select {
            padding: 8px 12px;
            border: 1px solid #d1d3e2;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .search-bar input {
            flex: 1;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e3e6f0;
        }
        
        th {
            background-color: #f8f9fc;
            font-weight: 600;
            color: #4e73df;
        }
        
        tr:hover {
            background-color: #f8f9fc;
        }
        
        /* Severity and Status Styling */
        .low {
            color: #ffc107;
        }
        
        .medium {
            color: #fd7e14;
        }
        
        .high {
            color: #e74a3b;
        }
        
        .very-high {
            color: #6f42c1;
        }
        
        .active {
            color: #1cc88a;
        }
        
        .inactive {
            color: #858796;
        }
        
        /* Action Icons Styling */
        .action-icon {
            color: #666;
            margin: 0 5px;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .action-icon:hover {
            color: #333;
        }
        
        .action-icon i {
            font-size: 1.1em;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0 text-gray-800">Manage Student Violations</h2>
        </div>

        <main>
            <!-- Action Buttons Section -->
            <div class="top-buttons">
                <a href="{{route('educator.add-violator-form')}}" class="btn">
                    <i class="fas fa-user-plus me-1"></i> Add Violator
                </a>
                <a href="{{route('educator.add-violation')}}" class="btn">
                    <i class="fas fa-exclamation-triangle me-1"></i> Add Violation
                </a>
            </div>

            <!-- Warning Statistics Section -->
            <section class="warning-section">
                <!-- Left Column - Warning and Written Warning -->
                <div class="column">
                    <a href="{{ route('educator.students-by-penalty', ['penalty' => 'W']) }}" class="warning-box tall">
                        <i class="fas fa-exclamation-circle mb-2" style="font-size: 1.5rem; color: #ffc107;"></i>
                        Warning Students
                        <span>{{ DB::table('violations')->where('penalty', 'W')->where('status', 'active')->count() }}</span>
                    </a>
                    <a href="{{ route('educator.students-by-penalty', ['penalty' => 'WW']) }}" class="warning-box tall">
                        <i class="fas fa-file-alt mb-2" style="font-size: 1.5rem; color: #4e73df;"></i>
                        Written Warning Students
                        <span>{{ DB::table('violations')->where('penalty', 'WW')->where('status', 'active')->count() }}</span>
                    </a>
                </div>

                <!-- Center Column - Verbal Warning -->
                <div class="column center">
                    <a href="{{ route('educator.students-by-penalty', ['penalty' => 'VW']) }}" class="warning-box wide">
                        <i class="fas fa-comments mb-2" style="font-size: 1.5rem; color: #36b9cc;"></i>
                        Verbal Warning Students
                        <span>{{ DB::table('violations')->where('penalty', 'VW')->where('status', 'active')->count() }}</span>
                    </a>
                </div>

                <!-- Right Column - Probation and Expulsion -->
                <div class="column">
                    <a href="{{ route('educator.students-by-penalty', ['penalty' => 'Pro']) }}" class="warning-box tall">
                        <i class="fas fa-user-clock mb-2" style="font-size: 1.5rem; color: #f6c23e;"></i>
                        Probation Students
                        <span>{{ DB::table('violations')->where('penalty', 'Pro')->where('status', 'active')->count() }}</span>
                    </a>
                    <a href="{{ route('educator.students-by-penalty', ['penalty' => 'Exp']) }}" class="warning-box tall">
                        <i class="fas fa-user-slash mb-2" style="font-size: 1.5rem; color: #e74a3b;"></i>
                        Expulsion Students
                        <span>{{ DB::table('violations')->where('penalty', 'Exp')->where('status', 'active')->count() }}</span>
                    </a>
                </div>
            </section>

            <!-- Violations Table Section -->
            <section class="violation-table">
                <!-- Search and Filter Controls -->
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search by student or violation..." class="form-control" />
                    <select id="severityFilter" class="form-select">
                        <option value="">All Severity</option>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                        <option value="Very High">Very High</option>
                    </select>
                </div>

                <!-- Violations Data Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Violation</th>
                                <th>Category</th>
                                <th>Student</th>
                                <th>Severity</th>
                                <th>Offense</th>
                                <th>Penalty</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="violation-list">
                            @foreach ($violations as $violation)
                                <tr>
                                    <td>
                                        @if($violation->violationType)
                                            {{ $violation->violationType->violation_name }}
                                        @else
                                            <span class="text-danger">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($violation->violationType && $violation->violationType->offenseCategory)
                                            {{ $violation->violationType->offenseCategory->category_name }}
                                        @else
                                            <span class="text-danger">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($violation->student)
                                            {{ $violation->student->fname }} {{ $violation->student->lname }}
                                        @else
                                            <span class="text-danger">N/A</span>
                                        @endif
                                    </td>
                                    <td class="{{ strtolower($violation->severity) }}">
                                        <span class="badge bg-{{ strtolower($violation->severity) === 'low' ? 'warning' : (strtolower($violation->severity) === 'medium' ? 'info' : (strtolower($violation->severity) === 'high' ? 'danger' : 'dark')) }}">{{ $violation->severity }}</span>
                                    </td>
                                    <td>{{ $violation->offense }}</td>
                                    <td>
                                        @switch($violation->penalty)
                                            @case('W')
                                                <span class="badge bg-warning text-dark">Warning</span>
                                                @break
                                            @case('VW')
                                                <span class="badge bg-info text-dark">Verbal Warning</span>
                                                @break
                                            @case('WW')
                                                <span class="badge bg-primary">Written Warning</span>
                                                @break
                                            @case('Pro')
                                                <span class="badge bg-warning text-dark">Probation</span>
                                                @break
                                            @case('Exp')
                                                <span class="badge bg-danger">Expulsion</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($violation->violation_date)->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge {{ $violation->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($violation->status) }}</span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('educator.edit-violation', ['id' => $violation->id]) }}" class="action-btn" title="Edit Violation">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('educator.view-violation', ['id' => $violation->id]) }}" class="action-btn" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize search timer variable for debouncing
    let searchTimer;
    
    /**
     * Filter table rows based on search query and severity filter
     * Matches student name and violation name against search query
     * Filters by selected severity level
     */
    function filterTable() {
        const searchQuery = $('#searchInput').val().toLowerCase();
        const severity = $('#severityFilter').val();
        
        // Show loading indicator
        if ($('.table-loading-indicator').length === 0) {
            $('.violation-table').append('<div class="table-loading-indicator text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Filtering results...</div>');
        }
        
        // Process each row in the table
        $('table tbody tr').each(function() {
            const $row = $(this);
            const studentName = $row.find('td:nth-child(3)').text().toLowerCase();
            const violationName = $row.find('td:nth-child(1)').text().toLowerCase();
            const rowSeverity = $row.find('td:nth-child(4)').text().trim();
            
            // Check if row matches both search query and severity filter
            const matchesSearch = searchQuery === '' || 
                                studentName.includes(searchQuery) || 
                                violationName.includes(searchQuery);
            const matchesSeverity = severity === '' || rowSeverity.includes(severity);
            
            // Show or hide row based on filter results
            $row.toggle(matchesSearch && matchesSeverity);
        });
        
        // Remove loading indicator
        setTimeout(function() {
            $('.table-loading-indicator').remove();
            
            // Show no results message if needed
            const visibleRows = $('table tbody tr:visible').length;
            if (visibleRows === 0) {
                if ($('.no-results-message').length === 0) {
                    $('table').after('<div class="no-results-message alert alert-info mt-3">No violations match your search criteria.</div>');
                }
            } else {
                $('.no-results-message').remove();
            }
        }, 300);
    }
    
    // Add debounced search input handler
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimer);
        $('.no-results-message').remove();
        searchTimer = setTimeout(filterTable, 300);
    });
    
    // Add severity filter change handler
    $('#severityFilter').on('change', filterTable);
    
    // Initialize tooltips for action icons
    $('[title]').tooltip();
});
</script>
@endsection