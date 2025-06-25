@extends('layouts.educator')

@section('title', 'Student Violation Manual')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/student/student-manual.css') }}">
@endsection

@section('content')
    <div class="container">
        <!-- Remove the first Edit Manual button -->
        
        <div class="main-heading">
            <img src="{{ asset('images/PN-logo-removebg-preview.png') }}" alt="">
            <h1 class="fw-bold">Student Code of Conduct</h1>
        </div>
        <h2>Empowering Responsible Center Life Through Awareness and Discipline.</h2>
        <p>Welcome, students! This code of conduct helps you understand the rules and expectations while living at the center. Staying informed is the first step to success and harmony!</p>

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
            <!-- Add a class for styling in the external CSS -->
            <div class="violation-header">
                <h3>Violation Categories and Penalties</h3>
                @if(auth()->user()->roles->where('name', 'educator')->isNotEmpty())
                <a href="{{ route('educator.manual.edit') }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit 
                </a>
                @endif
            </div>
            
            @foreach($categories as $index => $category)
            <div class="category-section">
                <h4>{{ $index + 1 }}. {{ $category->category_name }}</h4>
                <table class="table table-bordered">
                <thead>
    <tr>
        <th style="width: 8%; text-align: center;">#</th>
        <th style="width: 65%; text-align: left;">Violation Name</th>
        <th style="width: 25%; text-align: center;">Severity</th>
    </tr>
</thead>
                    <tbody>
                        @foreach($category->violationTypes->unique('violation_name') as $typeIndex => $type)
                        <tr>
                            <td>{{ $index + 1 }}.{{ $typeIndex + 1 }}</td>
                            <td>{{ $type->violation_name }}</td>
                            <td>
                                @switch(strtolower($type->severityRelation->severity_name ?? ''))
                                    @case('low')
                                        Low
                                        @break
                                    @case('medium')
                                        Medium
                                        @break
                                    @case('high')
                                        High
                                        @break
                                    @case('very high')
                                        Very High
                                        @break
                                    @default
                                        {{ $type->severityRelation->severity_name ?? 'Unknown' }}
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
@endsection






