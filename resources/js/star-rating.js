/**
 * Shows the 1-5 options in the rating form as a row of stars.
 *
 * The radio buttons stay in place, visually hidden, so keyboard use, submission and
 * screen readers behave exactly as they do without JavaScript. This only changes what
 * a sighted user sees, and previews a score while the pointer hovers over it.
 */

// Filled and empty stars differ in shape as well as colour, so colour is not the only cue.
const FILLED_STAR = '★';
const EMPTY_STAR = '☆';

const STAR_CLASSES =
    'block rounded-sm px-0.5 text-3xl leading-none text-[#706f6c] peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 data-filled:text-[#f53003] dark:text-[#A1A09A] dark:data-filled:text-[#FF4433]';

document.querySelectorAll('[data-star-rating]').forEach((group) => {
    const stars = [...group.querySelectorAll('label[data-score]')].map((label) => {
        const face = label.querySelector('[data-score-face]');
        const score = Number(label.dataset.score);

        face.className = STAR_CLASSES;
        face.setAttribute('aria-hidden', 'true');

        // The visible number is replaced by a star, so the radio keeps its name here.
        const accessibleName = document.createElement('span');
        accessibleName.className = 'sr-only';
        accessibleName.textContent = `${score} out of 5`;
        label.append(accessibleName);

        return { label, face, score };
    });

    // The "Skip" option has an empty value, which counts as no stars.
    const checkedScore = () => Number(group.querySelector('input:checked')?.value ?? 0);

    const fillUpTo = (score) => {
        stars.forEach((star) => {
            const isFilled = star.score <= score;

            star.face.textContent = isFilled ? FILLED_STAR : EMPTY_STAR;
            star.face.toggleAttribute('data-filled', isFilled);
        });
    };

    stars.forEach((star) => star.label.addEventListener('pointerenter', () => fillUpTo(star.score)));
    group.addEventListener('pointerleave', () => fillUpTo(checkedScore()));
    group.addEventListener('change', () => fillUpTo(checkedScore()));

    fillUpTo(checkedScore());
});
