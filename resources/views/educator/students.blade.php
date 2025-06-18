@extends('layouts.educator')

@section('title', 'All Students')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/educator/behavior.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endsection

@section('content')
<div class="container-fluid px-1">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 1rem;">
        <h2 class="mb-0"><i class="fas fa-users me-2"></i>All Students</h2>
        <div class="d-flex align-items-center" style="gap: 1rem;">
            <input type="text" id="studentSearch" class="form-control" placeholder="Search by name..." style="max-width: 250px;">
            <div class="batch-filter" style="min-width:200px;">
                <label for="batchSelect" class="form-label me-2 mb-0 fw-semibold visually-hidden">Batch</label>
                <select class="form-select" id="batchSelect">
                    <option value="all" selected>All Classes</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('studentSearch');
            const batchSelect = document.getElementById('batchSelect');
            const rows = document.querySelectorAll('#studentsList tr');
            
            function filterTable() {
                const search = searchInput.value.toLowerCase();
                const batch = batchSelect.value;
                rows.forEach(row => {
                    const name = row.children[0].textContent.toLowerCase();
                    const studentId = row.children[1].textContent;
                    let matchesBatch = (batch === 'all') || (studentId.startsWith(batch));
                    let matchesSearch = name.includes(search);
                    row.style.display = (matchesBatch && matchesSearch) ? '' : 'none';
                });
            }
            searchInput.addEventListener('input', filterTable);
            batchSelect.addEventListener('change', filterTable);
        });
    </script>
    @endpush

    <div class="table-responsive">
        <table class="table table-hover" id="studentsTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Student ID</th>
                    <th>Sex</th>
                    <th>Violations</th>
<th>Action</th>
                </tr>
            </thead>
            <tbody id="studentsList">
                @foreach($students as $student)
                <tr>
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->student_id ?? 'ID-' . $student->id }}</td>
                    <td>{{ $student->sex ?? 'Not specified' }}</td>
                    <td>
                        @php
                            $studentIdForViolations = $student->student_id ?? $student->id;
                            $violationCount = \App\Models\Violation::where('student_id', $studentIdForViolations)->count();
                        @endphp
                        <span class="badge {{ $violationCount > 0 ? 'bg-danger' : 'bg-success' }}">
                            {{ $violationCount }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ url('/educator/student-profile/' . ($student->student_id ?? $student->id)) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-user"></i> Violation History
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
