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
        form.querySelectorAll(serverErrorSelector).forEach((message) => {
            message.classList.add('hidden');

            form.querySelectorAll(`[aria-describedby~="${message.id}"]`).forEach((input) => {
                linkErrorMessage(input, message.id, false);
            });
        });
    };

    checks.forEach(({ input }) => input.addEventListener('input', hideServerErrors));

    form.addEventListener('submit', (event) => {
        const failedChecks = checks.filter(({ input, isValid }) => !isValid(input.value, form));

        checks.forEach((check) => {
            const hasFailed = failedChecks.includes(check);

            check.error.classList.toggle('hidden', !hasFailed);
            linkErrorMessage(check.input, check.error.id, hasFailed);
        });

        if (failedChecks.length > 0) {
            event.preventDefault();

            // Moving focus makes a screen reader read the field's label and its linked error.
            failedChecks[0].input.focus();
        }
    });
}

/**
 * Points a field at an error message, or stops pointing at it.
 *
 * A hidden message is still read out while aria-describedby names it, so a message
 * must be unlinked whenever it is hidden. On these forms a field describes nothing
 * but its errors, so any remaining link also means the field is invalid.
 */
function linkErrorMessage(input, messageId, shouldLink) {
    const messageIds = new Set((input.getAttribute('aria-describedby') ?? '').split(/\s+/).filter(Boolean));

    if (shouldLink) {
        messageIds.add(messageId);
    } else {
        messageIds.delete(messageId);
    }

    if (messageIds.size > 0) {
        input.setAttribute('aria-describedby', [...messageIds].join(' '));
        input.setAttribute('aria-invalid', 'true');
    } else {
        input.removeAttribute('aria-describedby');
        input.removeAttribute('aria-invalid');
    }
}

export function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());
}
