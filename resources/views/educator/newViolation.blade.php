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
                    <label for="offense">Offense</label>
                    <select class="form-field" id="offense" name="offense" required>
                        <option value="" selected disabled>Select Offense</option>
                        @foreach(['1st', '2nd', '3rd'] as $offense)
                            <option value="{{ $offense }}">{{ $offense }} Offense</option>
                        @endforeach
                    </select>
                    @error('offense')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Penalty Selection -->
                <div class="form-group">
                    <label for="penalty">Penalty</label>
                    <select class="form-field" id="penalty" name="penalty" required>
                        <option value="" selected disabled>Select Penalty</option>
                        @foreach([['value' => 'W', 'label' => 'Warning'], ['value' => 'VW', 'label' => 'Verbal Warning'], ['value' => 'WW', 'label' => 'Written Warning'], ['value' => 'Pro', 'label' => 'Probation'], ['value' => 'Exp', 'label' => 'Expulsion']] as $penalty)
                            <option value="{{ $penalty['value'] }}">{{ $penalty['label'] }}</option>
                        @endforeach
                    </select>
                    @error('penalty')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
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
</script>
@endpush