@extends('layouts.educator')

@section('title', 'Active Violation Cases')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/educator/behavior.css') }}">
@endsection

@section('content')
<div class="container-fluid px-1">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Active Violation Cases</h2>
        <a href="{{ route('educator.behavior') }}" class="btn btn-secondary">Back to Monitoring</a>
    </div>
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-3 mb-3 align-items-end" id="filterForm">
                <div class="col-md-4">
                    <label for="searchName" class="form-label">Search by Name</label>
                    <input type="text" class="form-control" id="searchName" name="name" value="{{ $currentName ?? '' }}" placeholder="Enter student name">
                </div>
                <div class="col-md-4">
                    <label for="batchFilter" class="form-label">Class</label>
                    <select class="form-select" id="batchFilter" name="batch">
                        <option value="all" {{ ($currentBatch ?? 'all') == 'all' ? 'selected' : '' }}>All Classes</option>
                        @foreach($batches as $batch)
                            <option value="{{ $batch }}" {{ ($currentBatch ?? 'all') == $batch ? 'selected' : '' }}>{{ $batch }}</option>
                        @endforeach
                    </select>
                </div>
                
            </form>
<script>
    // Auto-submit on batch change
    document.getElementById('batchFilter').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
    // Debounce for name input
    let debounceTimer;
    document.getElementById('searchName').addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            document.getElementById('filterForm').submit();
        }, 400);
    });
</script>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Violation Type</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($violations as $violation)
                        <tr>
                            <td>{{ $violation->student ? $violation->student->name : 'Unknown' }}</td>
                            <td>{{ $violation->violationType ? $violation->violationType->violation_name : 'Unknown' }}</td>
                            <td>{{ \Carbon\Carbon::parse($violation->violation_date)->format('M d, Y') }}</td>
                            <td><span class="badge bg-danger">ACTIVE</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No active violation cases found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
