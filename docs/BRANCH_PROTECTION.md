# Branch Protection Setup Guide

This document describes how to configure branch protection rules for the `main` branch in the drm-catholic repository to ensure code quality and prevent accidental changes.

## Overview

Branch protection rules are configured to:
- Prevent direct pushes to the main branch
- Require pull request reviews before merging
- Require status checks to pass before merging
- Ensure branches are up to date before merging
- Apply rules to administrators as well

## Automated Protection Mechanisms

This repository includes automated protection mechanisms:

### 1. CODEOWNERS File
Location: `.github/CODEOWNERS`

The CODEOWNERS file automatically requires review from repository owners (`@schoedel-learn`) for all changes. This ensures that every pull request is reviewed before merging.

### 2. Branch Protection Workflow
Location: `.github/workflows/branch-protection.yml`

This workflow runs on every pull request to `main` and validates:
- PR is not a draft
- PR has a description
- PR has a title
- Branch naming follows conventions (recommended but not enforced)
- No merge conflicts exist
- All tests pass
- Linting passes
- Build succeeds
- CODEOWNERS file is valid

### 3. Continuous Integration
Location: `.github/workflows/ci.yml`

The CI workflow runs comprehensive checks including:
- Linting with ESLint
- Building the project
- Running all tests
- Code coverage analysis
- Testing on multiple Node.js versions (18.x, 20.x)

### 4. CodeQL Security Analysis
Location: `.github/workflows/codeql.yml`

CodeQL analyzes the code for security vulnerabilities on every push and pull request.

## GitHub Branch Protection Settings

To complete the protection setup, configure the following settings in GitHub:

### How to Configure

1. Go to the repository on GitHub
2. Navigate to **Settings** → **Branches**
3. Click **Add branch protection rule**
4. Enter `main` as the branch name pattern
5. Enable the following settings:

### Required Settings

#### Require a pull request before merging
✅ **Enable this option**
- ✅ Require approvals: **1** (minimum)
- ✅ Dismiss stale pull request approvals when new commits are pushed
- ✅ Require review from Code Owners

#### Require status checks to pass before merging
✅ **Enable this option**
- ✅ Require branches to be up to date before merging
- **Required status checks:**
  - `build (18.x)` - CI build with Node.js 18
  - `build (20.x)` - CI build with Node.js 20
  - `code-quality` - Code quality checks with coverage
  - `Validate Pull Request` - Branch protection validation
  - `Require Tests to Pass` - Test execution
  - `Verify CODEOWNERS` - CODEOWNERS validation
  - `All Protection Checks Passed` - Final validation gate
  - `Analyze (php)` - CodeQL security analysis

#### Other Protection Options
- ✅ Require conversation resolution before merging
- ✅ Require signed commits (recommended)
- ✅ Require linear history (recommended for cleaner git history)
- ✅ Include administrators (apply these rules to repository administrators too)
- ✅ Restrict who can push to matching branches (only allow through PRs)
- ✅ Allow force pushes: **Disabled**
- ✅ Allow deletions: **Disabled**

## Workflow

### For Contributors

1. **Create a feature branch** following naming conventions:
   ```bash
   git checkout -b feature/your-feature-name
   # or
   git checkout -b bugfix/issue-description
   # or
   git checkout -b fix/bug-description
   ```

2. **Make your changes** and commit them:
   ```bash
   git add .
   git commit -m "Descriptive commit message"
   ```

3. **Push your branch**:
   ```bash
   git push origin feature/your-feature-name
   ```

4. **Open a Pull Request** on GitHub:
   - Provide a clear title
   - Write a detailed description of changes
   - Link to related issues if applicable
   - Mark as ready for review (not draft)

5. **Wait for automated checks**:
   - CI workflow must pass
   - Branch protection checks must pass
   - CodeQL analysis must pass

6. **Request review** from code owners (automatic based on CODEOWNERS)

7. **Address review feedback** if any

8. **Merge** once all checks pass and approval is received

### Branch Naming Conventions

Recommended branch name prefixes:
- `feature/` - New features or enhancements
- `bugfix/` - Bug fixes
- `fix/` - Quick fixes
- `hotfix/` - Critical production fixes
- `chore/` - Maintenance tasks
- `docs/` - Documentation changes
- `refactor/` - Code refactoring
- `test/` - Test additions or modifications
- `ci/` - CI/CD changes
- `copilot/` - GitHub Copilot generated changes

Example: `feature/add-sacrament-tracking`

## Status Checks

All pull requests must pass these automated checks:

### CI Workflow (ci.yml)
- ✅ Linting passes (ESLint)
- ✅ Build succeeds
- ✅ All tests pass
- ✅ Code coverage meets standards
- ✅ Works on Node.js 18.x and 20.x

### Branch Protection Workflow (branch-protection.yml)
- ✅ PR is ready for review (not draft)
- ✅ PR has description
- ✅ PR has title
- ✅ No merge conflicts
- ✅ Tests pass
- ✅ CODEOWNERS file is valid

### CodeQL Workflow (codeql.yml)
- ✅ No security vulnerabilities detected
- ✅ Code analysis passes

## Manual Verification

Repository administrators should verify branch protection is working by:

1. **Attempting to push directly to main** (should be blocked):
   ```bash
   git checkout main
   git commit --allow-empty -m "Test direct push"
   git push origin main  # This should fail
   ```

2. **Creating a test PR** without description (should fail validation)

3. **Creating a proper PR** following all guidelines (should pass after review)

## Troubleshooting

### Pull Request Checks Failing

If your PR checks are failing:

1. **Check the workflow logs** in the GitHub Actions tab
2. **Run tests locally**:
   ```bash
   npm run lint
   npm run build
   npm test
   ```
3. **Fix any issues** and push again
4. **Wait for checks to re-run** automatically

### Cannot Push to Main

This is expected behavior. Always create a pull request instead of pushing directly to main.

### Status Checks Not Running

Ensure:
- Workflows are enabled in repository settings
- Branch protection workflow is in `.github/workflows/`
- CI workflow is in `.github/workflows/`
- GitHub Actions has proper permissions

## Benefits

Branch protection provides:

- 🛡️ **Protection against accidental changes** - No direct pushes to main
- 👥 **Code review requirement** - All changes reviewed by code owners
- ✅ **Quality assurance** - Automated testing and linting
- 🔒 **Security scanning** - CodeQL catches vulnerabilities
- 📝 **Clear change tracking** - All changes via pull requests
- 🔄 **Consistent process** - Same workflow for all contributors
- 🎯 **Reduced bugs** - Multiple validation layers

## Maintenance

### Updating Protection Rules

To update branch protection settings:
1. Modify `.github/workflows/branch-protection.yml` for workflow changes
2. Update `.github/CODEOWNERS` for ownership changes
3. Adjust GitHub UI settings for protection rule changes

### Adding New Status Checks

When adding new CI workflows:
1. Create the workflow in `.github/workflows/`
2. Add the workflow job name to required status checks in GitHub settings
3. Test with a pull request to ensure it works

## References

- [GitHub Branch Protection Documentation](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-protected-branches/about-protected-branches)
- [CODEOWNERS Documentation](https://docs.github.com/en/repositories/managing-your-repositorys-settings-and-features/customizing-your-repository/about-code-owners)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)

## Support

For questions or issues with branch protection:
1. Check workflow logs in GitHub Actions
2. Review this documentation
3. Contact repository administrators
4. Open an issue in the repository
