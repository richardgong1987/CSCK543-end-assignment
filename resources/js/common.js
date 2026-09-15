/**
 * Client-side checks for a form the server also validates.
 *
 * These only save a round trip; the server rules remain the real guard, which is
 * why the forms keep `novalidate` and still render Laravel's own messages.
 *
 * @param {object} options
 * @param {string} options.formId
 * @param {{ inputId: string, errorId: string, isValid: (value: string, form: HTMLFormElement) => boolean }[]} options.fields
 * @param {string} [options.serverErrorSelector] marks the messages Laravel rendered
 */
export function setupFormValidation({ formId, fields, serverErrorSelector = '.server-error' }) {
    const form = document.getElementById(formId);

    // The bundle loads on every page, so most pages have no such form.
    if (!form) return;

    const checks = fields.map(({ inputId, errorId, isValid }) => ({
        input: document.getElementById(inputId),
        error: document.getElementById(errorId),
        isValid,
    }));

    // A server message describes the last submission, so it is stale once the user edits.
    const hideServerErrors = () => {
        form.querySelectorAll(serverErrorSelector).forEach((message) => message.classList.add('hidden'));
    };

    checks.forEach(({ input }) => input.addEventListener('input', hideServerErrors));

    form.addEventListener('submit', (event) => {
        let hasError = false;

        checks.forEach(({ input, error, isValid }) => {
            const isFieldValid = isValid(input.value, form);

            error.classList.toggle('hidden', isFieldValid);
            hasError ||= !isFieldValid;
        });

        if (hasError) {
            event.preventDefault();
        }
    });
}

export function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());
}
