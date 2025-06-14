    document.addEventListener('DOMContentLoaded', function() {
    // Initialize all severity selects
    document.querySelectorAll('.severity-select').forEach(select => {
        // Removed updateOffensesAndPenalties(select);
    });

    // Removing unused tracking variables for new violations
    
    // Add character counter functionality
    document.querySelectorAll('.violation-textarea').forEach(textarea => {
        textarea.addEventListener('input', function() {
            const counter = this.parentNode.querySelector('.char-counter .current-count');
            if (counter) {
                const currentLength = this.value.length;
                counter.textContent = currentLength;

                // Add warning class if approaching limit
                if (currentLength > 450) {
                    counter.classList.add('char-limit-warning');
                } else {
                    counter.classList.remove('char-limit-warning');
                }
            }
        });
    });

    // Add confirmation before form submission
    const form = document.getElementById('manualForm');
    const saveButton = document.getElementById('saveButton');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Remove any highlighting from previous validation attempts
        document.querySelectorAll('.border-danger').forEach(el => {
            el.classList.remove('border-danger');
        });

        document.querySelectorAll('.text-danger').forEach(el => {
            el.remove();
        });

        let hasValidationErrors = false;

        // Validate new category
        const newCategoryName = document.getElementById('new_category_name');
        const newViolationName = document.getElementById('new_violation_name');
        const emptyCategoryAlert = document.getElementById('empty-category-alert');

        // Reset alerts
        emptyCategoryAlert.style.display = 'none';

        // Check if new category has a name but no violation name
        if (newCategoryName.value.trim() !== '' && newViolationName.value.trim() === '') {
            hasValidationErrors = true;
            emptyCategoryAlert.style.display = 'block';
            newViolationName.classList.add('border-danger');

            // Add a small error message
            if (!newViolationName.parentNode.querySelector('.text-danger')) {
                const errorMsg = document.createElement('div');
                errorMsg.className = 'text-danger small mt-1';
                errorMsg.textContent = 'Please enter a violation name or leave the category name empty';
                newViolationName.parentNode.appendChild(errorMsg);
            }
        }

        // Check if violation name has a value but no category name
        if (newCategoryName.value.trim() === '' && newViolationName.value.trim() !== '') {
            hasValidationErrors = true;
            emptyCategoryAlert.style.display = 'block';
            newCategoryName.classList.add('border-danger');

            // Add a small error message
            if (!newCategoryName.parentNode.querySelector('.text-danger')) {
                const errorMsg = document.createElement('div');
                errorMsg.className = 'text-danger small mt-1';
                errorMsg.textContent = 'Please enter a category name or leave the violation name empty';
                newCategoryName.parentNode.appendChild(errorMsg);
            }
        }

        if (hasValidationErrors) {
            // Scroll to the first error
            const firstError = document.querySelector('.border-danger') || document.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        // Show loading state
        saveButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
        saveButton.disabled = true;

        // Gather all form data
        const formData = new FormData(form);
        const jsonData = {};
        formData.forEach((value, key) => {
            // Handle array-like inputs (e.g., categories[][id])
            const matches = key.match(/(\w+)\[(\d+)\](?:\[(\w+)\](?:\[(\d+)\](?:\[(\w+)\])?)?)?/);
            if (matches) {
                const field = matches[1];
                const index1 = matches[2];
                const subField1 = matches[3];
                const index2 = matches[4];
                const subField2 = matches[5];

                if (!jsonData[field]) {
                    jsonData[field] = [];
                }
                if (!jsonData[field][index1]) {
                    jsonData[field][index1] = {};
                }
                if (subField1 && !jsonData[field][index1][subField1]) {
                    jsonData[field][index1][subField1] = [];
                }

                if (subField2) {
                    if (!jsonData[field][index1][subField1][index2]) {
                        jsonData[field][index1][subField1][index2] = {};
                    }
                    jsonData[field][index1][subField1][index2][subField2] = value;
                } else if (subField1) {
                    jsonData[field][index1][subField1] = value; // This should be for direct sub-fields, not nested arrays
                } else {
                     jsonData[field][index1] = value; // This handles category_name
                }

                // Correct handling for nested arrays like violationTypes
                if (subField1 === 'violationTypes' && index2 !== undefined) {
                    if (!jsonData[field][index1][subField1][index2]) {
                        jsonData[field][index1][subField1][index2] = {};
                    }
                    jsonData[field][index1][subField1][index2][subField2] = value;
                } else if (subField1) {
                     if (index2 === undefined) { // For cases like new_category[violations][0][name]
                        jsonData[field][index1][subField1] = value;
                    } else {
                        // This is for nested violation details, like violation_name, default_penalty
                        if (!jsonData[field][index1][subField1][index2]) {
                            jsonData[field][index1][subField1][index2] = {};
                        }
                        jsonData[field][index1][subField1][index2][subField2] = value;
                    }
                } else {
                    jsonData[key] = value;
                }

            } else {
                jsonData[key] = value;
            }
        });
        
        // Special handling for new_category, as it's a single object with nested violations
        const newCategoryNameInput = document.getElementById('new_category_name');
        if (newCategoryNameInput && newCategoryNameInput.value.trim() !== '') {
            const newCategoryData = {
                category_name: newCategoryNameInput.value.trim(),
                violationTypes: []
            };

            // Collect all new_category violations
            const newViolationRows = form.querySelectorAll('.new-violation-row');
            if (newViolationRows.length > 0) {
                newViolationRows.forEach(row => {
                    const violationNameInput = row.querySelector('[name^="new_category[violations]"][name$="[name]"]');
                    const severitySelect = row.querySelector('[name^="new_category[violations]"][name$="[default_penalty]"]');

                    if (violationNameInput && violationNameInput.value.trim() !== '') {
                        newCategoryData.violationTypes.push({
                            violation_name: violationNameInput.value.trim(),
                            default_penalty: severitySelect ? severitySelect.value : 'W'
                        });
                    }
                });
            } else {
                // Handle the initial new violation if no dynamic rows are added
                const initialNewViolationName = document.getElementById('new_violation_name');
                const initialNewViolationSeverity = document.getElementById('new_violation_severity');
                if (initialNewViolationName && initialNewViolationName.value.trim() !== '') {
                    newCategoryData.violationTypes.push({
                        violation_name: initialNewViolationName.value.trim(),
                        default_penalty: initialNewViolationSeverity ? initialNewViolationSeverity.value : 'W'
                    });
                }
            }

            // Ensure categories is an array, even if empty initially from existing categories
            if (!jsonData.categories) {
                jsonData.categories = [];
            }
            jsonData.categories.push(newCategoryData);
        }
        
        // Remove the old new_category fields from jsonData if they were incorrectly added by the generic parser
        delete jsonData.new_category;

        // Send data using Fetch API
        fetch(form.action, {
            method: form.method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(jsonData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessToast(data.message);
                // Redirect to the specified URL from the backend after a short delay
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 2500);
            } else {
                showErrorToast(data.message || 'Failed to save manual changes');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorToast('An error occurred while saving the manual');
        })
        .finally(() => {
            // Reset button state
            saveButton.innerHTML = 'Save Changes';
            saveButton.disabled = false;
        });
    });
});
