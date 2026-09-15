/*========================================*/
/* LOGIN FORM VALIDATION */
/* Only runs when login form is present */
/*========================================*/

document.addEventListener('DOMContentLoaded', function (){
    const loginForm = document.getElementById('loginForm');

    // Only run if the login form exists on this page 
    if (!loginForm) return;

    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    const emailError = document.getElementById('loginEmailError');
    const passwordError = document.getElementById('loginPasswordError');

    //Find all server-side error messages (laravel errors)
    const serverErrors = document.querySelectorAll('.server-error');

    function hideLoginErrors (){
        emailError.classList.add('hidden');
        passwordError.classList.add('hidden');
    }

    function hideServerErrors(){
        serverErrors.forEach(function (error){
            error.classList.add('hidden');
            error.style.display = 'none';
        });
    }
    emailInput.addEventListener('input', hideServerErrors);
    passwordInput.addEventListener('input', hideServerErrors);

    loginForm.addEventListener('submit', function (event){
        hideLoginErrors();
        let hasError = false;

        // Email: must be valid format 
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if(!emailPattern.test(emailInput.value.trim())){
            emailError.classList.remove('hidden');
            hasError = true;
        }

        // password must not be empty
        if (passwordInput.value.trim() === ''){
            passwordError.classList.remove('hidden');
            hasError = true;
        }
        if (hasError){
            event.preventDefault();
        }
    });

});
