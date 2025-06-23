@extends('layouts.educator')

@section('css')
    <!-- External CSS Dependencies -->
    <link rel="stylesheet" href="{{ asset('css/educator/addViolator.css') }}">
@endsection

@section('content')
    <!-- Main Content Wrapper -->
    <div class="content-wrapper">
        <!-- Back Button -->

        <!-- Form Container -->
        <div class="form-container">
            <h2 class="form-title">Add New Violator</h2>
            <div id="termination-alert" class="alert alert-warning" style="display:none; margin-bottom: 15px; background-color: #fff3cd; border-color: #ffeeba; color: #856404; padding: 15px; border-radius: 4px;"></div>
            
            <!-- Violation Form -->
            <form id="violatorForm" class="violation-form" method="POST" action="{{ route('educator.add-violator') }}">
                @csrf
                <!-- Display validation errors if any -->
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <!-- Student Selection -->
                <div class="form-group">
                    <label for="student-select">Student</label>
                    <select class="form-field" id="student-select" name="student_id">
                        <option value="" selected disabled>Select Student</option>
                        @if(isset($students) && count($students) > 0)
                            @foreach($students as $student)
                                <option value="{{ $student->student_id }}">{{ $student->fname }} {{ $student->lname }} ({{ $student->student_id }})</option>
                            @endforeach
                        @else
                            <option value="" disabled>No students found</option>
                        @endif
                    </select>
                </div>

                <!-- Violation Date -->
                <div class="form-group">
                    <label for="violation-date">Violation Date</label>
                    <input type="date" class="form-field" id="violation-date" name="violation_date" />
                </div>

                <!-- Violation Category -->
                <div class="form-group">
                    <label for="violation-category">Category</label>
                    <select class="form-field" id="violation-category" name="category_id">
                        <option value="" selected disabled>Select Category</option>
                        @foreach($offenseCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Violation Type -->
                <div class="form-group">
                    <label for="violation-type">Type of Violation</label>
                    <select class="form-field" id="violation-type" name="violation_type_id">
                        <option value="" selected disabled>Select Violation Type</option>
                    </select>
                </div>

                <!-- Severity Selection -->
                <div class="form-group" id="severity-group">
                    <label for="severity">Severity</label>
                    <input type="text" class="form-field" id="severity" name="severity" readonly />
                </div>

                <!-- Infraction Count and Penalty (Auto-filled, Read-only) -->
                <div class="form-group">
                    <label for="infraction_count">Infraction Count</label>
                    <input type="text" id="infraction_count" name="infraction_count" class="form-field" readonly placeholder="Select a student">
                </div>
                <div class="form-group">
                    <label for="penalty">Penalty</label>
                    <input type="text" id="penalty" name="penalty" class="form-field" readonly>
                </div>

                <!-- Consequence Input -->
                <div class="form-group" id="consequence-group">
                    <label for="consequence">Consequence</label>
                    <select class="form-field" id="consequence-select" name="consequence_select">
                        <option value="" selected disabled>Select a recommended consequence</option>
                        <option value="No cellphone for 1 week">No cellphone for 1 week</option>
                        <option value="No going out for 1 month">No going out for 1 month</option>
                        <option value="Community Service">Community Service</option>
                        <option value="Kitchen team for 1 month">Kitchen team for 1 month</option>
                        <option value="No internet access for 3 days">No internet access for 3 days</option>
                        <option value="Extra assignment">Extra assignment</option>
                        <option value="other">Other (specify below)</option>
                    </select>
                    <input type="text" class="form-field" id="consequence-input" name="consequence" placeholder="Enter custom consequence" style="display:none; margin-top:8px;" />
                </div>

                <!-- Incident Details Section -->
                <div class="form-section">
                    <h3 class="section-title">Incident Details</h3>

                    <!-- Incident Date and Time -->
                    <div class="form-group">
                        <label for="incident-datetime">Date & Time of Incident</label>
                        <input type="datetime-local" class="form-field" id="incident-datetime" name="incident_datetime" />
                    </div>

                    <!-- Incident Place -->
                    <div class="form-group">
                        <label for="incident-place">Place of Incident</label>
                        <input type="text" class="form-field" id="incident-place" name="incident_place" placeholder="Enter location where incident occurred" />
                    </div>

                    <!-- Incident Details -->
                    <div class="form-group">
                        <label for="incident-details">Incident Details</label>
                        <textarea class="form-field" id="incident-details" name="incident_details" rows="4" placeholder="Describe what happened in detail..."></textarea>
                    </div>

                    <!-- Prepared By -->
                    <div class="form-group">
                        <label for="prepared-by">Prepared By</label>
                        <input type="text" class="form-field" id="prepared-by" name="prepared_by" value="{{ Auth::user()->name }}" readonly />
                    </div>
                </div>

                <!-- Hidden Status Field -->
                <input type="hidden" name="status" value="active" />

                <!-- Form Action Buttons -->
                <div class="form-actions">
                    <button type="button" class="cancel-btn">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-plus"></i> Add Violator
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // =============================================
    // Navigation & Form Event Handlers
    // =============================================
    document.querySelector('.back-btn').addEventListener('click', (e) => {
        e.preventDefault();
        window.history.back();
    });

    document.querySelector('.cancel-btn').addEventListener('click', (e) => {
        e.preventDefault();
        window.history.back();
    });

    document.getElementById('violation-category').addEventListener('change', function() {
        const categoryId = this.value;
        const violationTypeSelect = document.getElementById('violation-type');
        
        // Reset dependent fields
        violationTypeSelect.innerHTML = '<option value="" selected disabled>Select Violation Type</option>';
        document.getElementById('severity').value = '';
        updatePenaltyAndCheckTermination();
        
        if (categoryId) {
            fetch(`/api/violation-types/${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        data.forEach(violation => {
                            const option = document.createElement('option');
                            option.value = violation.id;
                            option.textContent = violation.violation_name;
                            option.dataset.severity = violation.severity;
                            violationTypeSelect.appendChild(option);
                        });
                    } else {
                        violationTypeSelect.innerHTML = '<option value="" disabled>No types found</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching violation types:', error);
                    violationTypeSelect.innerHTML = '<option value="" disabled>Error loading types</option>';
                });
        }
    });

    document.getElementById('violation-type').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.dataset.severity) {
            document.getElementById('severity').value = selectedOption.dataset.severity;
        } else {
            document.getElementById('severity').value = '';
        }
        updatePenaltyAndCheckTermination();
    });

    document.getElementById('student-select').addEventListener('change', async function() {
        const studentId = this.value;
        const infractionInput = document.getElementById('infraction_count');
        const terminationAlert = document.getElementById('termination-alert');
        const submitButton = document.querySelector('.submit-btn');
        
        console.log('Student selected:', studentId);
        
        // Always reset form to a default state on student change
        terminationAlert.style.display = 'none';
        submitButton.disabled = false;

        if (!studentId) {
            infractionInput.value = '';
            updatePenaltyAndCheckTermination(); // Clears penalty and re-evaluates
            return;
        }

        try {
            // Added timestamp to prevent browser caching
            const url = `/educator/check-infraction-count?student_id=${studentId}&t=${new Date().getTime()}`;
            console.log('Fetching from URL:', url);
            
            const response = await fetch(url);
            if (!response.ok) throw new Error('Network response failed');
            
            const data = await response.json();
            console.log('API Response:', data);
            
            // Check for existing termination first
            if (data.hasTermination) {
                console.log('Termination found for student:', studentId);
                infractionInput.value = 'N/A';
                terminationAlert.innerHTML = '<strong>Action Required:</strong> This student already has a termination/expulsion penalty. No further violations can be recorded.';
                terminationAlert.style.display = 'block';
                submitButton.disabled = true;
                return;
            }
            
            const count = data.count || 0;
            let infractionNum = count + 1;
            const suffix = { 1: 'st', 2: 'nd', 3: 'rd' }[infractionNum] || 'th';
            infractionInput.value = `${infractionNum}${suffix} Infraction`;
            
            updatePenaltyAndCheckTermination();

        } catch (error) {
            console.error('Error fetching infraction count:', error);
            infractionInput.value = 'Error';
            updatePenaltyAndCheckTermination();
        }
    });

    document.getElementById('consequence-select').addEventListener('change', function() {
        const input = document.getElementById('consequence-input');
        if (this.value === 'other') {
            input.style.display = 'block';
            input.required = true;
            input.value = '';
        } else {
            input.style.display = 'none';
            input.required = false;
            input.value = this.value;
        }
    });

    // =============================================
    // Helper Functions
    // =============================================
    function getPenalty(severity, infraction) {
        // Extract just the number from strings like "1st Infraction", "2nd Infraction", etc.
        const infractionMatch = infraction.toString().match(/(\d+)/);
        const infractionNum = infractionMatch ? parseInt(infractionMatch[1]) : parseInt(infraction);
        
        console.log('getPenalty called with:', { severity, infraction, infractionNum });
        
        if (isNaN(infractionNum)) {
            console.log('Infraction number is NaN, returning empty string');
            return '';
        }

        // Very High severity always results in Termination, regardless of infraction count
        if (severity.trim() === 'Very High') {
            return 'Termination of Contract';
        }

        // If it's the 4th infraction or higher, it's always Termination
        if (infractionNum >= 4) {
            return 'Termination of Contract';
        }

        // Penalty matrix based on severity and infraction count
        const penaltyMatrix = {
            'Low':    ['Verbal Warning', 'Written Warning', 'Probationary of Contract', 'Termination of Contract'],    // Termination at 4th
            'Medium': ['Written Warning', 'Probationary of Contract', 'Termination of Contract'],          // Termination at 3rd
            'High':   ['Probationary of Contract', 'Termination of Contract'],                // Termination at 2nd
            'Very High': ['Termination of Contract']                   // Immediate Termination
        };

        const penalties = penaltyMatrix[severity.trim()] || [];
        const penalty = penalties[infractionNum - 1];
        
        console.log('Penalty calculation:', { 
            severity: severity.trim(), 
            penalties, 
            infractionNum, 
            index: infractionNum - 1, 
            penalty 
        });
        
        return penalty || 'N/A';
    }

    function updatePenaltyAndCheckTermination() {
        const severity = document.getElementById('severity').value;
        const infraction = document.getElementById('infraction_count').value;
        const penaltyInput = document.getElementById('penalty');
        const infractionInput = document.getElementById('infraction_count');
        const submitButton = document.querySelector('.submit-btn');
        const terminationAlert = document.getElementById('termination-alert');

        let newPenalty = '';
        if (severity && infraction && infraction !== 'Error') {
            newPenalty = getPenalty(severity, infraction);
        }
        penaltyInput.value = newPenalty;

        // --- Termination Check ---
        const infractionNum = parseInt(infraction) || 0;
        const isTerminationPenalty = newPenalty === 'Termination of Contract';

        if (isTerminationPenalty) {
            let message = '';
            if (severity.trim() === 'Very High') {
                message = '<strong>Warning:</strong> This is a Very High severity violation that results in immediate termination of contract.';
            } else if (infractionNum >= 4) {
                message = '<strong>Warning:</strong> This is the 4th infraction, resulting in termination of contract.';
            } else {
                message = '<strong>Warning:</strong> Based on the severity and infraction count, this violation results in termination of contract.';
            }
            terminationAlert.innerHTML = message;
            terminationAlert.style.display = 'block';
            submitButton.disabled = false; // Allow submission for new termination
        } else {
            terminationAlert.style.display = 'none';
            submitButton.disabled = false;
        }
    }
</script>
@endpush