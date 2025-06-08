@extends('layouts.student')

@section('title', 'Student Violation Manual')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/student-manual.css') }}">
    <style>
        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background: #f0fdf4; /* bg-green-100 equivalent */
            border: 1px solid #4ade80; /* border-green-400 equivalent */
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideIn 0.3s ease-out;
            min-width: 300px;
            max-width: 400px;
        }

        .toast.success {
            border-left: 4px solid #22c55e; /* border-green-500 equivalent */
        }

        .toast.error {
            border-left: 4px solid #dc3545;
        }

        .toast.info {
            border-left: 4px solid #17a2b8;
        }

        .toast.warning {
            border-left: 4px solid #ffc107;
        }

        .toast i {
            font-size: 1.25rem;
            color: #166534; /* text-green-800 equivalent */
        }

        .toast.success i {
            color: #28a745;
        }

        .toast.error i {
            color: #dc3545;
        }

        .toast.info i {
            color: #17a2b8;
        }

        .toast.warning i {
            color: #ffc107;
        }

        .toast-message {
            flex: 1;
            color: #166534; /* text-green-800 equivalent */
            font-size: 0.9rem;
            font-weight: 500;
        }

        .toast-close {
            background: none;
            border: none;
            color: #166534; /* text-green-800 equivalent */
            cursor: pointer;
            padding: 0.25rem;
            font-size: 1rem;
            line-height: 1;
            opacity: 0.7;
        }

        .toast-close:hover {
            opacity: 1;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
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
                            <th>#</th>
                            <th>Violation Name</th>
                            <th>Severity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category->violationTypes as $typeIndex => $type)
                        <tr>
                            <td>{{ $index + 1 }}.{{ $typeIndex + 1 }}</td>
                            <td>{{ $type->violation_name }}</td>
                            <td>
                                @switch($type->default_penalty)
                                    @case('W')
                                        Low
                                        @break
                                    @case('VW')
                                        Medium
                                        @break
                                    @case('WW')
                                        High
                                        @break
                                    @case('Pro')
                                        High
                                        @break
                                    @case('Exp')
                                        Very High
                                        @break
                                    @default
                                        Medium
                                @endswitch
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