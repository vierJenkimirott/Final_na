    document.addEventListener('DOMContentLoaded', function() {
    // Initialize all severity selects
    document.querySelectorAll('.severity-select').forEach(select => {
        updateOffensesAndPenalties(select);
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

        // Create a simple, modern confirmation dialog
        const confirmDialog = document.createElement('div');
        confirmDialog.className = 'modal fade';
        confirmDialog.id = 'saveConfirmModal';
        confirmDialog.setAttribute('tabindex', '-1');
        confirmDialog.setAttribute('aria-labelledby', 'saveConfirmModalLabel');
        confirmDialog.setAttribute('aria-hidden', 'true');

        confirmDialog.innerHTML = `
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center p-4">
                        <h5 class="mb-3">Save Changes?</h5>
                        <div class="d-flex justify-content-center">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-success" id="confirmSaveBtn">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(confirmDialog);

        // Initialize the Bootstrap modal
        const modal = new bootstrap.Modal(document.getElementById('saveConfirmModal'));
        modal.show();

        // Handle the confirmation
        document.getElementById('confirmSaveBtn').addEventListener('click', function() {
            // Show loading state
            saveButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            saveButton.disabled = true;

            // Hide and remove the modal
            modal.hide();

            // Submit the form
            form.submit();
        });

        // Remove the modal from the DOM when it's closed
        document.getElementById('saveConfirmModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('saveConfirmModal').remove();
        });
    });
});
