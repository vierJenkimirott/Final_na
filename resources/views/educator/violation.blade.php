@extends('layouts.educator')

@section('title', 'Manage Student Violations')

@section('css')
    <!-- External CSS and Script Dependencies -->
    <link rel="stylesheet" href="{{ asset('css/educator/violation.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    .custom-pagination {
        display: flex;
        justify-content: center;
        margin-top: 1.5rem;
    }
    .custom-pagination ul {
        list-style: none;
        padding: 0;
        display: flex;
        align-items: center;
    }
    .custom-pagination li {
        margin: 0 5px;
    }
    .custom-pagination a, .custom-pagination span {
        color: #0d6efd;
        text-decoration: none;
        padding: 8px 15px;
        display: block;
        border-radius: 5px;
        font-weight: 500;
    }
    .custom-pagination a:hover {
        background-color: #f0f0f0;
        text-decoration: none;
    }
    .custom-pagination .active span {
        font-weight: 700;
        color: #333;
    }
    .custom-pagination .disabled span {
        color: #6c757d;
        pointer-events: none;
    }
    </style>
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
    $batch = request('batch', 'all');
    $batchFilter = function($query) use ($batch) {
        if ($batch !== 'all') {
            $query->where('users.student_id', 'like', $batch . '%');
        }
    };
    // Get unique students with Verbal Warning penalty
    $verbalWarningStudents = DB::table('violations')
        ->join('users', 'violations.student_id', '=', 'users.student_id')
        ->select('users.fname', 'users.lname', 'users.student_id')
        ->where('violations.penalty', 'VW')
        ->where('violations.status', 'active')
        ->when($batch !== 'all', function($query) use ($batch) {
            $query->where('users.student_id', 'like', $batch . '%');
        })
        ->groupBy('users.student_id', 'users.fname', 'users.lname')
        ->get();
    $verbalWarningCount = count($verbalWarningStudents);
    // Written Warning
    $writtenWarningStudents = DB::table('violations')
        ->join('users', 'violations.student_id', '=', 'users.student_id')
        ->select('users.fname', 'users.lname', 'users.student_id')
        ->where('violations.penalty', 'WW')
        ->where('violations.status', 'active')
        ->when($batch !== 'all', function($query) use ($batch) {
            $query->where('users.student_id', 'like', $batch . '%');
        })
        ->groupBy('users.student_id', 'users.fname', 'users.lname')
        ->get();
    $writtenWarningCount = count($writtenWarningStudents);
    // Probation
    $probationStudents = DB::table('violations')
        ->join('users', 'violations.student_id', '=', 'users.student_id')
        ->select('users.fname', 'users.lname', 'users.student_id')
        ->where('violations.penalty', 'Pro')
        ->where('violations.status', 'active')
        ->when($batch !== 'all', function($query) use ($batch) {
            $query->where('users.student_id', 'like', $batch . '%');
        })
        ->groupBy('users.student_id', 'users.fname', 'users.lname')
        ->get();
    $probationCount = count($probationStudents);
    // Expulsion
    $expulsionStudents = DB::table('violations')
        ->join('users', 'violations.student_id', '=', 'users.student_id')
        ->select('users.fname', 'users.lname', 'users.student_id')
        ->where('violations.penalty', 'Exp')
        ->where('violations.status', 'active')
        ->when($batch !== 'all', function($query) use ($batch) {
            $query->where('users.student_id', 'like', $batch . '%');
        })
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

            <div class="top-buttons d-flex align-items-center mb-3">
    <a href="{{route('educator.add-violator-form')}}" class="btn me-3">
        <i class="fas fa-user-plus me-1"></i> Add Violator
    </a>
</div>

            

            <!-- Violations Table Section -->
            <section class="violation-table">
                <!-- Search and Filter Controls -->
                <div class="search-bar d-flex align-items-center" style="gap: 1rem;">
    <input type="text" id="searchInput" placeholder="Search by student or violation..." class="form-control" value="{{ request('search', '') }}" />
    <select id="severityFilter" class="form-select" style="max-width: 180px;">
        <option value="">All Severity</option>
        <option value="Low">Low</option>
        <option value="Medium">Medium</option>
        <option value="High">High</option>
        <option value="Very High">Very High</option>
    </select>
    <select class="form-select" id="batchSelect" style="max-width: 200px;">
        <option value="all" selected>Loading classes...</option>
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
                        <tbody id="violation-table-body">
                            @forelse ($violations as $violation)
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
                                                'VW' => ['label' => 'VW', 'class' => 'bg-info text-dark'],
                                                'WW' => ['label' => 'WW', 'class' => 'bg-primary'],
                                                'P'  => ['label' => 'P',  'class' => 'bg-warning text-dark'],
                                                'T'  => ['label' => 'T',  'class' => 'bg-danger']
                                            ];
                                            // Map old codes to new abbreviations for display
                                            $penaltyMap = [
                                                'Pro' => 'P',
                                                'Exp' => 'T',
                                                'VW'  => 'VW',
                                                'WW'  => 'WW',
                                                'P'   => 'P',
                                                'T'   => 'T',
                                            ];
                                            $penaltyKey = $penaltyMap[$violation->penalty] ?? $violation->penalty;
                                            $penaltyInfo = $penaltyLabels[$penaltyKey] ?? ['label' => $penaltyKey, 'class' => 'bg-secondary'];
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
                            @empty
                                <tr><td colspan="8" class="text-center">No violations found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Clean, Text-based Pagination --}}
                @if ($violations->hasPages())
                <nav class="custom-pagination">
                    <ul>
                        {{-- Previous Page Link --}}
                        @if ($violations->onFirstPage())
                            <li class="disabled"><span>&laquo; Previous</span></li>
                        @else
                            <li><a href="{{ $violations->previousPageUrl() }}" rel="prev">&laquo; Previous</a></li>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($violations->links()->elements as $element)
                            {{-- "Three Dots" Separator --}}
                            @if (is_string($element))
                                <li class="disabled"><span>{{ $element }}</span></li>
                            @endif

                            {{-- Array Of Links --}}
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $violations->currentPage())
                                        <li class="active"><span>{{ $page }}</span></li>
                                    @else
                                        <li><a href="{{ $url }}">{{ $page }}</a></li>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($violations->hasMorePages())
                            <li><a href="{{ $violations->nextPageUrl() }}" rel="next">Next &raquo;</a></li>
                        @else
                            <li class="disabled"><span>Next &raquo;</span></li>
                        @endif
                    </ul>
                </nav>
                @endif
            </section>
        </main>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadAvailableBatches();
        // Set batch filter from query if present
        const urlParams = new URLSearchParams(window.location.search);
        const selectedBatch = urlParams.get('batch') || 'all';
        const selectedSeverity = urlParams.get('severity') || '';
        const searchInput = document.getElementById('searchInput');
        const severityFilter = document.getElementById('severityFilter');
        const batchSelect = document.getElementById('batchSelect');

        // Set severity filter from query if present
        if (selectedSeverity) severityFilter.value = selectedSeverity;

        batchSelect.addEventListener('change', function() {
            const batch = this.value;
            const params = new URLSearchParams(window.location.search);
            if (batch === 'all') {
                params.delete('batch');
            } else {
                params.set('batch', batch);
            }
            window.location.search = params.toString();
        });
        severityFilter.addEventListener('change', function() {
            const severity = this.value;
            const params = new URLSearchParams(window.location.search);
            if (severity === '') {
                params.delete('severity');
            } else {
                params.set('severity', severity);
            }
            // Also preserve batch and search
            const batch = batchSelect.value;
            if (batch === 'all') {
                params.delete('batch');
            } else {
                params.set('batch', batch);
            }
            const search = searchInput.value.trim();
            if (search) {
                params.set('search', search);
            } else {
                params.delete('search');
            }
            window.location.search = params.toString();
        });
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const search = this.value.trim();
                const params = new URLSearchParams(window.location.search);
                if (search) {
                    params.set('search', search);
                } else {
                    params.delete('search');
                }
                // Also preserve severity and batch
                const severity = severityFilter.value;
                if (severity === '') {
                    params.delete('severity');
                } else {
                    params.set('severity', severity);
                }
                const batch = batchSelect.value;
                if (batch === 'all') {
                    params.delete('batch');
                } else {
                    params.set('batch', batch);
                }
                window.location.search = params.toString();
            }
        });

        function loadAvailableBatches() {
            fetch('/educator/available-batches')
                .then(response => response.json())
                .then(data => {
                    batchSelect.innerHTML = '';
                    if (data.success) {
                        data.batches.forEach(batch => {
                            const option = document.createElement('option');
                            option.value = batch.value;
                            option.textContent = `${batch.label}`;
                            if (batch.value === selectedBatch) {
                                option.selected = true;
                            }
                            batchSelect.appendChild(option);
                        });
                    } else {
                        batchSelect.innerHTML = '<option value="all">All Classes</option>';
                    }
                })
                .catch(() => {
                    batchSelect.innerHTML = '<option value="all">All Classes</option>';
                });
        }
    });
</script>
@endsection