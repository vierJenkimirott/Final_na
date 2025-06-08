// Function to update offenses and penalties based on severity
// This function is no longer needed as offenses and penalties fields have been removed from the view.
/*
function updateOffensesAndPenalties(selectElement) {
    const severity = selectElement.value;
    const offensesFieldName = selectElement.getAttribute('data-offenses-field');
    const penaltiesFieldName = selectElement.getAttribute('data-penalties-field');
    
    // Find the corresponding offenses and penalties fields
    const offensesField = document.querySelector(`[name="${offensesFieldName}"]`);
    const penaltiesField = document.querySelector(`[name="${penaltiesFieldName}"]`);
    
    if (!offensesField || !penaltiesField) return;
    
    // Set values based on severity
    switch (severity) {
        case 'W': // Low
            offensesField.value = '1st, 2nd, 3rd';
            penaltiesField.value = '1st: Warning, 2nd: Verbal Warning, 3rd: Written Warning';
            break;
        case 'VW': // Medium
            offensesField.value = '1st, 2nd, 3rd';
            penaltiesField.value = '1st: Verbal Warning, 2nd: Written Warning, 3rd: Probation';
            break;
        case 'WW': // High
            offensesField.value = '1st, 2nd, 3rd';
            penaltiesField.value = '1st: Written Warning, 2nd: Probation, 3rd: Expulsion';
            break;
        case 'Exp': // Very High
            offensesField.value = '1st';
            penaltiesField.value = 'Immediate Expulsion';
            break;
        default:
            offensesField.value = '1st, 2nd, 3rd';
            penaltiesField.value = '1st: Warning, 2nd: Verbal Warning, 3rd: Written Warning';
    }
}
*/

// Function to delete a category
function deleteCategory(button) {
    const categorySection = button.closest('.category-section');
    const categoryId = button.getAttribute('data-category-id');
    const categoryName = categorySection.querySelector('.category-name-input').value;
    
    // Create and show a custom confirmation dialog
    const confirmDialog = document.createElement('div');
    confirmDialog.className = 'modal fade';
    confirmDialog.id = 'deleteCategoryModal';
    confirmDialog.setAttribute('tabindex', '-1');
    confirmDialog.setAttribute('aria-labelledby', 'deleteCategoryModalLabel');
    confirmDialog.setAttribute('aria-hidden', 'true');
    
    confirmDialog.innerHTML = `
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <h5 class="mb-3">Delete Category?</h5>
                    <p class="mb-4">This will delete the category and all its violations. This action cannot be undone.</p>
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteCategoryBtn">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(confirmDialog);
    
    // Initialize the Bootstrap modal
    const modal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
    modal.show();
    
    // Handle the confirmation
    document.getElementById('confirmDeleteCategoryBtn').addEventListener('click', function() {
        // Perform AJAX call for deletion
        fetch('/educator/manual/delete-category', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ category_id: categoryId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the category section from the page
                categorySection.remove();
                // Optionally show a success message to the user
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the category.');
        })
        .finally(() => {
            // Hide and remove the modal
            modal.hide();
            document.getElementById('deleteCategoryModal').addEventListener('hidden.bs.modal', function() {
                document.getElementById('deleteCategoryModal').remove();
            });
        });
    });
    
    // Remove the modal from the DOM when it's closed
    document.getElementById('deleteCategoryModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('deleteCategoryModal').remove();
    });
}

// Function to delete an existing violation
function deleteViolation(button) {
    // Get the violation name for the confirmation message
    const row = button.closest('tr');
    const violationName = row.querySelector('textarea').value.substring(0, 50) + (row.querySelector('textarea').value.length > 50 ? '...' : '');
    const violationId = button.getAttribute('data-violation-id');
    
    // Create and show a custom confirmation dialog
    const confirmDialog = document.createElement('div');
    confirmDialog.className = 'modal fade';
    confirmDialog.id = 'deleteConfirmModal';
    confirmDialog.setAttribute('tabindex', '-1');
    confirmDialog.setAttribute('aria-labelledby', 'deleteConfirmModalLabel');
    confirmDialog.setAttribute('aria-hidden', 'true');
    
    confirmDialog.innerHTML = `
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <h5 class="mb-3">Delete Violation?</h5>
                    <p class="mb-4">This action cannot be undone.</p>
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(confirmDialog);
    
    // Initialize the Bootstrap modal
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
    
    // Handle the confirmation
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        // Perform AJAX call for deletion
        fetch('/educator/manual/delete-violation-type', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ violation_type_id: violationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the row from the table
                row.remove();
                // Optionally show a success message to the user
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the violation.');
        })
        .finally(() => {
            // Hide and remove the modal
            modal.hide();
            document.getElementById('deleteConfirmModal').addEventListener('hidden.bs.modal', function() {
                document.getElementById('deleteConfirmModal').remove();
            });
        });
    });
    
    // Remove the modal from the DOM when it's closed
    document.getElementById('deleteConfirmModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('deleteConfirmModal').remove();
    });
}

// Function to add a new violation row to an existing category
function addViolationToCategory(button) {
    const categorySection = button.closest('.category-section');
    const categoryId = button.getAttribute('data-category-id');
    const categoryIndex = button.getAttribute('data-category-index');
    const violationsContainer = categorySection.querySelector('tbody');
    
    // Determine the next index for the new violation
    const existingViolationRows = violationsContainer.querySelectorAll('tr');
    const newViolationIndex = existingViolationRows.length; // Use the current count as the new index

    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <input type="hidden" name="categories[${categoryIndex}][violationTypes][${newViolationIndex}][id]" value="">
        <td>${newViolationIndex + 1}</td>
        <td class="editable-cell">
            <textarea name="categories[${categoryIndex}][violationTypes][${newViolationIndex}][violation_name]"
                      class="violation-textarea" maxlength="500" required placeholder="Enter violation name"></textarea>
            <div class="char-counter small text-muted">
                <span class="current-count">0</span>/500 characters
            </div>
        </td>
        <td>
            <select class="penalty-select severity-select"
                    name="categories[${categoryIndex}][violationTypes][${newViolationIndex}][default_penalty]" required>
                <option value="W">Low</option>
                <option value="VW">Medium</option>
                <option value="WW">High</option>
                <option value="Exp">Very High</option>
            </select>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm delete-new-violation-row">
                <i class="fas fa-trash"></i> Delete
            </button>
        </td>
    `;
    violationsContainer.appendChild(newRow);

    // Add character counter to the new textarea
    const newTextarea = newRow.querySelector('.violation-textarea');
    if (newTextarea) {
        newTextarea.addEventListener('input', function() {
            const counter = this.parentNode.querySelector('.char-counter .current-count');
            if (counter) {
                const currentLength = this.value.length;
                counter.textContent = currentLength;
                if (currentLength > 450) {
                    counter.classList.add('char-limit-warning');
                } else {
                    counter.classList.remove('char-limit-warning');
                }
            }
        });
    }

    // Add event listener for deleting the newly added row
    newRow.querySelector('.delete-new-violation-row').addEventListener('click', function() {
        newRow.remove();
        // Re-index rows if needed (optional, for visual consistency)
        // reindexViolationRows(violationsContainer);
    });

    // Scroll to the new row
    newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// Attach event listeners for adding new violation rows
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.add-violation-btn').forEach(button => {
        button.addEventListener('click', function() {
            addViolationToCategory(this);
        });
    });
});

// Function to delete a new violation that hasn't been saved yet
function deleteNewViolation(button) {
    const row = button.closest('tr');
    row.remove();
}
