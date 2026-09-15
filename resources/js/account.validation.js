import { isValidEmail, MIN_PASSWORD_LENGTH, setupFormValidation } from './common.js';

setupFormValidation({
    formId: 'accountDetailsForm',
    fields: [
        { inputId: 'name', errorId: 'nameError', isValid: (value) => value.trim() !== '' },
        { inputId: 'email', errorId: 'emailError', isValid: isValidEmail },
    ],
});

setupFormValidation({
    formId: 'accountPasswordForm',
    fields: [
        { inputId: 'current_password', errorId: 'currentPasswordError', isValid: (value) => value !== '' },
        { inputId: 'new_password', errorId: 'newPasswordError', isValid: (value) => value.length >= MIN_PASSWORD_LENGTH },
        {
            inputId: 'new_password_confirmation',
            errorId: 'newPasswordConfirmError',
            isValid: (value, form) => value === form.elements.password.value,
        },
    ],
});

setupFormValidation({
    formId: 'deleteAccountForm',
    fields: [
        { inputId: 'delete_password', errorId: 'deletePasswordError', isValid: (value) => value !== '' },
    ],
});
