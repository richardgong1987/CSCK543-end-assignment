import './register.validation.js';
import './login.validation.js';
import './account.validation.js';
import './favourite-toggle.js';
import './star-rating.js';
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
