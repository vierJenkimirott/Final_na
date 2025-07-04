@extends('layouts.educator')

@section('title', 'Edit Student Violation Manual')

@section('css')
<link rel="stylesheet" href="{{ asset('css/educator/edit-manual.css') }}">
@endsection

@section('content')
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Student Violation Manual</h2>
        <a href="{{ route('student-manual') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Manual
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('educator.manual.update') }}" method="POST" id="manualForm">
        @csrf

        <!-- Existing Categories -->
        @foreach ($categories as $index => $category)
            <div class="category-section">
                <div class="category-header">
                    <input type="hidden" name="categories[{{ $loop->index }}][id]" value="{{ $category->id }}">
                    <div class="d-flex align-items-center">
                        <span class="category-number me-3 fw-bold">{{ $loop->iteration }}.</span>
                        <h4 class="mb-0 d-flex align-items-center">
                            <input type="text" class="category-name-input"
                                   name="categories[{{ $loop->index }}][category_name]"
                                   value="{{ $category->category_name }}" required>
                        </h4>
                        <button type="button" class="btn btn-danger btn-sm delete-category ms-3"
                                data-category-id="{{ $category->id }}"
                                onclick="deleteCategory(this)">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>

                <table class="table table-bordered w-100" style="table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="width: 5%; text-align: center;">#</th>
                            <th style="width: 40%; text-align: left;">Violation Name</th>
                            <th style="width: 10%; text-align: center;">Severity</th>
                            <th style="width: 10%; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="violations-container-{{ $category->id }}">
                        <!-- Existing Violations -->
                        @foreach ($category->violationTypes as $typeIndex => $type)
                            <tr>
                                <input type="hidden" name="categories[{{ $loop->parent->index }}][violationTypes][{{ $loop->index }}][id]" value="{{ $type->id }}">
                                <td style="text-align: center;">{{ $loop->iteration }}</td>
                                <td class="editable-cell" style="text-align: left;">
                                    <textarea name="categories[{{ $loop->parent->index }}][violationTypes][{{ $loop->index }}][violation_name]"
                                              class="violation-textarea" maxlength="500" required>{{ $type->violation_name }}</textarea>
                                    <div class="char-counter small text-muted">
                                        <span class="current-count">{{ strlen($type->violation_name) }}</span>/500 characters
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    @switch(strtolower($type->severityRelation->severity_name ?? ''))
                                        @case('low')
                                            <span class="severity-text severity-low">Low</span>
                                            @break
                                        @case('medium')
                                            <span class="severity-text severity-medium">Medium</span>
                                            @break
                                        @case('high')
                                            <span class="severity-text severity-high">High</span>
                                            @break
                                        @case('very high')
                                            <span class="severity-text severity-very-high">Very High</span>
                                            @break
                                        @default
                                            <span class="severity-text severity-unknown">{{ $type->severityRelation->severity_name ?? 'Unknown' }}</span>
                                    @endswitch
                                    <input type="hidden" name="categories[{{ $loop->parent->index }}][violationTypes][{{ $loop->index }}][severity_id]" value="{{ $type->severity_id }}">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm delete-violation"
                                            data-violation-id="{{ $type->id }}"
                                            onclick="deleteViolation(this)">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Remove this button section from each category -->
                <div class="d-flex justify-content-end align-items-center mt-3">
                    <button type="button" class="btn btn-sm btn-outline-success add-violation-btn"
                            data-category-index="{{ $loop->index }}" data-category-id="{{ $category->id }}">
                        <i class="fas fa-plus"></i> Add Violation to {{ $category->category_name }}
                    </button>
                </div>
            </div>
        @endforeach

        <!-- Add New Category Section -->
        <div class="add-category-section">
            <div class="section-title text-center">
                <h4>ADD NEW CATEGORY</h4>
            </div>

            <div class="card-section">
                <div class="form-group mb-3">
                    <label for="new_category_name" class="form-label">Category Name:</label>
                    <input type="text" id="new_category_name" class="form-control"
                           name="new_category[name]" placeholder="Enter new category name">
                </div>

                <div class="form-group mb-3">
                    <label for="new_violation_name" class="form-label">Violation Name:</label>
                    <textarea id="new_violation_name" class="form-control violation-textarea"
                              name="new_category[violations][0][name]"
                              placeholder="Enter violation name" rows="3" maxlength="500"></textarea>
                    <div class="char-counter small text-muted">
                        <span class="current-count">0</span>/500 characters
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="new_violation_severity" class="form-label">Severity:</label>
                    <select id="new_violation_severity" class="form-control severity-select"
                            name="new_category[violations][0][severity_id]"
                            data-offenses-field="new_category[violations][0][offenses]"
                            data-penalties-field="new_category[violations][0][penalties_text]"
                            onchange="updateOffensesAndPenalties(this)">
                        <option value="1">Low</option>
                        <option value="2">Medium</option>
                        <option value="3">High</option>
                        <option value="4">Very High</option>
                    </select>
                </div>

                <div id="empty-category-alert" class="empty-form-alert">
                    Please enter both a category name and a violation name, or leave this section empty.
                </div>
            </div>
        </div>

        <div class="action-buttons d-flex justify-content-between">
            <a href="{{ route('educator.manual') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-success" id="saveButton">
                <i class="fas fa-save"></i> Save All Changes
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script src="{{ asset('js/toast.js') }}"></script>
<script src="{{ asset('js/edit-manual.js') }}"></script>
<script src="{{ asset('js/edit-manual-init.js') }}"></script>
@endpush
@endsection
