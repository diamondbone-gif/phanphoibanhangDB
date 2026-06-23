document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (togglePassword && passwordInput && eyeIcon) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';

            passwordInput.setAttribute('type', type);

            if (type === 'text') {
                eyeIcon.classList.replace('ph-eye', 'ph-eye-slash');
            } else {
                eyeIcon.classList.replace('ph-eye-slash', 'ph-eye');
            }
        });
    }

    const loginForm = document.getElementById('loginForm');
    const btnText = document.getElementById('btnText');
    const btnIcon = document.getElementById('btnIcon');
    const submitButton = document.getElementById('submitButton');

    if (loginForm && btnText && btnIcon && submitButton) {
        loginForm.addEventListener('submit', function() {
            btnText.textContent = 'Đang xử lý...';
            btnIcon.classList.remove('hidden');
            submitButton.disabled = true;
            submitButton.classList.add('opacity-70', 'cursor-not-allowed');
        });
    }
});