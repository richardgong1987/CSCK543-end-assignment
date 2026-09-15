import { isValidEmail, MIN_PASSWORD_LENGTH, setupFormValidation } from './common.js';

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
