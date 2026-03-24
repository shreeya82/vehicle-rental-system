document.addEventListener('DOMContentLoaded', function() {

    // ==================== Reset Register Modal ====================
    const registerModalEl = document.getElementById('registerModal');
    if(registerModalEl){
        registerModalEl.addEventListener('hidden.bs.modal', function () {
            const form = registerModalEl.querySelector('form');
            form.reset(); 
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        });
    }

    // ==================== Booking Form Validation ====================
    const bookingForm = document.getElementById('bookingForm');
    if(bookingForm) {
        const startInput = document.getElementById('start_date');
        const endInput = document.getElementById('end_date');
        const returnInput = document.getElementById('return_time');
        const purposeBox = document.querySelector('.purpose-box');
        const purposes = document.querySelectorAll('input[name="purpose[]"]');

        // Only allow one purpose checkbox at a time
        purposes.forEach(cb => {
            cb.addEventListener('change', function() {
                if(this.checked){
                    purposes.forEach(other => { if(other !== this) other.checked = false; });
                }
            });
        });

        function showFeedback(input, message){
            let feedback = input.nextElementSibling;
            if(!feedback || !feedback.classList.contains('invalid-feedback')){
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback d-block';
                input.parentNode.appendChild(feedback);
            }
            if(message){
                input.classList.add('is-invalid');
                feedback.textContent = message;
            } else {
                input.classList.remove('is-invalid');
                feedback.textContent = '';
            }
        }

        function validateBookingField() {
            let valid = true;

            // --- Purpose Validation ---
            const checkedPurpose = Array.from(purposes).filter(cb => cb.checked);
            let purposeFeedback = purposeBox.querySelector('.invalid-feedback');
            if(!purposeFeedback){
                purposeFeedback = document.createElement('div');
                purposeFeedback.className = 'invalid-feedback d-block';
                purposeBox.appendChild(purposeFeedback);
            }
            if(checkedPurpose.length === 0){
                purposeFeedback.textContent = 'Please select a purpose';
                purposeBox.classList.add('is-invalid');
                valid = false;
            } else {
                purposeFeedback.textContent = '';
                purposeBox.classList.remove('is-invalid');
            }

            // --- Start Date ---
            if(!startInput.value){
                showFeedback(startInput, 'Please select a start date');
                valid = false;
            } else {
                showFeedback(startInput, '');
            }

            // --- End Date ---
            if(!endInput.value){
                showFeedback(endInput, 'Please select an end date');
                valid = false;
            } else {
                showFeedback(endInput, '');
            }

            // --- Return Time ---
            if(!returnInput.value){
                showFeedback(returnInput, 'Please select a return time');
                valid = false;
            } else {
                showFeedback(returnInput, '');
            }

            // --- Date Logic ---
            if(startInput.value && endInput.value){
                const startDate = new Date(startInput.value);
                const endDate = new Date(endInput.value);
                const today = new Date(); today.setHours(0,0,0,0);
                const totalDays = (endDate - startDate)/(1000*60*60*24) + 1;

                if(startDate < today){ showFeedback(startInput, 'Start date cannot be in the past'); valid = false; }
                if(endDate < today){ showFeedback(endInput, 'End date cannot be in the past'); valid = false; }
                if(totalDays <= 0){ showFeedback(endInput, 'End date must be after start date'); valid = false; }
                if(totalDays > 10){ showFeedback(endInput, 'Booking cannot exceed 10 days'); valid = false; }
            }

            // --- Remaining Units ---
            const remaining = parseInt(document.getElementById('remaining_units')?.textContent || '0');
            if(remaining <= 0){
                showFeedback(endInput, 'No units available for selected dates');
                valid = false;
            }

            return valid;
        }

        bookingForm.addEventListener('submit', function(e){
            e.preventDefault();
            const valid = validateBookingField();

            if(valid){
                const price = parseFloat(document.getElementById('price').value);
                const totalDays = (new Date(endInput.value) - new Date(startInput.value))/(1000*60*60*24) + 1;
                const totalAmount = totalDays * price;

                Swal.fire({
                    title: 'Confirm Booking?',
                    html: `<strong>Total Amount:</strong> Rs. ${totalAmount}<br>
                           <strong>Days:</strong> ${totalDays}<br>
                           <strong>Return Time:</strong> ${returnInput.value}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Book Now'
                }).then(result => {
                    if(result.isConfirmed) bookingForm.submit();
                });
            }
        });

        // --- Remove error on input ---
        startInput.addEventListener('change', ()=>showFeedback(startInput,''));
        endInput.addEventListener('change', ()=>showFeedback(endInput,''));
        returnInput.addEventListener('change', ()=>showFeedback(returnInput,''));
        purposes.forEach(cb => cb.addEventListener('change', ()=>{
            purposeBox.classList.remove('is-invalid');
            const feedback = purposeBox.querySelector('.invalid-feedback');
            if(feedback) feedback.remove();
        }));

        // --- Update min date for end_date dynamically ---
        startInput.addEventListener("change", function(){
            endInput.min = this.value;
            if(endInput.value < this.value) endInput.value = "";
        });
    }
});
