@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Edit User</span>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.update_user', $user->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="batch" class="form-label">Batch Year</label>
                            <input type="text" class="form-control @error('batch') is-invalid @enderror" 
                                   id="batch" name="batch" 
                                   value="{{ old('batch', $user->batch) }}" 
                                   pattern="[0-9]{4}" 
                                   title="Four digit year (e.g., 2025)">
                            @error('batch')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="group" class="form-label">Group</label>
                            <input type="text" class="form-control @error('group') is-invalid @enderror" 
                                   id="group" name="group" 
                                   value="{{ old('group', $user->group) }}" 
                                   maxlength="2" 
                                   title="Group code (max 2 characters)">
                            @error('group')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="student_number" class="form-label">Student Number</label>
                            <input type="text" class="form-control @error('student_number') is-invalid @enderror" 
                                   id="student_number" name="student_number" 
                                   value="{{ old('student_number', $user->student_number) }}" 
                                   maxlength="4" 
                                   title="Student number (max 4 digits)">
                            @error('student_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="training_code" class="form-label">Training Code</label>
                            <input type="text" class="form-control @error('training_code') is-invalid @enderror" 
                                   id="training_code" name="training_code" 
                                   value="{{ old('training_code', $user->training_code) }}" 
                                   maxlength="2" 
                                   title="Training code (max 2 characters)">
                            @error('training_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="student_id" class="form-label">Student ID</label>
                            <input type="text" class="form-control @error('student_id') is-invalid @enderror" 
                                   id="student_id" name="student_id" 
                                   value="{{ old('student_id', $user->student_id) }}" 
                                   pattern="[A-Za-z0-9]+" 
                                   title="Only letters and numbers are allowed" readonly>
                            @error('student_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="fname" class="form-label">First Name</label>
                            <input type="text" class="form-control @error('fname') is-invalid @enderror" id="fname" name="fname" value="{{ old('fname', $user->fname) }}" required>
                            @error('fname')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="lname" class="form-label">Last Name</label>
                            <input type="text" class="form-control @error('lname') is-invalid @enderror" id="lname" name="lname" value="{{ old('lname', $user->lname) }}" required>
                            @error('lname')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="middle_initial" class="form-label">Middle Initial</label>
                            <input type="text" class="form-control @error('middle_initial') is-invalid @enderror" id="middle_initial" name="middle_initial" value="{{ old('middle_initial', $user->middle_initial) }}" maxlength="1">
                            @error('middle_initial')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="suffix" class="form-label">Suffix</label>
                            <input type="text" class="form-control @error('suffix') is-invalid @enderror" id="suffix" name="suffix" value="{{ old('suffix', $user->suffix) }}">
                            @error('suffix')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password (leave blank to keep current password)</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ $userRole == $role->id ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
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
                                    <input class="form-check-input" type="radio" name="sex" id="male" value="Male" {{ $user->sex == 'Male' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="male">Male</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="sex" id="female" value="Female" {{ $user->sex == 'Female' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="female">Female</label>
                                </div>
                            </div>
                            @error('sex')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Update User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Add event listeners to update student ID when fields change
    document.addEventListener('DOMContentLoaded', function() {
        const batchField = document.getElementById('batch');
        const groupField = document.getElementById('group');
        const studentNumberField = document.getElementById('student_number');
        const trainingCodeField = document.getElementById('training_code');
        
        // Add event listeners to all relevant fields
        batchField.addEventListener('input', updateStudentId);
        groupField.addEventListener('input', updateStudentId);
        studentNumberField.addEventListener('input', updateStudentId);
        trainingCodeField.addEventListener('input', updateStudentId);
        
        // Initial call to set ID if fields have values
        updateStudentId();
    });
    
    function updateStudentId() {
        const batch = document.getElementById('batch').value;
        const group = document.getElementById('group').value;
        const studentNumber = document.getElementById('student_number').value.padStart(4, '0');
        const trainingCode = document.getElementById('training_code').value;
        
        if (batch && group && studentNumber && trainingCode) {
            const studentId = `${batch}${group}${studentNumber}${trainingCode}`;
            document.getElementById('student_id').value = studentId;
            console.log('Generated Student ID:', studentId);
        }
    }
</script>
@endsection
