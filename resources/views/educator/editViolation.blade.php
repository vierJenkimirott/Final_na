@extends('layouts.educator')

@section('content')
    <!-- Main Container -->
    <div class="container">
        <h2>Edit Violation</h2>
        
        <!-- Edit Violation Form -->
        <form action="{{ route('educator_update_violation', ['id' => $violation->id]) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Student Selection -->
            <div class="form-group">
                <label for="student_id">Student</label>
                <select name="student_id" id="student_id" class="form-control" required>
                    <option value="">Select Student</option>
                    @foreach($students as $student)
                        <option value="{{ $student->student_id }}" {{ $violation->student_id == $student->student_id ? 'selected' : '' }}>
                            {{ $student->lname }}, {{ $student->fname }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Violation Date -->
            <div class="form-group">
                <label for="violation_date">Violation Date</label>
                <input type="date" name="violation_date" id="violation_date" class="form-control" 
                       value="{{ \Carbon\Carbon::parse($violation->violation_date)->format('Y-m-d') }}" required>
            </div>
            
            <!-- Category Selection -->
            <div class="form-group">
                <label for="offense_category_id">Category</label>
                <select name="offense_category_id" id="offense_category_id" class="form-control" required>
                    <option value="">Select Category</option>
                    @foreach($offenseCategories as $category)
                        <option value="{{ $category->id }}" {{ $violation->offenseCategory->id == $category->id ? 'selected' : '' }}>
                            {{ $category->category_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Violation Type Selection -->
            <div class="form-group">
                <label for="violation_type_id">Violation Type</label>
                <select name="violation_type_id" id="violation_type_id" class="form-control" required>
                    <option value="">Select Violation Type</option>
                    @foreach($violation->offenseCategory->violationTypes as $type)
                        <option value="{{ $type->id }}" {{ $violation->violation_type_id == $type->id ? 'selected' : '' }}>
                            {{ $type->violation_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Severity Selection -->
            <div class="form-group">
                <label for="severity">Severity</label>
                <select name="severity" id="severity" class="form-control" required>
                    <option value="Low" {{ $violation->severity == 'Low' ? 'selected' : '' }}>Low</option>
                    <option value="Medium" {{ $violation->severity == 'Medium' ? 'selected' : '' }}>Medium</option>
                    <option value="High" {{ $violation->severity == 'High' ? 'selected' : '' }}>High</option>
                    <option value="Very High" {{ $violation->severity == 'Very High' ? 'selected' : '' }}>Very High</option>
                </select>
            </div>
            
            <!-- Offense Selection -->
            <div class="form-group">
                <label for="offense">Offense</label>
                <select name="offense" id="offense" class="form-control" required>
                    <option value="1st" {{ $violation->offense == '1st' ? 'selected' : '' }}>1st Offense</option>
                    <option value="2nd" {{ $violation->offense == '2nd' ? 'selected' : '' }}>2nd Offense</option>
                    <option value="3rd" {{ $violation->offense == '3rd' ? 'selected' : '' }}>3rd Offense</option>
                </select>
            </div>
            
            <!-- Penalty Selection -->
            <div class="form-group">
                <label for="penalty">Penalty</label>
                <select name="penalty" id="penalty" class="form-control" required>
                    <option value="W" {{ $violation->penalty == 'W' ? 'selected' : '' }}>Warning</option>
                    <option value="VW" {{ $violation->penalty == 'VW' ? 'selected' : '' }}>Verbal Warning</option>
                    <option value="WW" {{ $violation->penalty == 'WW' ? 'selected' : '' }}>Written Warning</option>
                    <option value="Pro" {{ $violation->penalty == 'Pro' ? 'selected' : '' }}>Probation</option>
                    <option value="Exp" {{ $violation->penalty == 'Exp' ? 'selected' : '' }}>Expulsion</option>
                </select>
            </div>
            
            <!-- Consequence Input -->
            <div class="form-group">
                <label for="consequence">Consequence</label>
                <textarea name="consequence" id="consequence" class="form-control" rows="3" required>{{ $violation->consequence }}</textarea>
            </div>
            
            <!-- Status Selection -->
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="pending" {{ $violation->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="resolved" {{ $violation->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="cancelled" {{ $violation->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            
            <!-- Form Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('educator.violation') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Violation</button>
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
                        
                        if (Array.isArray(data)) {
                            if (data.length === 0) {
                                console.log('No violation types found for this category');
                                return;
                            }
                            
                            data.forEach(type => {
                                console.log('Adding option:', type);
                                const option = document.createElement('option');
                                option.value = type.id;
                                option.textContent = type.name;
                                violationTypeSelect.appendChild(option);
                            });
                        } else {
                            console.error('Data is not an array:', data);
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