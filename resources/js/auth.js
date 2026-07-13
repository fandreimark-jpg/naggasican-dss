// resources/js/auth.js
// Shows/hides the password on the login page when the eye icon is clicked.

window.togglePassword = function () {
    const input = document.getElementById("password");
    const icon = document.getElementById("eyeIcon");
    if (input.type === "password") {
        input.type = "text";
        icon.className = "bi bi-eye-slash";
    } else {
        input.type = "password";
        icon.className = "bi bi-eye";
    }
};