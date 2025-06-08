@extends('layouts.educator')

@section('content')
    <!-- Main Container -->
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Violation</h2>
            <a href="{{ route('educator.violation') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Violations</a>
        </div>
        
        {{-- Removed Laravel session success/error alerts, now handled by JavaScript toasts --}}

        <!-- Edit Violation Form -->
        <form action="{{ route('educator.update-violation', ['id' => $violation->id]) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <!-- Student Selection -->
                <div class="col-md-6 mb-3">
                    <label for="student_id" class="form-label">Student <span class="text-danger">*</span></label>
                    <select name="student_id" id="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
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
                <div class="col-md-6 mb-3">
                    <label for="violation_date" class="form-label">Violation Date <span class="text-danger">*</span></label>
                    <input type="date" name="violation_date" id="violation_date" class="form-control @error('violation_date') is-invalid @enderror" 
                           value="{{ \Carbon\Carbon::parse($violation->violation_date)->format('Y-m-d') }}" required>
                    @error('violation_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <!-- Category Selection -->
                <div class="col-md-6 mb-3">
                    <label for="offense_category_id" class="form-label">Category <span class="text-danger">*</span></label>
                    <select name="offense_category_id" id="offense_category_id" class="form-select @error('offense_category_id') is-invalid @enderror" required>
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
                <div class="col-md-6 mb-3">
                    <label for="violation_type_id" class="form-label">Violation Type <span class="text-danger">*</span></label>
                    <select name="violation_type_id" id="violation_type_id" class="form-select @error('violation_type_id') is-invalid @enderror" required>
                        <option value="">Select Violation Type</option>
                        {{-- Get violation types with severity directly from the database --}}
                        @php
                            $violationTypesWithSeverity = DB::table('violation_types')
                                ->select('violation_types.id', 'violation_types.violation_name', 'severities.severity_name')
                                ->join('severities', 'violation_types.severity_id', '=', 'severities.id')
                                ->where('violation_types.offense_category_id', $violation->offenseCategory->id)
                                ->get();
                        @endphp
                        
                        @foreach($violationTypesWithSeverity as $type)
                            {{-- Debug output for severity values --}}
                            @php
                                echo "<!-- Debug: Type ID: {$type->id}, Name: {$type->violation_name}, Severity: {$type->severity_name} -->";
                            @endphp
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
            </div>
            
            <div class="row">
                <!-- Severity Selection -->
                <div class="col-md-6 mb-3">
                    <label for="severity" class="form-label">Severity <span class="text-danger">*</span></label>
                    <select name="severity" id="severity" class="form-select @error('severity') is-invalid @enderror" required>
                        <option value="">Select Severity</option>
                        <option value="Low" {{ $violation->severity == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Medium" {{ $violation->severity == 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="High" {{ $violation->severity == 'High' ? 'selected' : '' }}>High</option>
                        <option value="Very High" {{ $violation->severity == 'Very High' ? 'selected' : '' }}>Very High</option>
                    </select>
                    @error('severity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Severity level affects behavior score deductions</small>
                </div>
                
                <!-- Offense Count Selection -->
                <div class="col-md-6 mb-3">
                    <label for="offense_count" class="form-label">Offense Count <span class="text-danger">*</span></label>
                    <select name="offense_count" id="offense_count" class="form-select" required>
                        <option value="">Select Offense Count</option>
                        <option value="1st">1st Offense</option>
                        <option value="2nd">2nd Offense</option>
                        <option value="3rd">3rd Offense</option>
                        <option value="4th">4th Offense</option>
                    </select>
                    <small class="form-text text-muted">Number of times this violation has occurred</small>
                </div>
                
                <!-- Penalty Selection -->
                <div class="col-md-6 mb-3">
                    <label for="penalty" class="form-label">Penalty <span class="text-danger">*</span></label>
                    <select name="penalty" id="penalty" class="form-select @error('penalty') is-invalid @enderror" required>
                        <option value="">Select Penalty</option>
                        <option value="VW" {{ $violation->penalty == 'VW' ? 'selected' : '' }}>Verbal Warning</option>
                        <option value="WW" {{ $violation->penalty == 'WW' ? 'selected' : '' }}>Written Warning</option>
                        <option value="Pro" {{ $violation->penalty == 'Pro' ? 'selected' : '' }}>Probation</option>
                        <option value="Exp" {{ $violation->penalty == 'Exp' ? 'selected' : '' }}>Expulsion</option>
                    </select>
                    @error('penalty')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Penalty is automatically determined by severity and offense count</small>
                </div>
            </div>
            

            <!-- Consequence Description -->
            <div class="mb-3">
                <label for="consequence" class="form-label">Consequence <span class="text-danger">*</span></label>
                <textarea name="consequence" id="consequence" class="form-control @error('consequence') is-invalid @enderror" rows="3" required>{{ $violation->consequence }}</textarea>
                @error('consequence')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Describe the consequences or actions taken</small>
            </div>
            
            <!-- Status Selection -->
            <div class="mb-3">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="active" {{ $violation->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="resolved" {{ $violation->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Set to 'Resolved' when the violation has been addressed</small>
            </div>
            
            <!-- Submit Button -->
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('educator.violation') }}" class="btn btn-secondary"><i class="fas fa-times me-2"></i>Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Violation</button>
            </div>
        </form>
    </div>

    <!-- Custom Styles -->
    <style>
        /* Container Styling */
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Form Group Styling */
        .form-group {
            margin-bottom: 20px;
        }
        
        /* Label Styling */
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        /* Form Control Styling */
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        /* Textarea Styling */
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        /* Action Buttons Container */
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        /* Button Base Styling */
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        /* Primary Button Styling */
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        /* Secondary Button Styling */
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        /* Button Hover Effect */
        .btn:hover {
            opacity: 0.9;
        }
    </style>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Display session messages as custom toasts
            @if(session('success'))
                window.showCustomToast('{{ session('success') }}', 'success');
            @endif

            @if(session('error'))
                window.showCustomToast('{{ session('error') }}', 'error');
            @endif

            // Logic to fetch violation types based on category selection
            const offenseCategorySelect = document.getElementById('offense_category_id');
            const violationTypeSelect = document.getElementById('violation_type_id');
            const severitySelect = document.getElementById('severity');
            const offenseCountSelect = document.getElementById('offense_count');
            const penaltySelect = document.getElementById('penalty');

            function fetchViolationTypes(categoryId, selectedViolationTypeId = null) {
                if (!categoryId) {
                    violationTypeSelect.innerHTML = '<option value="">Select Violation Type</option>';
                    return;
                }

                fetch(`/api/violation-types/${categoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        violationTypeSelect.innerHTML = '<option value="">Select Violation Type</option>';
                        data.forEach(type => {
                            const option = document.createElement('option');
                            option.value = type.id;
                            option.textContent = type.violation_name;
                            option.dataset.severity = type.severity.severity_name; // Store severity name
                            if (selectedViolationTypeId && type.id == selectedViolationTypeId) {
                                option.selected = true;
                            }
                            violationTypeSelect.appendChild(option);
                        });

                        // If a violation type was pre-selected, update severity and penalty
                        if (selectedViolationTypeId) {
                            updateSeverityAndPenalty();
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching violation types:', error);
                        window.showCustomToast('Failed to load violation types.', 'error');
                    });
            }

            function updateSeverityAndPenalty() {
                const selectedOption = violationTypeSelect.options[violationTypeSelect.selectedIndex];
                const selectedSeverity = selectedOption ? selectedOption.dataset.severity : '';

                severitySelect.value = selectedSeverity;
                updatePenaltyField(selectedSeverity, offenseCountSelect.value);
            }

            function updatePenaltyField(severity, offenseCount) {
                let penalty = '';
                if (severity && offenseCount) {
                    switch (severity) {
                        case 'Low':
                            if (offenseCount === '1st') penalty = 'VW';
                            else if (offenseCount === '2nd') penalty = 'WW';
                            else if (offenseCount === '3rd') penalty = 'Pro';
                            else if (offenseCount === '4th') penalty = 'Exp';
                            break;
                        case 'Medium':
                            if (offenseCount === '1st') penalty = 'WW';
                            else if (offenseCount === '2nd') penalty = 'Pro';
                            else if (offenseCount === '3rd') penalty = 'Exp';
                            else if (offenseCount === '4th') penalty = 'Exp';
                            break;
                        case 'High':
                            if (offenseCount === '1st') penalty = 'Pro';
                            else if (offenseCount === '2nd') penalty = 'Exp';
                            else if (offenseCount === '3rd') penalty = 'Exp';
                            else if (offenseCount === '4th') penalty = 'Exp';
                            break;
                        case 'Very High':
                            penalty = 'Exp'; // All Very High offenses result in Expulsion
                            break;
                    }
                }
                penaltySelect.value = penalty;
            }

            // Event Listeners
            offenseCategorySelect.addEventListener('change', function() {
                fetchViolationTypes(this.value);
                updateSeverityAndPenalty(); // Update severity and penalty after category changes
            });

            violationTypeSelect.addEventListener('change', updateSeverityAndPenalty);
            offenseCountSelect.addEventListener('change', function() {
                updatePenaltyField(severitySelect.value, this.value);
            });

            // Initial fetch and update if editing an existing violation
            const initialCategoryId = offenseCategorySelect.value;
            const initialViolationTypeId = '{{ $violation->violation_type_id }}';
            const initialOffenseCount = '{{ $violation->offense_count }}';

            if (initialCategoryId) {
                fetchViolationTypes(initialCategoryId, initialViolationTypeId);
            }
            offenseCountSelect.value = initialOffenseCount; // Set initial offense count
            updatePenaltyField(severitySelect.value, initialOffenseCount);

        });
    </script>
@endpush 