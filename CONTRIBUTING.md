# Contributing

## Getting a change in

Small, focused, tested. That is the whole bar.

**Issues:** version, PHP version, what you expected, what happened. A screenshot of the
affected tab helps more than a paragraph.

**Pull requests:** branch from `main`, one concern per PR, and describe the change in the
body rather than in the commits.

**Checks:** every file must pass `php -l`; CI runs it on push and on PR.

```bash
git clone https://github.com/BaronCeiling/onyx-cache && cd onyx-cache
find . -name '*.php' -print0 | xargs -0 -n1 php -l
```

**Translations:** `.po` under `languages/`, PR, no compiled `.mo` in the diff.

**Not accepted:** new settings tabs without an issue, bundled dependencies, and refactors
that rename things without changing behaviour.
