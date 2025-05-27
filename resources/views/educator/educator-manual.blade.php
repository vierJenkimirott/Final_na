@extends('layouts.educator')

@section('title', 'Student Violation Manual')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/student-manual.css') }}">
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

        <div class="violation-table">
            <!-- Add a class for styling in the external CSS -->
            <div class="violation-header">
                <h3>Violation Categories and Penalties</h3>
                @if(auth()->user()->roles->where('name', 'educator')->isNotEmpty())
                <a href="{{ route('educator.manual.edit') }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Manual
                </a>
                @endif
            </div>
            
            @foreach($categories as $index => $category)
            <div class="category-section">
                <h4>{{ $index + 1 }}. {{ $category->category_name }}</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Violation Name</th>
                            <th>Severity</th>
                            <th>Offenses</th>
                            <th>Penalties</th>
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
                            <td>
                                1st, 2nd, 3rd
                            </td>
                            <td>
                                @switch($type->default_penalty)
                                    @case('W')
                                        1st: Warning<br>
                                        2nd: Verbal Warning<br>
                                        3rd: Written Warning
                                        @break
                                    @case('VW')
                                        1st: Verbal Warning<br>
                                        2nd: Written Warning<br>
                                        3rd: Probation
                                        @break
                                    @case('WW')
                                        1st: Written Warning<br>
                                        2nd: Probation<br>
                                        3rd: Expulsion
                                        @break
                                    @case('Pro')
                                        1st: Probation<br>
                                        2nd: Expulsion
                                        @break
                                    @case('Exp')
                                        Immediate Expulsion
                                        @break
                                    @default
                                        {{ $type->default_penalty }}
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






