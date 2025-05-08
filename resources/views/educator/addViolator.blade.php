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
                <meta name="csrf-token" content="{{ csrf_token() }}">
                <!-- Student Selection -->
                <div class="form-group">
                    <label for="student-select">Student</label>
                    <select class="form-field" id="student-select" name="student_id" required>
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
                    <input type="date" class="form-field" id="violation-date" required />
                </div>

                <!-- Violation Category -->
                <div class="form-group">
                    <label for="violation-category">Category</label>
                    <select class="form-field" id="violation-category" name="category_id" required>
                        <option value="" selected disabled>Select Category</option>
                        @foreach($offenseCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Violation Type -->
                <div class="form-group">
                    <label for="violation-type">Type of Violation</label>
                    <select class="form-field" id="violation-type" name="violation_type_id" required>
                        <option value="" selected disabled>Select Violation Type</option>
                    </select>
                </div>

                <!-- Severity Selection -->
                <div class="form-group" id="severity-group">
                    <label for="severity">Severity</label>
                    <select class="form-field" id="severity" required>
                        <option value="" selected disabled>Select Severity</option>
                        @if(isset($severities) && count($severities) > 0)
                            @foreach($severities as $severity)
                                <option value="{{ $severity }}">{{ $severity }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Offense Selection -->
                <div class="form-group" id="offense-group">
                    <label for="offense">Offense</label>
                    <select class="form-field" id="offense" required>
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
                    <select class="form-field" id="penalty" required>
                        <option value="" selected disabled>Select Penalty</option>
                        @if(isset($penalties) && count($penalties) > 0)
                            @foreach($penalties as $penalty)
                                <option value="{{ $penalty['value'] }}">{{ $penalty['label'] }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Consequence Input -->
                <div class="form-group" id="consequence-group">
                    <label for="consequence">Consequence</label>
                    <input type="text" class="form-field" id="consequence" placeholder="Enter consequence" required />
                </div>

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
        
        // Set the severity automatically without disabling the dropdown
        const severitySelect = document.getElementById('severity');
        if (severity) {
            severitySelect.value = severity;
            // Keep the dropdown visible and enabled
            // severitySelect.disabled = true; - removing this line
            
            // Update offense options based on severity
            updateOffenseOptions(severity);
        }
    });

    /**
     * Handle offense change event
     * Updates penalty options based on selected offense and severity
     */
    document.getElementById('offense').addEventListener('change', function() {
        const severity = document.getElementById('severity').value;
        const offense = this.value;
        updatePenaltyOptions(severity, offense);
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
        
        if (severity === 'Low') {
            offenseSelect.innerHTML += `
                <option value="1st">1st Offense</option>
                <option value="2nd">2nd Offense</option>
                <option value="3rd">3rd Offense</option>
            `;
        } else if (severity === 'Medium') {
            offenseSelect.innerHTML += `
                <option value="1st">1st Offense</option>
                <option value="2nd">2nd Offense</option>
                <option value="3rd">3rd Offense</option>
            `;
        } else if (severity === 'High') {
            offenseSelect.innerHTML += `
                <option value="1st">1st Offense</option>
                <option value="2nd">2nd Offense</option>
                <option value="3rd">3rd Offense</option>
            `;
        } else if (severity === 'Very High') {
            offenseSelect.innerHTML += `
                <option value="1st">1st Offense</option>
            `;
        } else {
            offenseSelect.innerHTML += `
                <option value="1st">1st Offense</option>
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
        
        if (severity === 'Low') {
            if (offense === '1st') {
                penaltySelect.innerHTML += `<option value="W">Warning</option>`;
            } else if (offense === '2nd') {
                penaltySelect.innerHTML += `<option value="VW">Verbal Warning</option>`;
            } else if (offense === '3rd') {
                penaltySelect.innerHTML += `<option value="WW">Written Warning</option>`;
            }
        } else if (severity === 'Medium') {
            if (offense === '1st') {
                penaltySelect.innerHTML += `<option value="VW">Verbal Warning</option>`;
            } else if (offense === '2nd') {
                penaltySelect.innerHTML += `<option value="WW">Written Warning</option>`;
            } else if (offense === '3rd') {
                penaltySelect.innerHTML += `<option value="Pro">Probation</option>`;
            }
        } else if (severity === 'High') {
            if (offense === '1st') {
                penaltySelect.innerHTML += `<option value="WW">Written Warning</option>`;
            } else if (offense === '2nd') {
                penaltySelect.innerHTML += `<option value="Pro">Probation</option>`;
            } else if (offense === '3rd') {
                penaltySelect.innerHTML += `<option value="Exp">Expulsion</option>`;
            }
        } else if (severity === 'Very High') {
            if (offense === '1st') {
                penaltySelect.innerHTML += `<option value="Pro">Expulsion</option>`;
            }
        } else {
            penaltySelect.innerHTML += `
                <option value="WW">Written Warning</option>
                <option value="Pro">Probation</option>
                <option value="Exp">Expulsion</option>
            `;
        }
    }

    // =============================================
    // Form Submission Handler
    // =============================================
    /**
     * Handle form submission
     * Collects form data and sends it to the backend
     */
    document.getElementById('violatorForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Get form data
        const formData = {
            student_id: document.getElementById('student-select').value,
            violation_date: document.getElementById('violation-date').value,
            violation_type_id: document.getElementById('violation-type').value,
            severity: document.getElementById('severity').value,
            offense: document.getElementById('offense').value,
            penalty: document.getElementById('penalty').value,
            consequence: document.getElementById('consequence').value
        };

        try {
            console.log('Submitting form data:', formData);
            
            // Send data to backend
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch('{{ route("educator.add-violator") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            console.log('Response status:', response.status);
            const result = await response.json();
            console.log('Response data:', result);

            if (result.success) {
                // Show success message
                alert('Violation recorded successfully!');
                // Redirect to violations list
                window.location.href = '{{ route("educator.violation") }}';
            } else {
                // Show error message with details
                alert('Error: ' + (result.message || 'Unknown error occurred'));
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            alert('An error occurred while submitting the form. Please check the console for details.');
        }
    });
</script>
@endpush