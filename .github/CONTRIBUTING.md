# Contribution Guide

Thank you for considering contributing to Laravel HTMX! Please review the following guidelines before submitting a pull request.

For significant changes, please open an issue first so we can discuss the approach.

## Process

1. Fork the project
2. Create a new branch
3. Code, test, commit, and push
4. Open a pull request detailing your changes

## Guidelines

- Ensure the coding style passes by running `composer lint`.
- Send a coherent commit history, making sure each commit in your pull request is meaningful.
- You may need to [rebase](https://git-scm.com/book/en/v2/Git-Branching-Rebasing) to avoid merge conflicts.
- Please remember that we follow [SemVer](http://semver.org/).

## Commits & branches

Commit messages and branch names follow [Conventional Commits](https://www.conventionalcommits.org/):

- Types: `feat:`, `fix:`, `docs:`, `chore:`, `refactor:`, `test:` — append `!` (or a `BREAKING CHANGE:` footer) when the package contract breaks.
- Branches carry the same prefix: `feat/<slug>`, `fix/<slug>`, `docs/<slug>`, `chore/<slug>`.
- PRs squash-merge under one conventional title, so `main` stays scannable. WIP commits inside the branch can be freeform.

## Setup

Clone your fork, then install the dev dependencies:

```bash
composer install
```

## Lint

Lint your code:

```bash
composer lint
```

## Tests

Run all tests:

```bash
composer test
```
