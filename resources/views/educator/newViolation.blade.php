@extends('layouts.educator')

@section('css')
    <!-- External CSS Dependencies -->
    <link rel="stylesheet" href="{{ asset('css/newViolation.css') }}">
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
            <h2 class="form-title">Add New Violation</h2>
            
            <!-- Violation Form -->
            <form id="violationForm" class="violation-form">
                <!-- Violation Name Input -->
                <div class="form-group">
                    <label for="violationName">Violation Name</label>
                    <input type="text" class="form-field" id="violationName" placeholder="Enter violation name" required/>
                </div>

                <!-- Category Selection -->
                <div class="form-group">
                    <label for="category">Category</label>
                    <select class="form-field" id="category" required>
                        <option value="" selected disabled>Select Category</option>
                    </select>
                </div>

                <!-- Severity Selection -->
                <div class="form-group">
                    <label for="severity">Severity</label>
                    <select class="form-field" id="severity" required>
                        <option value="" selected disabled>Select Severity</option>
                    </select>
                </div>

                <!-- Offense Selection -->
                <div class="form-group">
                    <label for="offense">Offense</label>
                    <select class="form-field" id="offense" required>
                        <option value="" selected disabled>Select Offense</option>
                    </select>
                </div>

                <!-- Penalty Selection -->
                <div class="form-group">
                    <label for="penalty">Penalty</label>
                    <select class="form-field" id="penalty" required>
                        <option value="" selected disabled>Select Penalty</option>
                    </select>
                </div>

                <!-- Form Action Buttons -->
                <div class="form-actions">
                    <button type="button" class="cancel-btn">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-plus"></i> Add Violation
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
    // Form Data Fetching
    // =============================================
    /**
     * Fetch and populate form data from the backend
     * Populates categories, severities, offenses, and penalties
     */
    async function fetchFormData() {
        try {
            const response = await fetch('{{ route("educator.violation-form-data") }}');
            const result = await response.json();

            if (result.success) {
                const { categories, severities, offenses, penalties } = result.data;

                // Populate categories
                const categorySelect = document.getElementById('category');
                categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.category_name;
                    option.textContent = category.category_name;
                    categorySelect.appendChild(option);
                });

                // Populate severities
                const severitySelect = document.getElementById('severity');
                severities.forEach(severity => {
                    const option = document.createElement('option');
                    option.value = severity;
                    option.textContent = severity;
                    severitySelect.appendChild(option);
                });

                // Populate offenses
                const offenseSelect = document.getElementById('offense');
                offenses.forEach(offense => {
                    const option = document.createElement('option');
                    option.value = offense;
                    option.textContent = offense + ' Offense';
                    offenseSelect.appendChild(option);
                });

                // Populate penalties
                const penaltySelect = document.getElementById('penalty');
                penalties.forEach(penalty => {
                    const option = document.createElement('option');
                    option.value = penalty.value;
                    option.textContent = penalty.label;
                    penaltySelect.appendChild(option);
                });
            } else {
                console.error('Error fetching form data:', result.message);
                alert('Error loading form data. Please refresh the page.');
            }
        } catch (error) {
            console.error('Error fetching form data:', error);
            alert('Error loading form data. Please refresh the page.');
        }
    }

    // Initialize form data when the page loads
    document.addEventListener('DOMContentLoaded', fetchFormData);

    // =============================================
    // Form Submission Handler
    // =============================================
    /**
     * Handle form submission
     * Collects form data and sends it to the backend
     */
    document.getElementById('violationForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Get form data
        const formData = {
            violation_name: document.getElementById('violationName').value,
            category: document.getElementById('category').value,
            severity: document.getElementById('severity').value,
            offense: document.getElementById('offense').value,
            penalty: document.getElementById('penalty').value
        };

        try {
            // Send data to backend
            const response = await fetch('{{ route("educator.add-violation-type") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                // Show success message
                alert('Violation type added successfully!');
                // Redirect to violations list
                window.location.href = '{{ route("educator.violation") }}';
            } else {
                // Show error message
                alert('Error: ' + (result.message || 'Unknown error occurred'));
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            alert('An error occurred while submitting the form. Please try again.');
        }
    });
</script>
@endpush