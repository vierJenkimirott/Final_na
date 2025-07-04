@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Manage Students</h5>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">Back to Dashboard</a>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Batch Tabs -->
            <ul class="nav nav-tabs mb-4" id="studentBatchTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-students" type="button" role="tab" aria-controls="all-students" aria-selected="true">
                        All Students
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="batch2025-tab" data-bs-toggle="tab" data-bs-target="#batch2025-students" type="button" role="tab" aria-controls="batch2025-students" aria-selected="false">
                        Batch 2025 ({{ count($batch2025Students) }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="batch2026-tab" data-bs-toggle="tab" data-bs-target="#batch2026-students" type="button" role="tab" aria-controls="batch2026-students" aria-selected="false">
                        Batch 2026 ({{ count($batch2026Students) }})
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="studentBatchTabContent">
                <!-- All Students Tab -->
                <div class="tab-pane fade show active" id="all-students" role="tabpanel" aria-labelledby="all-tab">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                    <tr>
                                        <td>{{ $student->student_id }}</td>
                                        <td>{{ $student->name }}</td>
                                        <td>{{ $student->email }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.edit_user', $student->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="if(confirm('Are you sure you want to delete this student?')) {
                                                            document.getElementById('delete-form-{{ $student->id }}').submit();
                                                        }">
                                                    Delete
                                                </button>
                                            </div>
                                            <form id="delete-form-{{ $student->id }}" 
                                                action="{{ route('admin.delete_user', $student->id) }}" 
                                                method="POST" 
                                                style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No students found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        <div class="d-flex justify-content-center mt-4">
                            {{ $students->links() }}
                        </div>
                    </div>
                </div>

                <!-- Batch 2025 Tab -->
                <div class="tab-pane fade" id="batch2025-students" role="tabpanel" aria-labelledby="batch2025-tab">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($batch2025Students as $student)
                                    <tr>
                                        <td>{{ $student->student_id }}</td>
                                        <td>{{ $student->name }}</td>
                                        <td>{{ $student->email }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.edit_user', $student->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="if(confirm('Are you sure you want to delete this student?')) {
                                                            document.getElementById('delete-form-2025-{{ $student->id }}').submit();
                                                        }">
                                                    Delete
                                                </button>
                                            </div>
                                            <form id="delete-form-2025-{{ $student->id }}" 
                                                action="{{ route('admin.delete_user', $student->id) }}" 
                                                method="POST" 
                                                style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No students found in Batch 2025</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Batch 2026 Tab -->
                <div class="tab-pane fade" id="batch2026-students" role="tabpanel" aria-labelledby="batch2026-tab">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($batch2026Students as $student)
                                    <tr>
                                        <td>{{ $student->student_id }}</td>
                                        <td>{{ $student->name }}</td>
                                        <td>{{ $student->email }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.edit_user', $student->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="if(confirm('Are you sure you want to delete this student?')) {
                                                            document.getElementById('delete-form-2026-{{ $student->id }}').submit();
                                                        }">
                                                    Delete
                                                </button>
                                            </div>
                                            <form id="delete-form-2026-{{ $student->id }}" 
                                                action="{{ route('admin.delete_user', $student->id) }}" 
                                                method="POST" 
                                                style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No students found in Batch 2026</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection