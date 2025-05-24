@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Admin Dashboard</h1>
        <a href="{{ route('admin.create_user') }}" class="btn btn-primary">Add User</a>
    </div>

    <div class="row mb-4">
        <!-- Statistics Cards -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Students</h5>
                    <p class="display-4 text-primary">{{ $totalStudents }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Educators</h5>
                    <p class="display-4 text-success">{{ $totalEducators }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Actions -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Quick Actions</h5>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.manage_student') }}" class="btn btn-primary">
                            Manage Students
                        </a>
                        <a href="{{ route('admin.manage_educator') }}" class="btn btn-success">
                            Manage Educators
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 