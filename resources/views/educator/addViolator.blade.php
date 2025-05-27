@extends('layouts.educator')

@section('css')
    <!-- External CSS Dependencies -->
    <link rel="stylesheet" href="{{ asset('css/addViolator.css') }}">
@endsection

@section('content')
    <!-- Main Content Wrapper -->
    <div class="content-wrapper">
        <!-- Back Button -->
        <button class="back-btn">
            <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12l4-4m-4 4 4 4"/>
            </svg>Back
        </button>

        <!-- Form Container -->
        <div class="form-container">
            <h2 class="form-title">Add New Violator</h2>
            
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

                <!-- Offense Selection -->
                <div class="form-group" id="offense-group">
                    <label for="offense">Offense</label>
                    <select class="form-field" id="offense" name="offense" required>
                        <option value="" selected disabled>Select Offense</option>
                        @if(isset($offenses) && count($offenses) > 0)
                            @foreach($offenses as $offense)
                                <option value="{{ $offense }}">{{ $offense }} Offense</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Penalty Selection -->
                <div class="form-group" id="penalty-group">
                    <label for="penalty">Penalty</label>
                    <input type="text" class="form-field" id="penalty" name="penalty" readonly />
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
                
                <!-- Hidden Status Field -->
                <input type="hidden" name="status" value="active" />

                <!-- Form Action Buttons -->
                <div class="form-actions">
                    <button type="button" class="cancel-btn">
                        <i class="fas fa-times"></i> Cancel
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
    // Navigation Event Handlers
    // =============================================
    document.querySelector('.back-btn').addEventListener('click', () => {
        window.history.back();
    });

    document.querySelector('.cancel-btn').addEventListener('click', () => {
        window.history.back();
    });

    // =============================================
    // Global Variables
    // =============================================
    let violationData = {};

    // =============================================
    // Event Handlers
    // =============================================
    /**
     * Handle category change event
     * Fetches and populates violation types based on selected category
     */
    document.getElementById('violation-category').addEventListener('change', function() {
        const categoryId = this.value;
        const violationTypeSelect = document.getElementById('violation-type');
        
        // Clear current options
        violationTypeSelect.innerHTML = '<option value="" selected disabled>Select Violation Type</option>';
        
        if (categoryId) {
            console.log('Fetching violation types for category:', categoryId);
            // Fetch violation types for selected category
            fetch(`/educator/violation-types/${categoryId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Received violation types:', data);
                    violationData = data; // Store the data
                    if (data && data.length > 0) {
                        data.forEach(violation => {
                            const option = document.createElement('option');
                            option.value = violation.id;
                            option.textContent = violation.name;
                            option.dataset.severity = violation.severity; // Store severity in data attribute
                            console.log('Adding violation type:', violation.name, 'with severity:', violation.severity);
                            violationTypeSelect.appendChild(option);
                        });
                    } else {
                        // If no data is returned, show a message
                        const option = document.createElement('option');
                        option.value = "";
                        option.disabled = true;
                        option.textContent = "No violation types found for this category";
                        violationTypeSelect.appendChild(option);
                    }
                })
                .catch(error => {
                    console.error('Error fetching violation types:', error);
                    // Show error message in the dropdown
                    const option = document.createElement('option');
                    option.value = "";
                    option.disabled = true;
                    option.textContent = "Error loading violation types";
                    violationTypeSelect.appendChild(option);
                });
        }
    });

    /**
     * Handle violation type change event
     * Updates severity and offense options based on selected violation type
     */
    document.getElementById('violation-type').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        console.log('Selected violation:', selectedOption);
        const severity = selectedOption.dataset.severity;
        console.log('Severity from data attribute:', severity);
        
        if (severity) {
            // Set the severity in the input field
            document.getElementById('severity').value = severity;
            
            // Update offense options based on severity
            updateOffenseOptions(severity);
            
            // Reset offense and penalty selections
            document.getElementById('offense').selectedIndex = 0;
            document.getElementById('penalty').selectedIndex = 0;
        }
    });

    /**
     * Handle offense change event
     * Updates penalty options based on selected offense and severity
     */
    document.getElementById('offense').addEventListener('change', function() {
        const severity = document.getElementById('severity').value;
        const offense = this.value;
        setPenalty(severity, offense);
    });

    /**
 * Set penalty value based on severity and offense
 * @param {string} severity - The severity level of the violation
 * @param {string} offense - The offense number (1st, 2nd, 3rd)
 */
function setPenalty(severity, offense) {
    const penaltyInput = document.getElementById('penalty');
    let penalty = '';
    const normalizedSeverity = severity.toLowerCase();

    if (normalizedSeverity === 'low') {
        if (offense === '1st') penalty = 'Warning';
        else if (offense === '2nd') penalty = 'Verbal Warning';
        else if (offense === '3rd') penalty = 'Written Warning';
    } else if (normalizedSeverity === 'medium') {
        if (offense === '1st') penalty = 'Verbal Warning';
        else if (offense === '2nd') penalty = 'Written Warning';
        else if (offense === '3rd') penalty = 'Probation';
    } else if (normalizedSeverity === 'high') {
        if (offense === '1st') penalty = 'Written Warning';
        else if (offense === '2nd') penalty = 'Probation';
        else if (offense === '3rd') penalty = 'Expulsion';
    } else if (normalizedSeverity === 'very high') {
        if (offense === '1st') penalty = 'Expulsion';
    }
    penaltyInput.value = penalty;
}

    document.getElementById('consequence-select').addEventListener('change', function() {
        const input = document.getElementById('consequence-input');
        if (this.value === 'other') {
            input.style.display = 'block';
            input.required = true;
            input.value = '';
        } else {
            input.style.display = 'none';
            input.required = false;
            input.value = this.value; // Set the input value to the selected dropdown value
        }
    });

    document.getElementById('violatorForm').addEventListener('submit', function(e) {
    const studentId = document.getElementById('student-select').value;
    const violationDate = document.getElementById('violation-date').value;
    const violationType = document.getElementById('violation-type').value;
    const severity = document.getElementById('severity').value;
    const offense = document.getElementById('offense').value;
    const penalty = document.getElementById('penalty').value;

    if (!studentId || !violationDate || !violationType || !severity || !offense || !penalty) {
        e.preventDefault();
        alert('Please fill in all required fields.');
    }
});
    // =============================================
    // Helper Functions
    // =============================================
    /**
     * Update offense options based on severity level
     * @param {string} severity - The severity level of the violation
     */
    function updateOffenseOptions(severity) {
        const offenseSelect = document.getElementById('offense');
        offenseSelect.innerHTML = '<option value="" selected disabled>Select Offense</option>';
        
        // Normalize severity for case-insensitive comparison
        const normalizedSeverity = severity.toLowerCase();
        
        if (normalizedSeverity === 'low') {
            offenseSelect.innerHTML += `
                <option value="1st">1st Offense</option>
                <option value="2nd">2nd Offense</option>
                <option value="3rd">3rd Offense</option>
            `;
        } else if (normalizedSeverity === 'medium') {
            offenseSelect.innerHTML += `
                <option value="1st">1st Offense</option>
                <option value="2nd">2nd Offense</option>
                <option value="3rd">3rd Offense</option>
            `;
        } else if (normalizedSeverity === 'high') {
            offenseSelect.innerHTML += `
                <option value="1st">1st Offense</option>
                <option value="2nd">2nd Offense</option>
                <option value="3rd">3rd Offense</option>
            `;
        } else if (normalizedSeverity === 'very high') {
            offenseSelect.innerHTML += `
                <option value="1st">1st Offense</option>
            `;
        } else {
            // Default case - show all options
            offenseSelect.innerHTML += `
                <option value="1st">1st Offense</option>
                <option value="2nd">2nd Offense</option>
                <option value="3rd">3rd Offense</option>
            `;
        }
    }

    /**
     * Update penalty options based on severity and offense
     * @param {string} severity - The severity level of the violation
     * @param {string} offense - The offense number (1st, 2nd, 3rd)
     */
    function updatePenaltyOptions(severity, offense) {
        const penaltySelect = document.getElementById('penalty');
        penaltySelect.innerHTML = '<option value="" selected disabled>Select Penalty</option>';
        
        // Normalize severity for case-insensitive comparison
        const normalizedSeverity = severity.toLowerCase();
        
        if (normalizedSeverity === 'low') {
            if (offense === '1st') {
                penaltySelect.innerHTML += `<option value="W">Warning</option>`;
            } else if (offense === '2nd') {
                penaltySelect.innerHTML += `<option value="VW">Verbal Warning</option>`;
            } else if (offense === '3rd') {
                penaltySelect.innerHTML += `<option value="WW">Written Warning</option>`;
            }
        } else if (normalizedSeverity === 'medium') {
            if (offense === '1st') {
                penaltySelect.innerHTML += `<option value="VW">Verbal Warning</option>`;
            } else if (offense === '2nd') {
                penaltySelect.innerHTML += `<option value="WW">Written Warning</option>`;
            } else if (offense === '3rd') {
                penaltySelect.innerHTML += `<option value="Pro">Probation</option>`;
            }
        } else if (normalizedSeverity === 'high') {
            if (offense === '1st') {
                penaltySelect.innerHTML += `<option value="WW">Written Warning</option>`;
            } else if (offense === '2nd') {
                penaltySelect.innerHTML += `<option value="Pro">Probation</option>`;
            } else if (offense === '3rd') {
                penaltySelect.innerHTML += `<option value="Exp">Expulsion</option>`;
            }
        } else if (normalizedSeverity === 'very high') {
            if (offense === '1st') {
                penaltySelect.innerHTML += `<option value="Exp">Expulsion</option>`;
            }
        } else {
            // Default case - show all penalties
            penaltySelect.innerHTML += `
                <option value="W">Warning</option>
                <option value="VW">Verbal Warning</option>
                <option value="WW">Written Warning</option>
                <option value="Pro">Probation</option>
                <option value="Exp">Expulsion</option>
            `;
        }
    }

    // Add a hidden status field to the form
    const statusField = document.createElement('input');
    statusField.type = 'hidden';
    statusField.name = 'status';
    statusField.value = 'active';
    document.getElementById('violatorForm').appendChild(statusField);
    
    
</script>
@endpush