document.addEventListener('DOMContentLoaded', function() {

    // ===================== Validation Functions =====================
    function validateVehicleName(input) {
        const val = input.value.trim();
        if (!val) return 'Vehicle Name is required.';
        return '';
    }

    function validateVehicleType(input) {
        const val = input.value;
        if (!val) return 'Vehicle Type is required.';
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

    // ===================== Form Setup =====================
    const vehicleForm = document.querySelector('form');
    if(vehicleForm){
        const config = {
            'vehicle_name': validateVehicleName,
            'vehicle_type': validateVehicleType,
            'price_per_day': validatePrice,
            'availability': validateAvailability,
            'description': validateDescription,
            'image': validateImage
        };

        Object.keys(config).forEach(id => {
            const input = vehicleForm.querySelector(`[name="${id}"]`);
            if(!input) return;

            input.addEventListener('input', () => {
                const error = config[id](input);
                showFieldError(input, error);
            });

            input.addEventListener('change', () => {
                const error = config[id](input);
                showFieldError(input, error);
            });

            input.addEventListener('blur', () => {
                const error = config[id](input);
                showFieldError(input, error);
            });
        });

        vehicleForm.addEventListener('submit', (e)=>{
            let hasError = false;
            Object.keys(config).forEach(id=>{
                const input = vehicleForm.querySelector(`[name="${id}"]`);
                const error = config[id](input);
                if(error) { showFieldError(input, error); hasError = true; }
                else clearFieldError(input);
            });
            if(hasError) e.preventDefault();
        });
    }

    // ===================== Error Display Functions =====================
    function showFieldError(input, msg){
        let errorEl = input.nextElementSibling;
        if(!errorEl || !errorEl.classList.contains('field-error')){
            errorEl = document.createElement('div');
            errorEl.className = 'field-error text-danger small mt-1';
            input.parentNode.appendChild(errorEl);
        }
        errorEl.textContent = msg;
        if(msg) input.classList.add('is-invalid');
        else input.classList.remove('is-invalid');
    }

    function clearFieldError(input){
        let errorEl = input.nextElementSibling;
        if(errorEl && errorEl.classList.contains('field-error')) errorEl.textContent = '';
        input.classList.remove('is-invalid');
    }

});
