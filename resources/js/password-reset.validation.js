import { isValidEmail, MIN_PASSWORD_LENGTH, setupFormValidation } from './common.js';

setupFormValidation({
    formId: 'forgotPasswordForm',
    fields: [
        { inputId: 'email', errorId: 'forgotEmailError', isValid: isValidEmail },
    ],
});

setupFormValidation({
    formId: 'resetPasswordForm',
    fields: [
        { inputId: 'email', errorId: 'resetEmailError', isValid: isValidEmail },
        { inputId: 'password', errorId: 'resetPasswordError', isValid: (value) => value.length >= MIN_PASSWORD_LENGTH },
        {
            inputId: 'password_confirmation',
            errorId: 'resetConfirmError',
            isValid: (value, form) => value === form.elements.password.value,
        },
    ],
});
