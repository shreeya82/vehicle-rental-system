<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['register_errors'] ?? [];
$old = $_SESSION['register_old'] ?? [];
unset($_SESSION['register_errors'], $_SESSION['register_old']);
$success = $_SESSION['register_success'] ?? null;
unset($_SESSION['register_success']);
?>

<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <form id="registerForm" method="POST" action="register.php" novalidate>
        <div class="modal-header">
          <h5 class="modal-title">User Registration</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <?php if(isset($errors['general'])): ?>
            <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
          <?php endif; ?>

          <div class="mb-3">
            <label>Full Name</label>
            <input type="text" id="full_name" name="full_name"
                   class="form-control <?php echo isset($errors['full_name'])?'is-invalid':''; ?>"
                   value="<?php echo htmlspecialchars($old['full_name'] ?? ''); ?>">
            <div class="invalid-feedback"><?php echo $errors['full_name'] ?? 'Full name required'; ?></div>
          </div>

          <div class="mb-3">
            <label>Phone</label>
            <input type="text" id="phone" name="phone"
                   class="form-control <?php echo isset($errors['phone'])?'is-invalid':''; ?>"
                   value="<?php echo htmlspecialchars($old['phone'] ?? ''); ?>">
            <div class="invalid-feedback"><?php echo $errors['phone'] ?? 'Phone required'; ?></div>
          </div>

          <div class="mb-3">
            <label>Email</label>
            <input type="email" id="email" name="email"
                   class="form-control <?php echo isset($errors['email'])?'is-invalid':''; ?>"
                   value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>">
            <div class="invalid-feedback"><?php echo $errors['email'] ?? 'Valid email required'; ?></div>
          </div>

          <div class="mb-3">
            <label>Password</label>
            <input type="password" id="password" name="password"
                   class="form-control <?php echo isset($errors['password'])?'is-invalid':''; ?>">
            <div class="invalid-feedback"><?php echo $errors['password'] ?? 'Password required'; ?></div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success w-100">Register</button>
        </div>

      </form>

    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const modalEl = document.getElementById('registerModal');

    // Show modal if there are errors
    <?php if(!empty($errors)): ?>
        new bootstrap.Modal(modalEl).show();
    <?php endif; ?>

    // =================== Modal Field Validation ===================
    function validateField(field, validator, message){
        field.addEventListener('input', function(){
            if(validator(field.value)){
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
                field.nextElementSibling.innerText = '';
            } else {
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
                field.nextElementSibling.innerText = message;
            }
        });
    }

    validateField(document.getElementById('full_name'),
        v => /^[A-Za-z]+(\s[A-Za-z]+)+$/.test(v),
        'Full name must include first and last name');

    validateField(document.getElementById('phone'),
        v => /^(97|98)\d{8}$/.test(v),
        'Phone must start with 97 or 98 and be 10 digits');

    validateField(document.getElementById('email'),
        v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v),
        'Enter a valid email');

    validateField(document.getElementById('password'),
        v => /^(?=.*[A-Za-z])(?=.*\d).{6,}$/.test(v),
        'Password must be 6+ chars and include letters & numbers');

    // Prevent submission if invalid
    form.addEventListener('submit', function(e){
        const fields = ['full_name','phone','email','password'];
        let valid = true;
        fields.forEach(id => {
            const input = document.getElementById(id);
            if(!input.classList.contains('is-valid')){
                input.classList.add('is-invalid');
                valid = false;
            }
        });
        if(!valid) e.preventDefault();
    });

    // =================== Reset Modal on Close ===================
    if(modalEl){
        modalEl.addEventListener('hidden.bs.modal', function () {
            form.reset();
            form.querySelectorAll('.is-invalid, .is-valid').forEach(el => el.classList.remove('is-invalid','is-valid'));
        });
    }

    // =================== SweetAlert for Success ===================
    <?php if($success): ?>
    Swal.fire({
        icon: 'success',
        title: 'Registration Submitted!',
        text: '<?php echo addslashes($success); ?>'
    }).then(() => {
        // Optional: redirect to login page
        window.location.href = 'index.php';
    });
    <?php endif; ?>
});
</script>
