# Contributing to DRM Catholic

Thank you for your interest in contributing to DRM Catholic! This document explains how to get involved.

## Code of Conduct

This project adheres to the [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md). By participating, you agree to uphold this code. Please report unacceptable behavior to the maintainers.

## How to Contribute

### Reporting Bugs

Before filing a bug report, search the [existing issues](https://github.com/schoedel-learn/drm-catholic/issues) to avoid duplicates. When filing a new issue, use the **Bug Report** template and include:

- A clear description of the problem
- Steps to reproduce the behavior
- Expected vs. actual behavior
- Environment details (PHP version, Node version, OS)

### Suggesting Features

Use the **Feature Request** template when opening a new issue. Describe the use case, the proposed solution, and any alternatives you considered.

### Pull Requests

1. **Fork** the repository and create a branch from `main`:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Set up the development environment** (see [Getting Started](README.md#getting-started)).

3. **Make your changes**, following the coding standards below.

4. **Add or update tests** for any new or changed behavior.

5. **Run the test suite** and confirm everything passes:
   ```bash
   # Laravel (from laravel/)
   php artisan test
   ```

6. **Run the linter**:
   ```bash
   # PHP (from laravel/)
   ./vendor/bin/pint
   ```

7. **Commit** using a descriptive message and **open a pull request** against `main`.

8. Respond to review feedback promptly.

## Coding Standards

### PHP / Laravel

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style.
- Run [Laravel Pint](https://laravel.com/docs/pint) before committing: `./vendor/bin/pint`
- Write PHPUnit tests for new features and bug fixes.
- Keep controllers thin; put business logic in service classes.

## Environment Variables and Secrets

- **Never** commit real secrets, API keys, or credentials.
- Copy `laravel/.env.example` to `laravel/.env` and fill in your own values locally.
- All secrets used in CI must be stored as GitHub Actions Secrets.

## Branch and Commit Conventions

- Branch names: `feature/<description>`, `fix/<description>`, `chore/<description>`
- Commit messages: imperative mood, present tense (e.g., `Add Google Places search endpoint`)

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).
