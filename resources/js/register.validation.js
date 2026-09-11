
/* =======================================================*/
/* Registration Form Validation*/
/* This script run when the register form is present */
/* ========================================================*/

document.addEventListener ('DOMContentLoaded', function(){
    const form = document.getElementById('registerForm');

    //Only run if the register form exists on this page
    if (!form) return;

    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');

    const nameError = document.getElementById('nameError');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const confirmError = document.getElementById('confirmError');

    function hideErrors(){
        nameError.classList.add('hidden');
        emailError.classList.add('hidden');
        passwordError.classList.add('hidden');
        confirmError.classList.add('hidden');
    }
    form.addEventListener('submit', function (event){
        hideErrors();
        let hasError = false;

        if (nameInput.value.trim() === ''){
            nameError.classList.remove('hidden');
            hasError = true;
        }

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(emailInput.value.trim())){
            emailError.classList.remove('hidden');
            hasError = true;
        }
        if (passwordInput.value.length < 8){
            passwordError.classList.remove('hidden');
            hasError = true;
        }
        if (confirmInput.value !== passwordInput.value){
            confirmError.classList.remove('hidden');
            hasError = true;
        }
        if (hasError){
            event.preventDefault();
        }
    });
});
