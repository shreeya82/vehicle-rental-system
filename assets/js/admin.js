document.addEventListener('DOMContentLoaded', function() {

    // ======== Error Helpers ========
    function showFieldError(input, msg) {
        let errorEl = input.nextElementSibling;
        if (!errorEl || !errorEl.classList.contains('field-error')) {
            errorEl = document.createElement('div');
            errorEl.className = 'field-error text-danger small mt-1';
            input.parentNode.appendChild(errorEl);
        }
        errorEl.textContent = msg;
        input.classList.add('is-invalid');
    }

    function clearFieldError(input) {
        let errorEl = input.nextElementSibling;
        if (errorEl && errorEl.classList.contains('field-error')) {
            errorEl.textContent = '';
        }
        input.classList.remove('is-invalid');
    }

    // ======== Validators ========
    function validateFullName(input) {
        const value = input.value.trim();
        if (!value) return 'Full Name is required.';
        const words = value.split(' ').filter(w => w.length > 0);
        if (words.length < 2) return 'Please enter first and last name.';
        if (value.length < 3) return 'Full Name must be at least 3 characters.';
        return '';
    }

    function validatePhone(input) {
        const value = input.value.trim();
        const phoneRegex = /^(98|97)\d{8}$/;
        if (!value) return 'Phone is required.';
        if (!phoneRegex.test(value)) return 'Phone must be 10 digits and start with 98 or 97.';
        return '';
    }

    function validateEmail(input) {
        const value = input.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!value) return 'Email is required.';
        if (!emailRegex.test(value)) return 'Invalid email address.';
        return '';
    }

    function validatePassword(input, required = true) {
        const value = input.value.trim();
        if (required && !value) return 'Password is required.';
        if (value && value.length < 8) return 'Password must be at least 8 characters.';
        return '';
    }

    function validateVehicleName(input) {
        const val = input.value.trim();
        if (!val) return 'Vehicle Name is required.';
        if (val.length < 2) return 'Vehicle Name must be at least 2 characters.';
        return '';
    }

    function validateVehicleType(input) {
        const val = input.value.trim();
        if (!val) return 'Vehicle Type is required.';
        if (val.length < 2) return 'Vehicle Type must be at least 2 characters.';
        return '';
    }

    function validatePrice(input) {
        const val = parseFloat(input.value.trim());
        if (!val || isNaN(val) || val <= 0) return 'Price must be a number greater than 0.';
        return '';
    }

    function validateAvailability(input) {
        const val = input.value;
        if (val !== 'Available' && val !== 'Unavailable') return 'Select a valid availability.';
        return '';
    }

    function validateDescription(input) {
        const val = input.value.trim();
        if (val.length > 300) return 'Description cannot exceed 300 characters.';
        return '';
    }

    function validateImage(input) {
        if (input.files.length === 0) return '';
        const file = input.files[0];
        const allowed = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!allowed.includes(file.type)) return 'Image must be JPG or PNG.';
        if (file.size > 2 * 1024 * 1024) return 'Image size cannot exceed 2MB.';
        return '';
    }

    function validatePlateNumber(input) {
        const val = input.value.trim();
        if (!val) return 'Plate Number is required.';
        if (!/^[A-Za-z0-9\-]+$/.test(val)) return 'Plate Number can only contain letters, numbers, and hyphens.';
        if (val.length < 2) return 'Plate Number must be at least 2 characters.';
        return '';
    }

    function validateQuantity(input) {
        const val = parseInt(input.value.trim());
        if (isNaN(val) || val < 1) return 'Quantity must be a number greater than 0.';
        return '';
    }

    function validateStatus(input) {
        const val = input.value;
        if (val !== 'Pending' && val !== 'Approved') return 'Select a valid status.';
        return '';
    }

    // ======== Live Validation Setup ========
    function setupLiveValidation(form, config) {
        Object.keys(config).forEach(id => {
            const input = document.getElementById(id) || form.querySelector(`[name="${id}"]`);
            if (!input) return;

            input.addEventListener('input', () => {
                const error = config[id](input);
                if (error) showFieldError(input, error);
                else clearFieldError(input);
            });

            input.addEventListener('change', () => {
                const error = config[id](input);
                if (error) showFieldError(input, error);
                else clearFieldError(input);
            });

            input.addEventListener('blur', () => {
                const error = config[id](input);
                if (error) showFieldError(input, error);
                else clearFieldError(input);
            });
        });

        form.addEventListener('submit', (e) => {
            let hasError = false;
            Object.keys(config).forEach(id => {
                const input = document.getElementById(id) || form.querySelector(`[name="${id}"]`);
                const error = config[id](input);
                if (error) {
                    showFieldError(input, error);
                    hasError = true;
                } else {
                    clearFieldError(input);
                }
            });
            if (hasError) e.preventDefault();
        });
    }

    // ======== Users Forms ========
    const addUserForm = document.getElementById('addUserForm');
    if (addUserForm) {
        setupLiveValidation(addUserForm, {
            'full_name': validateFullName,
            'phone': validatePhone,
            'email': validateEmail,
            'password': input => validatePassword(input, true)
        });
    }

    const editUserForm = document.getElementById('editUserForm');
    if (editUserForm) {
        setupLiveValidation(editUserForm, {
            'edit-full-name': validateFullName,
            'edit-phone': validatePhone,
            'edit-email': validateEmail,
            'edit-password': input => validatePassword(input, false)
        });
    }

    // ======== Owner Add Vehicle Form ========
    const addVehicleForm = document.querySelector('#addVehicleModal form');
    if (addVehicleForm) {
        setupLiveValidation(addVehicleForm, {
            'vehicle_name': validateVehicleName,
            'vehicle_type': validateVehicleType,
            'price_per_day': validatePrice,
            'availability': validateAvailability,
            'description': validateDescription,
            'image': validateImage,
            'plate_number': validatePlateNumber,
            'quantity': validateQuantity
        });
    }

    // ======== Owner Edit Vehicle Form ========
    const editVehicleForm = document.querySelector('#editVehicleModal form');
    if (editVehicleForm) {
        setupLiveValidation(editVehicleForm, {
            'edit-name': validateVehicleName,
            'edit-type': validateVehicleType,
            'edit-price': validatePrice,
            'edit-availability': validateAvailability,
            'edit-description': validateDescription,
            'edit-image': validateImage,
            'edit-plate_number': validatePlateNumber,
            'edit-quantity': validateQuantity
        });
    }

});
