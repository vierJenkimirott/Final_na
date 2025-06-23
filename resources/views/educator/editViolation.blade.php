@extends('layouts.educator')

@section('css')
<link rel="stylesheet" href="{{asset('css/educator/edit-violation.css')}}">
@endsection

@section('content')
    <!-- Main Container -->
    <div class="content-wrapper px-1">
        <div class="form-container">
            <h2>Edit Violation</h2>
        
            <div id="termination-alert" class="alert alert-warning" style="display:none; margin-bottom: 15px; background-color: #fff3cd; border-color: #ffeeba; color: #856404; padding: 15px; border-radius: 4px;"></div>
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <!-- Show validation errors if any -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <!-- Edit Violation Form -->
            <form id="violationForm" action="{{ route('educator.update-violation', ['id' => $violation->id]) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <!-- Student Selection (disabled for display, hidden for submission) -->
                <div class="form-group">
                    <label for="student_id">Student</label>
                    <select name="student_id" id="student_id" class="form-control" disabled>
                        <option value="">Select Student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->student_id }}" {{ $violation->student_id == $student->student_id ? 'selected' : '' }}>
                                {{ $student->lname }}, {{ $student->fname }} ({{ $student->student_id }})
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="student_id" value="{{ $violation->student_id }}">
                </div>
                
                <!-- Violation Date (readonly) -->
                <div class="form-group">
                    <label for="violation_date">Violation Date</label>
                    <input type="date" name="violation_date" id="violation_date" class="form-control" value="{{ \Carbon\Carbon::parse($violation->violation_date)->format('Y-m-d') }}" readonly>
                </div>
                
                <!-- Category Selection (disabled) -->
                <div class="form-group">
                    <label for="offense_category_id">Category</label>
                    <select name="offense_category_id" id="offense_category_id" class="form-control" disabled>
                        <option value="">Select Category</option>
                        @foreach($offenseCategories as $category)
                            <option value="{{ $category->id }}" {{ $violation->violationType->offense_category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Violation Type Selection (disabled for display, hidden for submission) -->
                <div class="form-group">
                    <label for="violation_type_id">Violation Type</label>
                    <select name="violation_type_id" id="violation_type_id" class="form-control" disabled>
                        <option value="">Select Violation Type</option>
                        <option value="{{ $violation->violation_type_id }}" selected>{{ $violation->violationType->violation_name ?? '' }}</option>
                    </select>
                    <input type="hidden" name="violation_type_id" value="{{ $violation->violation_type_id }}">
                </div>
                
                <!-- Severity (readonly) -->
                <div class="form-group">
                    <label for="severity">Severity</label>
                    <input type="text" id="severity" name="severity" class="form-control" value="{{ $violation->severity }}" readonly>
                </div>
                
                <!-- Infraction Count (readonly input) -->
                <div class="form-group">
                    <label for="infraction_count">Infraction Count</label>
                    <input type="text" id="infraction_count" name="infraction_count" class="form-control" value="{{ $violation->infraction_count ? $violation->infraction_count . 'th Infraction' : '' }}" readonly>
                </div>
                    
                <!-- Penalty (readonly input) -->
                <div class="form-group">
                    <label for="penalty">Penalty</label>
                    <input type="text" id="penalty" name="penalty" class="form-control" value="{{ $violation->penalty }}" readonly>
                </div>
                
                <!-- Status Selection (editable) -->
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="active" {{ $violation->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="resolved" {{ $violation->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    </select>
                </div>

                <!-- Consequence (readonly) -->
                <div class="form-group" id="consequence-group">
                    <label for="consequence">Consequence</label>
                    <textarea name="consequence" id="consequence" class="form-control" rows="3" readonly>{{ $violation->consequence }}</textarea>
                </div>

                <!-- Incident Details Section (readonly) -->
                <div class="incident-details-section">
                    <h3 class="section-title">Incident Details</h3>

                    <!-- Incident Date and Time -->
                    <div class="form-group">
                        <label for="incident_datetime">Date & Time of Incident</label>
                        <input type="datetime-local" name="incident_datetime" id="incident_datetime" class="form-control" value="{{ $violation->incident_datetime ? \Carbon\Carbon::parse($violation->incident_datetime)->format('Y-m-d\TH:i') : '' }}" readonly>
                    </div>

                    <!-- Incident Place -->
                    <div class="form-group">
                        <label for="incident_place">Place of Incident</label>
                        <input type="text" name="incident_place" id="incident_place" class="form-control" value="{{ $violation->incident_place }}" readonly>
                    </div>

                    <!-- Incident Details -->
                    <div class="form-group">
                        <label for="incident_details">Incident Details</label>
                        <textarea name="incident_details" id="incident_details" class="form-control" rows="4" readonly>{{ $violation->incident_details }}</textarea>
                    </div>

                    <!-- Prepared By -->
                    <div class="form-group">
                        <label for="prepared_by">Prepared By</label>
                        <input type="text" name="prepared_by" id="prepared_by" class="form-control" value="{{ $violation->prepared_by ?: Auth::user()->name }}" readonly>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="form-actions">
                <button type="button" class="cancel-btn">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="submit" class="submit-btn">
                    <i class="fas fa-lock me-2"></i>Update Violation
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelector('.cancel-btn').addEventListener('click', () => window.location.href = "{{ route('educator.violation') }}");
</script>
@endpush 
    
