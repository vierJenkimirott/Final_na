@extends('layouts.student')

@section('title', 'Student Violation Manual')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/student.css') }}">
    <style>
        .manual-section {
            margin-bottom: 30px;
        }
        .manual-section h3 {
            color: #3a3a3a;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .violation-table {
            width: 100%;
            border-collapse: collapse;
        }
        .violation-table th, .violation-table td {
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
        }
        .violation-table thead {
            background-color: #f8f9fa;
        }
        .violation-level-1 {
            background-color: #fff3cd;
        }
        .violation-level-2 {
            background-color: #ffe5d0;
        }
        .violation-level-3 {
            background-color: #f8d7da;
        }
    </style>
@endsection

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h2>Student Violation Manual</h2>
        </div>
        <div class="card-body">
            <div class="manual-section">
                <h3>Introduction</h3>
                <p>This manual outlines the expected conduct and behavior of all students. It details various violations and their corresponding consequences to ensure a safe, respectful, and productive learning environment.</p>
            </div>

            <div class="manual-section">
                <h3>Violation Levels</h3>
                <p>Violations are categorized into three levels based on their severity:</p>
                <ul>
                    <li><strong>Level 1:</strong> Minor infractions that require correction but do not severely impact the learning environment.</li>
                    <li><strong>Level 2:</strong> Moderate infractions that disrupt the learning environment or show disrespect to others.</li>
                    <li><strong>Level 3:</strong> Serious infractions that significantly disrupt the learning environment, pose safety risks, or violate important school policies.</li>
                </ul>
            </div>

            <div class="manual-section">
                <h3>Common Violations and Consequences</h3>
                <table class="violation-table">
                    <thead>
                        <tr>
                            <th>Violation</th>
                            <th>Level</th>
                            <th>Consequence</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="violation-level-1">
                            <td>Tardiness</td>
                            <td>1</td>
                            <td>Warning, point deduction</td>
                        </tr>
                        <tr class="violation-level-1">
                            <td>Dress code violation</td>
                            <td>1</td>
                            <td>Warning, point deduction</td>
                        </tr>
                        <tr class="violation-level-2">
                            <td>Disruptive behavior in class</td>
                            <td>2</td>
                            <td>Point deduction, parent notification</td>
                        </tr>
                        <tr class="violation-level-2">
                            <td>Unauthorized use of electronic devices</td>
                            <td>2</td>
                            <td>Device confiscation, point deduction</td>
                        </tr>
                        <tr class="violation-level-3">
                            <td>Academic dishonesty</td>
                            <td>3</td>
                            <td>Zero on assignment, parent conference, possible suspension</td>
                        </tr>
                        <tr class="violation-level-3">
                            <td>Bullying or harassment</td>
                            <td>3</td>
                            <td>Suspension, mandatory counseling, parent conference</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="manual-section">
                <h3>Reporting Process</h3>
                <p>Violations are reported by staff members and processed through the school's behavior management system. Students will be notified of reported violations and have the opportunity to discuss them with appropriate staff members.</p>
            </div>

            <div class="manual-section">
                <h3>Appeal Process</h3>
                <p>Students who believe a violation was incorrectly reported may appeal through the following process:</p>
                <ol>
                    <li>Submit a written appeal to the student affairs office within 3 days of the violation notification.</li>
                    <li>Meet with the designated staff member to discuss the appeal.</li>
                    <li>If necessary, a review committee will evaluate the appeal and make a final determination.</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
