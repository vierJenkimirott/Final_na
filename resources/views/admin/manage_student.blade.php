@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="card">
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
                                <td colspan="4" class="text-center">No students found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $students->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 