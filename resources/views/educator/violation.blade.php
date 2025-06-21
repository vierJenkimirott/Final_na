@extends('layouts.educator')

@section('title', 'Manage Student Violations')

@section('css')
    <!-- External CSS and Script Dependencies -->
    <link rel="stylesheet" href="{{ asset('css/educator/violation.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endsection

@section('content')
    <div class="container-fluid px-1">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-bold">Manage Student Violations</h2>
        </div>

        <main>
            <!-- Action Buttons Section -->
        

            <!-- Warning Statistics Section -->
            @php
                // Get unique students with Verbal Warning penalty
                $verbalWarningStudents = DB::table('violations')
                    ->join('users', 'violations.student_id', '=', 'users.student_id')
                    ->select('users.fname', 'users.lname', 'users.student_id')
                    ->where('violations.penalty', 'VW')
                    ->where('violations.status', 'active')
                    ->groupBy('users.student_id', 'users.fname', 'users.lname')
                    ->get();
                $verbalWarningCount = count($verbalWarningStudents);
                
                // Get unique students with Written Warning penalty
                $writtenWarningStudents = DB::table('violations')
                    ->join('users', 'violations.student_id', '=', 'users.student_id')
                    ->select('users.fname', 'users.lname', 'users.student_id')
                    ->where('violations.penalty', 'WW')
                    ->where('violations.status', 'active')
                    ->groupBy('users.student_id', 'users.fname', 'users.lname')
                    ->get();
                $writtenWarningCount = count($writtenWarningStudents);
                
                // Get unique students with Probation penalty
                $probationStudents = DB::table('violations')
                    ->join('users', 'violations.student_id', '=', 'users.student_id')
                    ->select('users.fname', 'users.lname', 'users.student_id')
                    ->where('violations.penalty', 'Pro')
                    ->where('violations.status', 'active')
                    ->groupBy('users.student_id', 'users.fname', 'users.lname')
                    ->get();
                $probationCount = count($probationStudents);
                
                // Get unique students with Expulsion penalty
                $expulsionStudents = DB::table('violations')
                    ->join('users', 'violations.student_id', '=', 'users.student_id')
                    ->select('users.fname', 'users.lname', 'users.student_id')
                    ->where('violations.penalty', 'Exp')
                    ->where('violations.status', 'active')
                    ->groupBy('users.student_id', 'users.fname', 'users.lname')
                    ->get();
                $expulsionCount = count($expulsionStudents);
            @endphp
            <section class="warning-section" style="padding: 20px; display: flex; justify-content: space-between; flex-wrap: nowrap;">
                <!-- Penalty Statistics Boxes -->
                <a href="{{ route('educator.students-by-penalty', ['penalty' => 'VW']) }}" class="warning-box tall" style="flex: 1; margin: 0 8px; text-align: center;">
                    <div class="penalty-header" style="display: flex; flex-direction: column; align-items: center;">
                        <span>Verbal Warning<br>Student</span>
                        <span class="count-badge" style="background: none; width: auto; height: auto; margin-top: 10px; font-size: 36px; color: #333;">{{ $verbalWarningCount }}</span>
                    </div>
                </a>
                
                <a href="{{ route('educator.students-by-penalty', ['penalty' => 'WW']) }}" class="warning-box tall" style="flex: 1; margin: 0 8px; text-align: center;">
                    <div class="penalty-header" style="display: flex; flex-direction: column; align-items: center;">
                        <span>Written Warning<br>Student</span>
                        <span class="count-badge" style="background: none; width: auto; height: auto; margin-top: 10px; font-size: 36px; color: #333;">{{ $writtenWarningCount }}</span>
                    </div>
                </a>
                
                <a href="{{ route('educator.students-by-penalty', ['penalty' => 'Pro']) }}" class="warning-box tall" style="flex: 1; margin: 0 8px; text-align: center;">
                    <div class="penalty-header" style="display: flex; flex-direction: column; align-items: center;">
                        <span>Probation Student</span>
                        <span class="count-badge" style="background: none; width: auto; height: auto; margin-top: 10px; font-size: 36px; color: #333;">{{ $probationCount }}</span>
                    </div>
                </a>
                
                <a href="{{ route('educator.students-by-penalty', ['penalty' => 'Exp']) }}" class="warning-box tall" style="flex: 1; margin: 0 8px; text-align: center;">
                    <div class="penalty-header" style="display: flex; flex-direction: column; align-items: center;">
                        <span>Expulsion Student</span>
                        <span class="count-badge" style="background: none; width: auto; height: auto; margin-top: 10px; font-size: 36px; color: #333;">{{ $expulsionCount }}</span>
                    </div>
                </a>
            </section>

            <div class="top-buttons">
                <a href="{{route('educator.add-violator-form')}}" class="btn">
                    <i class="fas fa-user-plus me-1"></i> Add Violator
                </a>
                <!-- <a href="{{route('educator.add-violation')}}" class="btn">
                    <i class="fas fa-exclamation-triangle me-1"></i> Add Violation
                </a> -->
            </div>

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
                                <th>Penalty</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="violation-list">
                            @foreach ($violations as $violation)
                                <tr>
                                    <td>{{ $violation->violationType->violation_name ?? 'N/A' }}</td>
                                    <td>{{ $violation->violationType->offenseCategory->category_name ?? 'N/A' }}</td>
                                    <td>{{ $violation->student ? $violation->student->fname.' '.$violation->student->lname : 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ strtolower($violation->severity) === 'low' ? 'warning' : (strtolower($violation->severity) === 'medium' ? 'info' : (strtolower($violation->severity) === 'high' ? 'danger' : 'dark')) }}">{{ $violation->severity }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $penaltyLabels = [
                                                'VW' => ['label' => 'Verbal Warning', 'class' => 'bg-info text-dark'],
                                                'WW' => ['label' => 'Written Warning', 'class' => 'bg-primary'],
                                                'Pro' => ['label' => 'Probation', 'class' => 'bg-warning text-dark'],
                                                'Exp' => ['label' => 'Expulsion', 'class' => 'bg-danger']
                                            ];
                                            $penaltyInfo = $penaltyLabels[$violation->penalty] ?? ['label' => $violation->penalty, 'class' => 'bg-secondary'];
                                        @endphp
                                        <span class="badge {{ $penaltyInfo['class'] }}">{{ $penaltyInfo['label'] }}</span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($violation->violation_date)->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge {{ $violation->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($violation->status) }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('educator.edit-violation', ['id' => $violation->id]) }}" class="action-btn" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="{{ route('educator.view-violation', ['id' => $violation->id]) }}" class="action-btn" title="View"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
            {{ $violations->links() }}
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