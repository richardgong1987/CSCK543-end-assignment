import { isValidEmail, setupFormValidation } from './common.js';

// Mirrors Password::defaults() on the server, which is Laravel's 8-character minimum.
const MIN_PASSWORD_LENGTH = 8;

setupFormValidation({
    formId: 'registerForm',
    fields: [
        { inputId: 'name', errorId: 'nameError', isValid: (value) => value.trim() !== '' },
        { inputId: 'email', errorId: 'emailError', isValid: isValidEmail },
        { inputId: 'password', errorId: 'passwordError', isValid: (value) => value.length >= MIN_PASSWORD_LENGTH },
        {
            inputId: 'password_confirmation',
            errorId: 'confirmError',
            isValid: (value, form) => value === form.elements.password.value,
        },
    ],
});
