@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Add New User</span>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.store_user') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="student_id" class="form-label">Student ID (if applicable)</label>
                            <input type="text" class="form-control @error('student_id') is-invalid @enderror" 
                                   id="student_id" name="student_id" 
                                   value="{{ old('student_id') }}" 
                                   pattern="[A-Za-z0-9]+" 
                                   title="Only letters and numbers are allowed">
                            @error('student_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="fname" class="form-label">First Name</label>
                            <input type="text" class="form-control @error('fname') is-invalid @enderror" id="fname" name="fname" value="{{ old('fname') }}" required>
                            @error('fname')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="lname" class="form-label">Last Name</label>
                            <input type="text" class="form-control @error('lname') is-invalid @enderror" id="lname" name="lname" value="{{ old('lname') }}" required>
                            @error('lname')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="middle_initial" class="form-label">Middle Initial</label>
                            <input type="text" class="form-control @error('middle_initial') is-invalid @enderror" id="middle_initial" name="middle_initial" value="{{ old('middle_initial') }}" maxlength="1">
                            @error('middle_initial')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="suffix" class="form-label">Suffix</label>
                            <input type="text" class="form-control @error('suffix') is-invalid @enderror" id="suffix" name="suffix" value="{{ old('suffix') }}">
                            @error('suffix')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                            @error('role')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sex</label>
                            <div class="d-flex">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="sex" id="male" value="Male" required>
                                    <label class="form-check-label" for="male">Male</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="sex" id="female" value="Female">
                                    <label class="form-check-label" for="female">Female</label>
                                </div>
                            </div>
                            @error('sex')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Register User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 