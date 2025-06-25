@extends('layouts.educator')

@section('title', 'Violation History')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-danger text-white">
            @php
    $penaltyPriority = ['Exp','T','Pro','P','WW','W','VW','V'];
    $studentStatus = null;
    foreach($penaltyPriority as $code){
        if($violations->contains('penalty',$code)){
            $studentStatus = match($code){
                'Exp','T' => 'Expulsion',
                'Pro','P' => 'Probation',
                'WW','W' => 'Written Warning',
                'VW','V' => 'Verbal Warning',
            };
            break;
        }
    }

@endphp
<h4 class="mb-0">Violation History: {{ $student->name }}
@if($studentStatus)
    <br><small class="text-light">Status: {{ $studentStatus }}</small>
@endif
</h4>
        </div>
        <div class="card-body">
            @if($violations->isEmpty())
                <div class="alert alert-success">No violations found for this student.</div>
            @else
                <div class="table-responsive"><table class="table table-bordered table-striped table-sm align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Severity</th>
                            <th>Infraction Count</th>
                            <th>Penalty</th>
                            <th>Consequence</th>
                            <th>Status</th>
                            <th>Date Resolved</th>
                            <th>Incident Date/Time</th>
                            <th>Place</th>
                            <th>Details</th>
                            <th>Prepared By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($violations as $violation)
                        <tr>
                            <td>{{ $violation->violation_date }}</td>
                            <td>{{ $violation->violationType->violation_name ?? 'N/A' }}</td>
                            <td>{{ $violation->severity ?? ($violation->violationType->severity ?? 'N/A') }}</td>
                            <td>{{ $violation->infraction_count ?? 'N/A' }}</td>
                            <td>{{ $violation->penalty ?? 'N/A' }}</td>
                            <td>{{ $violation->consequence ?? 'N/A' }}</td>
                            <td>
                                <span class="badge {{ $violation->status === 'active' ? 'bg-danger' : 'bg-secondary' }}">
                                    {{ ucfirst($violation->status) }}
                                </span>
                            </td>
                            <td>{{ $violation->status === 'resolved' ? optional($violation->updated_at)->format('Y-m-d H:i:s') : 'N/A' }}</td>
                            <td>{{ $violation->incident_datetime ?? 'N/A' }}</td>
                            <td>{{ $violation->incident_place ?? 'N/A' }}</td>
                            <td>{{ $violation->incident_details ?? 'N/A' }}</td>
                            <td>{{ $violation->prepared_by ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table></div>
            @endif
            <a href="{{ url('/educator/students') }}" class="btn btn-secondary">Back to Students List</a>
        </div>
    </div>
</div>
@endsection
