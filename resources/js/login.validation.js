import { isValidEmail, setupFormValidation } from './common.js';

setupFormValidation({
    formId: 'loginForm',
    fields: [
        { inputId: 'email', errorId: 'loginEmailError', isValid: isValidEmail },
        { inputId: 'password', errorId: 'loginPasswordError', isValid: (value) => value.trim() !== '' },
    ],
});
