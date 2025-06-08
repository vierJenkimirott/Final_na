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
            
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            
            <!-- Violation Form -->
            <form id="violationForm" class="violation-form" method="POST" action="{{ route('educator.add-violation-type') }}">
                @csrf
                <!-- Violation Name Input -->
                <div class="form-group">
                    <label for="violation_name">Violation Name</label>
                    <input type="text" class="form-field" id="violation_name" name="violation_name" placeholder="Enter violation name" required/>
                    @error('violation_name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Category Selection -->
                <div class="form-group">
                    <label for="category">Category</label>
                    <select class="form-field" id="category" name="category" required>
                        <option value="" selected disabled>Select Category</option>
                        @foreach($categories ?? [] as $category)
                            <option value="{{ $category->category_name }}">{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Severity Selection -->
                <div class="form-group">
                    <label for="severity">Severity</label>
                    <select class="form-field" id="severity" name="severity" required>
                        <option value="" selected disabled>Select Severity</option>
                        @foreach(['Low', 'Medium', 'High', 'Very High'] as $severity)
                            <option value="{{ $severity }}">{{ $severity }}</option>
                        @endforeach
                    </select>
                    @error('severity')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Offense Selection -->
                <div class="form-group">
                    <label>Offenses & Penalties</label>
                    <div id="offense-penalty-list"></div>
                </div>
                <!-- Penalty Selection -->
                <!-- <div class="form-group">
                    <label for="penalty">Penalty</label>
                    <input type="text" class="form-field" id="penalty" name="penalty" readonly required>
                        @error('penalty')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                </div> -->

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
    // ...existing code...
// Dynamic offense and penalty logic
$(document).ready(function() {
    const penaltyMap = {
        'Low':   ['Warning', 'Verbal Warning', 'Written Warning'],
        'Medium': ['Verbal Warning', 'Written Warning', 'Probation'],
        'High':  ['Written Warning', 'Probation', 'Expulsion'],
        'Very High': ['Expulsion']
    };
    const offenseLabels = ['1st Offense', '2nd Offense', '3rd Offense'];

    $('#severity').on('change', function() {
        const severity = $(this).val();
        const $list = $('#offense-penalty-list');
        $list.empty();

        if (penaltyMap[severity]) {
            let html = '<ul style="list-style:none;padding-left:0;">';
            penaltyMap[severity].forEach((penalty, idx) => {
                html += `<li><strong>${offenseLabels[idx] || ((idx+1)+' Offense')}:</strong> ${penalty}</li>`;
            });
            html += '</ul>';
            $list.html(html);
        }
    });

    $('#offense').on('change', function() {
        const severity = $('#severity').val();
        const offenseIdx = this.selectedIndex - 1; // -1 for placeholder
        if (penaltyMap[severity] && penaltyMap[severity][offenseIdx]) {
            $('#penalty').val(penaltyMap[severity][offenseIdx]);
        } else {
            $('#penalty').val('');
        }
    });
// ...existing code...
    $('#violationForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Hide the form title and form
                    $('.form-title').hide();
                    $('#violationForm').hide();
                    
                    // Show success toast
                    showSuccessToast('Violation added successfully!');
                    
                    // Redirect after a short delay
                    setTimeout(function() {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            window.location.href = "{{ route('educator.violation') }}";
                        }
                    }, 2000);
                } else {
                    showErrorToast(response.message || 'Failed to create violation');
                }
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON?.message || 'An error occurred while creating the violation';
                showErrorToast(errorMessage);
            }
        });
    });
});
    
    // Back button functionality
    $('.back-btn').on('click', function() {
        window.history.back();
    });
    // Cancel button functionality
    $('.cancel-btn').on('click', function() {
        window.location.href = "{{ route('educator.violation') }}";
    });
</script>
@endpush




