// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Get all forms
    const forms = document.querySelectorAll('form');

    // Add submit event listener to each form
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Get all required fields
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            // Check if all required fields are filled
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });

            // If form is not valid, prevent submission
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });

    // Add input event listener to remove error class when user starts typing
    document.querySelectorAll('input, textarea').forEach(field => {
        field.addEventListener('input', function() {
            this.classList.remove('error');
        });
    });
}); 