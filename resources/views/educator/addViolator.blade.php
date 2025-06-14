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
                    <input type="text" class="form-field" id="offense" name="offense" readonly value="1st offense" />
                    <input type="hidden" id="offense-count" name="offense_count" value="1" />
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
    // Navigation Event Handlers
    // =============================================
    document.querySelector('.back-btn').addEventListener('click', (e) => {
        e.preventDefault(); // Prevent default form submission behavior
        window.history.back();
    });

    document.querySelector('.cancel-btn').addEventListener('click', (e) => {
        e.preventDefault(); // Also prevent default to be safe
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
            fetch(`/api/violation-types/${categoryId}`)
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
                            option.textContent = violation.violation_name;
                            option.dataset.severity = violation.severity; // Store severity in data attribute
                            console.log('Adding violation type:', violation.violation_name, 'with severity:', violation.severity);
                            violationTypeSelect.appendChild(option);
                        });
                        
                        // If we have options, select the first one to initialize severity
                        if (violationTypeSelect.options.length > 1) {
                            // Select the first real option (index 1, after the placeholder)
                            violationTypeSelect.selectedIndex = 1;
                            // Trigger the change event to update severity and dependent fields
                            violationTypeSelect.dispatchEvent(new Event('change'));
                            console.log('Auto-selected first violation type to initialize severity');
                        }
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
                    // Show toast notification
                    if (typeof window.showCustomToast === 'function') {
                        window.showCustomToast('Failed to load violation types. Please try again.', 'error');
                    }
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
            
            // Reset offense to 1st by default
            document.getElementById('offense').value = '1st offense';
            document.getElementById('offense-count').value = '1';
            
            // Check if student is selected and if so, check for existing violations
            const studentId = document.getElementById('student-select').value;
            if (studentId) {
                checkExistingViolations(studentId, this.value);
            }
            
            // Update penalty based on severity and offense
            setPenalty(severity, '1st');
        }
    });

    /**
     * Handle student select change event
     * Reset offense count when student changes or check for existing violations
     */
    document.getElementById('student-select').addEventListener('change', function() {
        // Reset offense to 1st offense by default
        document.getElementById('offense-count').value = '1';
        document.getElementById('offense').value = '1st offense';
        
        // Check if violation type is selected and if so, check for existing violations
        const violationTypeId = document.getElementById('violation-type').value;
        if (violationTypeId) {
            checkExistingViolations(this.value, violationTypeId);
        } else {
            // Just update penalty based on current severity and 1st offense
            const severity = document.getElementById('severity').value;
            setPenalty(severity, '1st');
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
    
    // Add a 4th offense option for low severity
    document.getElementById('violation-type').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const severity = selectedOption.dataset.severity;
        const offenseSelect = document.getElementById('offense');
        
        // Clear existing options
        while (offenseSelect.options.length > 1) {
            offenseSelect.remove(1);
        }
        
        // Add standard options
        const offenses = ['1st', '2nd', '3rd'];
        
        // Add 4th offense for low severity
        if (severity && severity.toLowerCase() === 'low') {
            offenses.push('4th');
        }
        
        // Populate the offense dropdown
        offenses.forEach(offense => {
            const option = document.createElement('option');
            option.value = offense;
            option.textContent = `${offense} Offense`;
            offenseSelect.appendChild(option);
        });
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
    
    // Map penalties to their code values
    const penaltyMap = {
        'Verbal Warning': 'VW',
        'Written Warning': 'WW',
        'Probation': 'Pro',
        'Expulsion': 'Exp'
    };

    // Determine the appropriate penalty based on severity and offense count
    if (normalizedSeverity === 'low') {
        if (offense === '1st') penalty = 'Verbal Warning';
        else if (offense === '2nd') penalty = 'Written Warning';
        else if (offense === '3rd') penalty = 'Probation';
        else penalty = 'Expulsion'; // 4th or more offense
    } else if (normalizedSeverity === 'medium') {
        if (offense === '1st') penalty = 'Written Warning';
        else if (offense === '2nd') penalty = 'Probation';
        else penalty = 'Expulsion'; // 3rd or more offense
    } else if (normalizedSeverity === 'high') {
        if (offense === '1st') penalty = 'Probation';
        else penalty = 'Expulsion'; // 2nd or more offense
    } else if (normalizedSeverity === 'very high') {
        penalty = 'Expulsion'; // Always expulsion for very high severity
    }
    
    // Set the penalty value to the code, not the label
    penaltyInput.value = penaltyMap[penalty] || '';
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
     * 
     * Note: This function is now handled by the violation-type change event handler
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

    /**
     * Check for existing violations for a student with the same severity
     * @param {string} studentId - The student ID
     * @param {string} violationTypeId - The violation type ID
     */
    function checkExistingViolations(studentId, violationTypeId) {
        if (!studentId || !violationTypeId) {
            console.log('Missing student ID or violation type ID');
            return;
        }
        
        console.log('Checking existing violations for student:', studentId, 'and violation type:', violationTypeId);
        
        // Fetch existing violations count for this student and severity
        fetch(`/educator/check-existing-violations?student_id=${studentId}&violation_type_id=${violationTypeId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Received offense data:', data);
                if (data && data.offenseCount) {
                    // Use the offense string directly from the API if available
                    const offenseString = data.offenseString || '1st offense';
                    const offenseNum = data.offenseCount;
                    
                    // Map numeric values to strings if offenseString is not provided
                    const offenseMap = {
                        1: '1st',
                        2: '2nd',
                        3: '3rd',
                        4: '4th'
                    };
                    const offenseText = offenseMap[offenseNum] || '1st';
                    
                    // Update the offense field
                    document.getElementById('offense-count').value = offenseNum;
                    document.getElementById('offense').value = data.offenseString || `${offenseText} offense`;
                    
                    // Use the final penalty from the API if available
                    if (data.finalPenalty) {
                        // Map penalty codes to dropdown values
                        const penaltyMap = {
                            'VW': 'Verbal Warning',
                            'WW': 'Written Warning',
                            'Pro': 'Probation',
                            'Exp': 'Expulsion'
                        };
                        
                        const penaltyText = penaltyMap[data.finalPenalty] || '';
                        const penaltySelect = document.getElementById('penalty');
                        
                        // Find and select the option with the matching text
                        for (let i = 0; i < penaltySelect.options.length; i++) {
                            if (penaltySelect.options[i].text === penaltyText) {
                                penaltySelect.selectedIndex = i;
                                break;
                            }
                        }
                        
                        console.log(`Set penalty to ${penaltyText} based on highest existing penalty`);
                    } else {
                        // Fallback to the old method if finalPenalty is not provided
                        const severity = document.getElementById('severity').value;
                        setPenalty(severity, offenseText);
                    }
                    
                    console.log(`Set offense to ${offenseString || `${offenseText} offense`} based on existing violations`);
                } else {
                    // Default to 1st offense if no existing violations
                    document.getElementById('offense-count').value = '1';
                    document.getElementById('offense').value = '1st offense';
                    
                    // Update penalty based on severity and 1st offense
                    const severity = document.getElementById('severity').value;
                    setPenalty(severity, '1st');
                    
                    console.log('No existing violations found, set to 1st offense');
                }
            })
            .catch(error => {
                console.error('Error checking existing violations:', error);
                // Default to 1st offense on error
                document.getElementById('offense-count').value = '1';
                document.getElementById('offense').value = '1st offense';
            });
    }
    
    // Add a hidden status field to the form
    const statusField = document.createElement('input');
    statusField.type = 'hidden';
    statusField.name = 'status';
    statusField.value = 'active';
    document.getElementById('violatorForm').appendChild(statusField);
    
    
</script>
@endpush