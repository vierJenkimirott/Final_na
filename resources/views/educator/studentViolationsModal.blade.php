@props(['violations', 'student'])
<div class="modal fade" id="studentViolationsModal" tabindex="-1" aria-labelledby="studentViolationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="studentViolationsModalLabel">
                    <i class="fas fa-list-alt me-2"></i>Violation History for {{ $student->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($violations->isEmpty())
                    <div class="alert alert-info">No violation history found for this student.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Violation Type</th>
                                    <th>Penalty</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($violations as $violation)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($violation->violation_date)->format('M d, Y') }}</td>
                                        <td>{{ $violation->violationType->violation_name ?? 'N/A' }}</td>
                                        <td>{{ $violation->penalty }}</td>
                                        <td>{{ ucfirst($violation->status) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
