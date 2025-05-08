@extends('layouts.educator')

@section('css')
    <!-- External CSS and Script Dependencies -->
    <link rel="stylesheet" href="{{ asset('css/violation.css') }}">
    <script src="https://kit.fontawesome.com/4e45d9ad8d.js" crossorigin="anonymous"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom Styles -->
    <style>
        /* Warning Box Styling */
        .warning-box {
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .warning-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
    <!-- Page Header -->
    <h2>Violation</h2>

    <main>
        <!-- Action Buttons Section -->
        <div class="top-buttons">
            <a href="{{route('educator.add-violator-form')}}" class="btn">+ Add Violator</a>
            <a href="{{route('educator.add-violation')}}" class="btn">+ Add Violation</a>
            <a href="{{route('educator.student-violations')}}" class="btn">Student Violation History</a>
        </div>

        <!-- Warning Statistics Section -->
        <section class="warning-section">
            <!-- Left Column - Warning and Written Warning -->
            <div class="column">
                <a href="{{ route('educator.students-by-penalty', ['penalty' => 'W']) }}" class="warning-box tall" onclick="window.location.href='{{ route('educator.students-by-penalty', ['penalty' => 'W']) }}'; return false;">
                    Warning Student<br>
                    <span>{{ DB::table('violations')->where('penalty', 'W')->where('status', 'active')->count() }}</span>
                </a>
                <a href="{{ route('educator.students-by-penalty', ['penalty' => 'WW']) }}" class="warning-box tall" onclick="window.location.href='{{ route('educator.students-by-penalty', ['penalty' => 'WW']) }}'; return false;">
                    Written Warning Student<br>
                    <span>{{ DB::table('violations')->where('penalty', 'WW')->where('status', 'active')->count() }}</span>
                </a>
            </div>

            <!-- Center Column - Verbal Warning -->
            <div class="column center">
                <a href="{{ route('educator.students-by-penalty', ['penalty' => 'VW']) }}" class="warning-box wide" onclick="window.location.href='{{ route('educator.students-by-penalty', ['penalty' => 'VW']) }}'; return false;">
                    Verbal Warning Student<br>
                    <span>{{ DB::table('violations')->where('penalty', 'VW')->where('status', 'active')->count() }}</span>
                </a>
            </div>

            <!-- Right Column - Probation and Expulsion -->
            <div class="column">
                <a href="{{ route('educator.students-by-penalty', ['penalty' => 'Pro']) }}" class="warning-box tall" onclick="window.location.href='{{ route('educator.students-by-penalty', ['penalty' => 'Pro']) }}'; return false;">
                    Probation Student<br>
                    <span>{{ DB::table('violations')->where('penalty', 'Pro')->where('status', 'active')->count() }}</span>
                </a>
                <a href="{{ route('educator.students-by-penalty', ['penalty' => 'Exp']) }}" class="warning-box tall" onclick="window.location.href='{{ route('educator.students-by-penalty', ['penalty' => 'Exp']) }}'; return false;">
                    Expulsion Student<br>
                    <span>{{ DB::table('violations')->where('penalty', 'Exp')->where('status', 'active')->count() }}</span>
                </a>
            </div>
        </section>

        <!-- Violations Table Section -->
        <section class="violation-table">
            <!-- Search and Filter Controls -->
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search violation..." />
                <select id="severityFilter">
                    <option value="">All Severity</option>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                    <option value="Very High">Very High</option>
                </select>
            </div>

            <!-- Violations Data Table -->
            <table>
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
                <tbody>
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
                            <td class="{{ strtolower($violation->severity) }}">{{ $violation->severity }}</td>
                            <td>{{ $violation->offense }}</td>
                            <td>
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
                            </td>
                            <td>{{ \Carbon\Carbon::parse($violation->violation_date)->format('M d, Y') }}</td>
                            <td class="{{ strtolower($violation->status) }}">{{ ucfirst($violation->status) }}</td>
                            <td>
                                <a href="{{ route('educator.edit-violation', ['id' => $violation->id]) }}" class="action-icon" title="Edit Violation">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('educator.view-violation', ['id' => $violation->id]) }}" class="action-icon" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </main>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize search timer variable
    let searchTimer;
    
    /**
     * Filter table rows based on search query and severity filter
     * Matches student name and violation name against search query
     * Filters by selected severity level
     */
    function filterTable() {
        const searchQuery = $('#searchInput').val().toLowerCase();
        const severity = $('#severityFilter').val();
        
        $('table tbody tr').each(function() {
            const $row = $(this);
            const studentName = $row.find('td:nth-child(3)').text().toLowerCase();
            const violationName = $row.find('td:nth-child(1)').text().toLowerCase();
            const rowSeverity = $row.find('td:nth-child(4)').text();
            
            const matchesSearch = searchQuery === '' || 
                                studentName.includes(searchQuery) || 
                                violationName.includes(searchQuery);
            const matchesSeverity = severity === '' || rowSeverity === severity;
            
            $row.toggle(matchesSearch && matchesSeverity);
        });
    }
    
    // Add debounced search input handler
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(filterTable, 300);
    });
    
    // Add severity filter change handler
    $('#severityFilter').on('change', filterTable);
});
</script>
@endsection