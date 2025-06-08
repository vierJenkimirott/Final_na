@extends('layouts.educator')

@section('css')
<link rel="stylesheet" href="{{asset('css/educator/edit-violation.css')}}">
@endsection

@section('content')
    <!-- Main Container -->
    <div class="content-wrapper">
        <div class="form-container">
            <h2>Edit Violation</h2>
            
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
            
            <!-- Edit Violation Form -->
            <form action="{{ route('educator.update-violation', ['id' => $violation->id]) }}" method="POST" class="violation-form">
                @csrf
                @method('PUT')
                
                <!-- Student Selection -->
                <div class="form-group">
                    <label for="student_id">Student <span class="text-danger"></span></label>
                    <select name="student_id" id="student_id" class="form-control @error('student_id') is-invalid @enderror" required>
                        <option value="">Select Student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->student_id }}" {{ $violation->student_id == $student->student_id ? 'selected' : '' }}>
                                {{ $student->lname }}, {{ $student->fname }} ({{ $student->student_id }})
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Violation Date -->
                <div class="form-group">
                    <label for="violation_date">Violation Date <span class="text-danger"></span></label>
                    <input type="date" name="violation_date" id="violation_date" class="form-control @error('violation_date') is-invalid @enderror" 
                           value="{{ \Carbon\Carbon::parse($violation->violation_date)->format('Y-m-d') }}" required>
                    @error('violation_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Category Selection -->
                <div class="form-group">
                    <label for="offense_category_id">Category <span class="text-danger"></span></label>
                    <select name="offense_category_id" id="offense_category_id" class="form-control @error('offense_category_id') is-invalid @enderror" required>
                        <option value="">Select Category</option>
                        @foreach($offenseCategories as $category)
                            <option value="{{ $category->id }}" {{ $violation->offenseCategory->id == $category->id ? 'selected' : '' }}>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('offense_category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Violation Type Selection -->
                <div class="form-group">
                    <label for="violation_type_id">Violation Type <span class="text-danger"></span></label>
                    <select name="violation_type_id" id="violation_type_id" class="form-control @error('violation_type_id') is-invalid @enderror" required>
                        <option value="">Select Violation Type</option>
                        @php
                            $violationTypesWithSeverity = DB::table('violation_types')
                                ->select('violation_types.id', 'violation_types.violation_name', 'severities.severity_name')
                                ->join('severities', 'violation_types.severity_id', '=', 'severities.id')
                                ->where('violation_types.offense_category_id', $violation->offenseCategory->id)
                                ->get();
                        @endphp
                        
                        @foreach($violationTypesWithSeverity as $type)
                            <option value="{{ $type->id }}" 
                                data-severity="{{ $type->severity_name }}"
                                {{ $violation->violation_type_id == $type->id ? 'selected' : '' }}>
                                {{ $type->violation_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('violation_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Severity Selection -->
                <div class="form-group">
                    <label for="severity">Severity <span class="text-danger"></span></label>
                    <select name="severity" id="severity" class="form-control @error('severity') is-invalid @enderror" required>
                        <option value="">Select Severity</option>
                        <option value="Low" {{ $violation->severity == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Medium" {{ $violation->severity == 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="High" {{ $violation->severity == 'High' ? 'selected' : '' }}>High</option>
                        <option value="Very High" {{ $violation->severity == 'Very High' ? 'selected' : '' }}>Very High</option>
                    </select>
                    @error('severity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Offense Count Selection -->
                <div class="form-group">
                    <label for="offense_count">Offense Count <span class="text-danger"></span></label>
                    <select name="offense_count" id="offense_count" class="form-control" required>
                        <option value="">Select Offense Count</option>
                        <option value="1st">1st Offense</option>
                        <option value="2nd">2nd Offense</option>
                        <option value="3rd">3rd Offense</option>
                        <option value="4th">4th Offense</option>
                    </select>
                </div>
                
                <!-- Penalty Selection -->
                <div class="form-group">
                    <label for="penalty">Penalty <span class="text-danger"></span></label>
                    <select name="penalty" id="penalty" class="form-control @error('penalty') is-invalid @enderror" required>
                        <option value="">Select Penalty</option>
                        <option value="VW" {{ $violation->penalty == 'VW' ? 'selected' : '' }}>Verbal Warning</option>
                        <option value="WW" {{ $violation->penalty == 'WW' ? 'selected' : '' }}>Written Warning</option>
                        <option value="Pro" {{ $violation->penalty == 'Pro' ? 'selected' : '' }}>Probation</option>
                        <option value="Exp" {{ $violation->penalty == 'Exp' ? 'selected' : '' }}>Expulsion</option>
                    </select>
                    @error('penalty')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Status Selection -->
                <div class="form-group">
                    <label for="status">Status <span class="text-danger"></span></label>
                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="active" {{ $violation->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="resolved" {{ $violation->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Consequence Description -->
                <div class="form-group" id="consequence-group">
                    <label for="consequence">Consequence <span class="text-danger"></span></label>
                    <textarea name="consequence" id="consequence" class="form-control @error('consequence') is-invalid @enderror" rows="3" required>{{ $violation->consequence }}</textarea>
                    @error('consequence')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Submit Buttons -->
                <div class="form-actions">
                    <button type="button" class="cancel-btn">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="button" class="back-btn">
                        <i class="fas fa-arrow-left me-2"></i>Back to Violations
                    </button>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-lock me-2"></i>Update Violation
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Debug output to check violation types and their severities
    console.log('Debug: All violation types', @json($violationTypes));
    
    document.addEventListener('DOMContentLoaded', function() {
        // Get form elements
        const severitySelect = document.getElementById('severity');
        const offenseCountSelect = document.getElementById('offense_count');
        const penaltySelect = document.getElementById('penalty');
        
        // Set default offense count
        offenseCountSelect.value = '1st';
        
        // Function to update penalty based on severity and offense count
        function updatePenalty() {
            const severity = severitySelect.value;
            const offenseCount = offenseCountSelect.value;
            
            if (!severity || !offenseCount) return;
            
            // Determine the appropriate penalty based on severity and offense count
            let penaltyValue = '';
            
            if (severity === 'Low') {
                if (offenseCount === '1st') penaltyValue = 'VW'; // Verbal Warning
                else if (offenseCount === '2nd') penaltyValue = 'WW'; // Written Warning
                else if (offenseCount === '3rd') penaltyValue = 'Pro'; // Probation
                else penaltyValue = 'Exp'; // Expulsion for 4th offense
            } else if (severity === 'Medium') {
                if (offenseCount === '1st') penaltyValue = 'WW'; // Written Warning
                else if (offenseCount === '2nd') penaltyValue = 'Pro'; // Probation
                else penaltyValue = 'Exp'; // Expulsion for 3rd or more offense
            } else if (severity === 'High') {
                if (offenseCount === '1st') penaltyValue = 'Pro'; // Probation
                else penaltyValue = 'Exp'; // Expulsion for 2nd or more offense
            } else if (severity === 'Very High') {
                penaltyValue = 'Exp'; // Always expulsion for very high severity
            }
            
            // Set the penalty value
            penaltySelect.value = penaltyValue;
        }
        
        // Add event listeners
        severitySelect.addEventListener('change', updatePenalty);
        offenseCountSelect.addEventListener('change', updatePenalty);
        
        // Update penalty on page load
        updatePenalty();
        
        // Update available offense counts based on severity
        severitySelect.addEventListener('change', function() {
            const severity = this.value;
            const offenseCountOptions = offenseCountSelect.options;
            
            // Show/hide 4th offense option based on severity
            for (let i = 0; i < offenseCountOptions.length; i++) {
                if (offenseCountOptions[i].value === '4th') {
                    offenseCountOptions[i].style.display = (severity === 'Low') ? '' : 'none';
                }
            }
            
            // If a non-Low severity is selected and 4th offense was selected, reset to 3rd
            if (severity !== 'Low' && offenseCountSelect.value === '4th') {
                offenseCountSelect.value = '3rd';
            }
        });
        
        // Trigger the severity change event to initialize the offense count options
        severitySelect.dispatchEvent(new Event('change'));

        // Navigation for Cancel and Back buttons
        document.querySelector('.back-btn').addEventListener('click', (e) => {
            e.preventDefault();
            window.history.back();
        });

        document.querySelector('.cancel-btn').addEventListener('click', (e) => {
            e.preventDefault();
            window.history.back();
        });
    });
</script>

    <!-- JavaScript -->
    <script>
        // =============================================
        // Form Initialization
        // =============================================
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('offense_category_id');
            const violationTypeSelect = document.getElementById('violation_type_id');
            
            // Validate required elements
            if (!categorySelect || !violationTypeSelect) {
                console.error('Could not find required select elements:', {
                    categorySelect: !!categorySelect,
                    violationTypeSelect: !!violationTypeSelect
                });
                return;
            }

            console.log('Initial category value:', categorySelect.value);
            
            // Initialize severity based on the selected violation type
            function initializeSeverity() {
                const selectedViolationType = violationTypeSelect.options[violationTypeSelect.selectedIndex];
                if (selectedViolationType && selectedViolationType.dataset.severity) {
                    const severity = selectedViolationType.dataset.severity;
                    console.log('Initial severity from violation type:', severity);
                    
                    const severitySelect = document.getElementById('severity');
                    if (severitySelect) {
                        severitySelect.value = severity;
                        console.log('Setting initial severity to:', severity);
                        
                        // Trigger the change event to update dependent fields
                        severitySelect.dispatchEvent(new Event('change'));
                    }
                }
            }
            
            // Call the initialization function after the DOM is fully loaded
            setTimeout(initializeSeverity, 100);
            
            // =============================================
            // Violation Type Change Handler
            // =============================================
            violationTypeSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                console.log('Selected violation type:', selectedOption.textContent);
                
                // Get severity from data attribute
                const severity = selectedOption.dataset.severity;
                console.log('Severity from data attribute:', severity);
                
                if (severity) {
                    // Set the severity in the select field
                    const severitySelect = document.getElementById('severity');
                    if (severitySelect) {
                        severitySelect.value = severity;
                        console.log('Setting severity to:', severity);
                        
                        // Trigger the change event to update dependent fields
                        severitySelect.dispatchEvent(new Event('change'));
                    } else {
                        console.error('Severity select element not found');
                    }
                }
            });
            
            // =============================================
            // Category Change Handler
            // =============================================
            categorySelect.addEventListener('change', async function() {
                const categoryId = this.value;
                console.log('Category changed to:', categoryId);
                
                violationTypeSelect.innerHTML = '<option value="">Select Violation Type</option>';
                
                if (categoryId) {
                    try {
                        console.log('Fetching violation types for category:', categoryId);
                        const response = await fetch(`/educator/violation-types/${categoryId}`);
                        
                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        
                        const data = await response.json();
                        console.log('Received data:', data);
                        
                        // Clear existing options
                        violationTypeSelect.innerHTML = '<option value="">Select Violation Type</option>';
                        
                        if (Array.isArray(data) && data.length > 0) {
                            console.log('Processing violation types:', data.length);
                            data.forEach(type => {
                                console.log('Adding option:', type);
                                const option = document.createElement('option');
                                option.value = type.id;
                                option.textContent = type.name;
                                
                                // Make sure severity is set correctly
                                const severity = type.severity || 'Medium';
                                option.dataset.severity = severity;
                                
                                console.log('Adding violation type with severity:', type.name, severity);
                                violationTypeSelect.appendChild(option);
                            });
                            
                            // If we have options, trigger change on the first one to update severity
                            if (violationTypeSelect.options.length > 1) {
                                console.log('Auto-selecting first violation type');
                                violationTypeSelect.selectedIndex = 0; // Select the first option (which is the placeholder)
                                violationTypeSelect.selectedIndex = 1; // Select the first real option
                                violationTypeSelect.dispatchEvent(new Event('change'));
                            }
                        } else {
                            console.log('No violation types found for this category');
                        }
                    }
                    } catch (error) {
                        console.error('Error fetching violation types:', error);
                        violationTypeSelect.innerHTML = '<option value="">Error loading violation types</option>';
                    }
                }
            });
        });
    </script>
@endsection 