/**
 * Client-side checks for a form the server also validates.
 *
 * These only save a round trip; the server rules remain the real guard, which is
 * why the forms keep `novalidate` and still render Laravel's own messages.
 *
 * A field is checked when the user leaves it, re-checked as they type while its
 * message is showing, and every field is checked on submit.
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

    const editedInputs = new Set();

    // A server message describes the last submission, so it is stale once the user edits.
    const hideServerErrors = () => {
        form.querySelectorAll(serverErrorSelector).forEach((message) => {
            message.classList.add('hidden');

            form.querySelectorAll(`[aria-describedby~="${message.id}"]`).forEach((input) => {
                linkErrorMessage(input, message.id, false);
            });
        });
    };

    const showCheckResult = (check) => {
        const hasFailed = !check.isValid(check.input.value, form);

        check.error.classList.toggle('hidden', !hasFailed);
        linkErrorMessage(check.input, check.error.id, hasFailed);

        return hasFailed;
    };

    const isShowingError = (check) => !check.error.classList.contains('hidden');

    checks.forEach((check) => {
        check.input.addEventListener('input', () => {
            editedInputs.add(check.input);
            hideServerErrors();

            // Every visible message is re-checked, not just this field's, because one rule can
            // depend on another field: fixing the password can fix its confirmation too.
            checks.filter(isShowingError).forEach(showCheckResult);
        });

        // Only a field the user has typed in, so tabbing through an empty form stays quiet.
        check.input.addEventListener('blur', () => {
            if (editedInputs.has(check.input)) {
                showCheckResult(check);
            }
        });
    });

    form.addEventListener('submit', (event) => {
        const failedChecks = checks.filter(showCheckResult);

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

// Mirrors Password::defaults() on the server, which is Laravel's 8-character minimum.
export const MIN_PASSWORD_LENGTH = 8;

export function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());
}
