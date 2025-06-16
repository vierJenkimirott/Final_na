@extends('layouts.student')

@section('title', 'Student Violation Manual')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/student/student-manual.css') }}">
    <style>
        .category-section table {
            width: 100% !important;
            table-layout: fixed !important;
        }
        .category-section h4 {
            font-size: 1.2rem !important;
            font-weight: 600 !important;
            line-height: 1.4 !important;
        }
        .table th {
            font-size: 1rem !important;
        }
        .table td {
            font-size: 1rem !important;
            vertical-align: middle !important;
            word-wrap: break-word !important;
            white-space: normal !important;
        }
        /* Specific styles for the numbering column */
        .category-section table th:first-child,
        .category-section table td:first-child {
            width: 8% !important; /* Made slightly larger than 5% for better fit with 1.10+ numbers*/
            text-align: center !important;
        }
        /* Adjusting other column widths accordingly */
        .category-section table th:nth-child(2),
        .category-section table td:nth-child(2) {
            width: 62% !important;
        }
        .category-section table th:nth-child(3),
        .category-section table td:nth-child(3) {
            width: 30% !important;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="main-heading">
            <img src="{{ asset('images/PN-logo-removebg-preview.png') }}" alt="" style="width: 200px; height: 200px; display: block; margin: auto;">
            <h1 style="text-align: center;">Student Code of Conduct</h1>
        </div>
        <h2 style="text-align: center;">Empowering Responsible Center Life Through Awareness and Discipline.</h2>
        <p class="fs-5 mb-5">Welcome, students! This code of conduct helps you understand the rules and expectations while living at the center. Staying informed is the first step to success and harmony!</p>

        <div class="penalty-system-explanation mt-5">
            <h3 class="text-sm">Penalty Rules Based on Infraction Count and Severity</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 20%;" class="text-sm">Infraction Count</th>
                            <th style="width: 20%;" class="text-sm">
                                <span class="badge" color: #000;">🟡 Low</span>
                            </th>
                            <th style="width: 20%;" class="text-sm">
                                <span class="badge" color: #fff;">🌸 Medium</span>
                            </th>
                            <th style="width: 20%;" class="text-sm">
                                <span class="badge" color: #fff;">🟠 High</span>
                            </th>
                            <th style="width: 20%;" class="text-sm">
                                <span class="badge" color: #fff;">🔴 Very High</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-sm"><strong>1st Infraction</strong></td>
                            <td class="text-sm">Verbal Warning (VW)</td>
                            <td class="text-sm">Written Warning (WW)</td>
                            <td class="text-sm">Probation (P)</td>
                            <td class="text-sm">Termination (T)</td>
                        </tr>
                        <tr>
                            <td class="text-sm"><strong>2nd Infraction</strong></td>
                            <td class="text-sm">Written Warning (WW)</td>
                            <td class="text-sm">Probation (P)</td>
                            <td class="text-sm">Termination (T)</td>
                            <td class="text-sm"></td>
                        </tr>
                        <tr>
                            <td class="text-sm"><strong>3rd Infraction</strong></td>
                            <td class="text-sm">Probation (P)</td>
                            <td class="text-sm">Termination (T)</td>
                            <td class="text-sm"></td>
                            <td class="text-sm"></td>
                        </tr>
                        <tr>
                            <td class="text-sm"><strong>4th Infraction</strong></td>
                            <td class="text-sm">Termination (T)</td>
                            <td class="text-sm"></td>
                            <td class="text-sm"></td>
                            <td class="text-sm"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="important-reminders mt-4">
                <div class="bg-light p-4 rounded-lg border">
                    <h3 class="text-sm mb-3">🔑 Important Things to Remember About Penalties</h3>
                    
                    <table class="table table-bordered table-sm mb-0">
                        <tbody>
                            <tr>
                                <td style="width: 30%;" class="align-middle bg-white">
                                    <h4 class="mb-0 small">🧠 Penalties get stricter with every new infraction.</h4>
                                </td>
                                <td class="align-middle text-sm bg-white">
                                    The 1st time you make a mistake, the penalty is lighter. The 2nd time, it gets stronger. The 3rd time, it's even stronger, and so on.
                                </td>
                            </tr>
                            <tr>
                                <td class="align-middle bg-white">
                                    <h4 class="mb-0 small">⚠️ More serious violations get tougher penalties faster.</h4>
                                </td>
                                <td class="align-middle text-sm bg-white">
                                    If you do something more serious, even if it's your first time, the penalty will be stronger than for less serious mistakes.
                                </td>
                            </tr>
                            <tr>
                                <td class="align-middle bg-white">
                                    <h4 class="mb-0 small">📌 Each penalty depends on the specific violation's seriousness AND how many times it happened.</h4>
                                </td>
                                <td class="align-middle text-sm bg-white">
                                    So, the penalty for your 2nd violation depends on how serious that violation is — it's not just about how many times you've made mistakes overall.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="violation-table">
            <h3>Violation Categories and Penalties</h3>
            
            @foreach($categories as $index => $category)
            <div class="category-section">
                <h4>{{ $index + 1 }}. {{ $category->category_name }}</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 10%; text-align: center;">#</th>
                            <th style="width: 60%;">Violation Name</th>
                            <th style="width: 30%; text-align: center;">Severity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category->violationTypes as $typeIndex => $type)
                        <tr>
                            <td>{{ $index + 1 }}.{{ $typeIndex + 1 }}</td>
                            <td>{{ $type->violation_name }}</td>
                            <td class="penalty-cell">
                                @if($type->severityRelation)
                                    {{ $type->severityRelation->severity_name }}
                                @elseif($type->severity)
                                    {{ $type->severity }}
                                @else
                                    Medium
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Add toast container at the top of the body -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        // Toast notification function
        function showSuccessToast(message) {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'toast success';
            
            // Customize message based on content
            let icon, customMessage;
            if (message.toLowerCase().includes('manual updated')) {
                icon = 'fas fa-book-open';
                customMessage = '📚 Manual has been updated successfully!';
            } else if (message.toLowerCase().includes('violation deleted')) {
                icon = 'fas fa-trash-alt';
                customMessage = '🗑️ Violation record has been removed successfully!';
            } else {
                icon = 'fas fa-check-circle';
                customMessage = message;
            }
            
            toast.innerHTML = `
                <i class="${icon}"></i>
                <div class="toast-message">${customMessage}</div>
                <button class="toast-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            toastContainer.appendChild(toast);
            
            // Auto-remove after 2.5 seconds
            setTimeout(() => {
                toast.style.animation = 'fadeOut 0.3s ease-out forwards';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, 2500);
        }

        // Example usage:
        // showSuccessToast('Manual updated successfully');
        // showSuccessToast('Violation deleted successfully.');
    </script>
@endsection