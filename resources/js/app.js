/**
 * Progressive enhancement for the recipe search form.
 *
 * The form is a plain GET form and submits on its own, so everything below is a
 * convenience rather than a requirement: with scripting off, the Search button still
 * applies the sort menu along with the rest of the filters.
 */
document.querySelectorAll('[data-auto-submit]').forEach((control) => {
    // A sort menu that needs a second click to take effect is the usual complaint
    // about server-rendered listings, so apply it as soon as the choice changes.
    control.addEventListener('change', () => control.form?.requestSubmit());
});

document.querySelectorAll('form[role="search"]').forEach((form) => {
    // A browser puts every named field in the query string, empty ones included, which
    // leaves a search URL too noisy to read or share. Disabled fields are left out
    // altogether, so empty ones are switched off as the form goes. The page navigates
    // immediately afterwards, so nothing stays disabled in front of the user.
    form.addEventListener('submit', () => {
        form.querySelectorAll('input:not([type="checkbox"]), select').forEach((field) => {
            field.disabled = field.value === '';
        });
    });
});

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