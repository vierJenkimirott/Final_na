@extends('layouts.educator')

@section('content')
    <!-- Main Container -->
    <div class="container">
        <h2>Violation Details</h2>
        
        <!-- Violation Details Section -->
        <div class="violation-details">
            <!-- Student Information -->
            <div class="detail-row">
                <div class="detail-label">Student:</div>
                <div class="detail-value">
                    @if($violation->student)
                        {{ $violation->student->fname }} {{ $violation->student->lname }}
                    @else
                        <span class="text-danger">Student data not available (ID: {{ $violation->student_id }})</span>
                    @endif
                </div>
            </div>
            
            <!-- Violation Date -->
            <div class="detail-row">
                <div class="detail-label">Violation Date:</div>
                <div class="detail-value">{{ \Carbon\Carbon::parse($violation->violation_date)->format('M d, Y') }}</div>
            </div>
            
            <!-- Category Information -->
            <div class="detail-row">
                <div class="detail-label">Category:</div>
                <div class="detail-value">
                    @if($violation->offenseCategory)
                        {{ $violation->offenseCategory->category_name }}
                    @else
                        <span class="text-danger">Category not available</span>
                    @endif
                </div>
            </div>
            
            <!-- Violation Type -->
            <div class="detail-row">
                <div class="detail-label">Violation Type:</div>
                <div class="detail-value">
                    @if($violation->violationType)
                        {{ $violation->violationType->violation_name }}
                    @else
                        <span class="text-danger">Violation type not available</span>
                    @endif
                </div>
            </div>
            
            <!-- Severity Level -->
            <div class="detail-row">
                <div class="detail-label">Severity:</div>
                <div class="detail-value {{ strtolower($violation->severity) }}">{{ $violation->severity }}</div>
            </div>
            
            <!-- Offense Number -->
            <div class="detail-row">
                <div class="detail-label">Offense:</div>
                <div class="detail-value">{{ $violation->offense }}</div>
            </div>
            
            <!-- Penalty Information -->
            <div class="detail-row">
                <div class="detail-label">Penalty:</div>
                <div class="detail-value">
                    @switch($violation->penalty)
                        @case('W')
                            Warning
                            @break
                        @case('VW')
                            Verbal Warning
                            @break
                        @case('WW')
                            Written Warning
                            @break
                        @case('Pro')
                            Probation
                            @break
                        @case('Exp')
                            Expulsion
                            @break
                    @endswitch
                </div>
            </div>
            
            <!-- Consequence Details -->
            <div class="detail-row">
                <div class="detail-label">Consequence:</div>
                <div class="detail-value">{{ $violation->consequence }}</div>
            </div>
            
            <!-- Violation Status -->
            <div class="detail-row">
                <div class="detail-label">Status:</div>
                <div class="detail-value {{ strtolower($violation->status) }}">{{ ucfirst($violation->status) }}</div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ route('educator.violation') }}" class="btn btn-secondary">Back to List</a>
            <a href="{{ route('educator.edit-violation', ['id' => $violation->id]) }}" class="btn btn-primary">Edit Violation</a>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        /* Container Layout */
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Violation Details Card */
        .violation-details {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        /* Detail Row Layout */
        .detail-row {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        /* Label Styling */
        .detail-label {
            width: 150px;
            font-weight: bold;
            color: #666;
        }
        
        /* Value Styling */
        .detail-value {
            flex: 1;
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
        }
        
        /* Primary Button */
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        /* Secondary Button */
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        /* Button Hover Effect */
        .btn:hover {
            opacity: 0.9;
        }
        
        /* Severity Level Colors */
        .low { color: #28a745; }      /* Green */
        .medium { color: #ffc107; }   /* Yellow */
        .high { color: #fd7e14; }     /* Orange */
        .very-high { color: #dc3545; } /* Red */
        
        /* Status Colors */
        .pending { color: #ffc107; }   /* Yellow */
        .resolved { color: #28a745; }  /* Green */
        .cancelled { color: #dc3545; } /* Red */
    </style>
@endsection 