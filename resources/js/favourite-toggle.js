/**
 * Saves or removes a favourite without reloading the recipe page.
 *
 * The form works on its own; this only sends it in the background. Anything
 * unexpected, such as a network error or an expired session, falls back to a
 * normal submission, so the server's redirect and messages take over.
 */

// Must match the button text rendered in recipes/show.blade.php.
const BUTTON_LABELS = { saved: 'Remove favourite', notSaved: 'Save favourite' };

document.querySelectorAll('form[data-favourite-form]').forEach((form) => {
    const button = form.querySelector('button[type="submit"]');
    const methodInput = form.querySelector('input[name="_method"]');
    const status = form.querySelector('[data-favourite-status]');

    const showFavouriteState = (isFavourite) => {
        form.action = isFavourite ? form.dataset.removeUrl : form.dataset.saveUrl;
        methodInput.disabled = !isFavourite;
        button.textContent = isFavourite ? BUTTON_LABELS.saved : BUTTON_LABELS.notSaved;
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        // Read the form before disabling anything, or the disabled state could leak into it.
        const body = new FormData(form);
        button.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body,
            });

            if (!response.ok) {
                throw new Error(`Favourite request failed with status ${response.status}`);
            }

            const { is_favourite: isFavourite, message } = await response.json();

            showFavouriteState(isFavourite);
            status.textContent = message;
        } catch {
            // form.submit() does not fire the submit event, so this cannot loop.
            form.submit();
        } finally {
            button.disabled = false;
        }
    });
});
