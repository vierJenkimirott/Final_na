@extends('layouts.educator')

@section('content')
    <div class="header-controls">
        <h2>Rewards Management</h2>
        <div class="button-group">
            <a href="{{ route('rewards.add') }}" class="btn btn-primary">Add New Reward</a>
            <form action="{{ route('rewards.generate-monthly-points') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to generate 100 points for all students this month?')">
                    Generate Monthly Points
                </button>
            </form>
        </div>
    </div>

    <div class="rewards-container">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Reward Type</th>
                                <th>Points</th>
                                <th>Description</th>
                                <th>Date Awarded</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rewards as $reward)
                            <tr>
                                <td>{{ $reward->student_name }}</td>
                                <td>{{ $reward->reward_type }}</td>
                                <td>{{ $reward->points }}</td>
                                <td>{{ $reward->description }}</td>
                                <td>{{ $reward->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('rewards.edit', $reward->id) }}" class="btn btn-sm btn-info">Edit</a>
                                    <form action="{{ route('rewards.destroy', $reward->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this reward?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection