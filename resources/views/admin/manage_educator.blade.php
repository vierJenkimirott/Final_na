@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Manage Educators</h5>
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
                            <th>Department</th>
                            <th>Position</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($educators as $educator)
                            <tr>
                                <td>{{ $educator->educator_id ?? $educator->id }}</td>
                                <td>{{ $educator->name }}</td>
                                <td>{{ $educator->email }}</td>
                                <td>{{ $educator->department }}</td>
                                <td>{{ $educator->position }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.edit_user', $educator->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="if(confirm('Are you sure you want to delete this educator?')) {
                                                    document.getElementById('delete-form-{{ $educator->id }}').submit();
                                                }">
                                            Delete
                                        </button>
                                    </div>
                                    <form id="delete-form-{{ $educator->id }}" 
                                          action="{{ route('admin.delete_user', $educator->id) }}" 
                                          method="POST" 
                                          style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No educators found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $educators->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 