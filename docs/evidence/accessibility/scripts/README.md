# Scripts behind the accessibility evidence

These produced the evidence in `docs/accessibility.md`. They are kept as a record of the
method, not as part of the test suite, and are not run by CI.

| Script | What it does |
| --- | --- |
| `voiceover-applescript-run.mjs` | Drives macOS VoiceOver through the site with AppleScript and records what it says after each key. Needs the two permissions in `docs/accessibility.md` (problems T1 and T2), takes over the screen for a few minutes, and always stops VoiceOver at the end. |
| `keyboard-run.mjs` | Goes through the site with the keyboard only, taking a screenshot and recording the focused element at each step, and saves the accessibility tree of the main pages. |

Both expect the site running on XAMPP at `http://recipebox.localhost`, Google Chrome, and
`playwright-core` installed next to them (`npm install playwright-core`). They write to the
absolute paths at the top of each file, which point at the machine they were run on;
change those before running them elsewhere. The VoiceOver script signs in as the sample
account `amelia@example.test` and toggles a favourite in the XAMPP database.
