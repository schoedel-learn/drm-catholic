# Branch Protection Setup Guide

This guide provides step-by-step instructions for setting up branch protection on the `main` branch of the DRM Catholic repository.

## Why Branch Protection?

Branch protection helps maintain code quality and prevents accidental changes to important branches. For a public repository in early development, it's essential to:

- Prevent direct pushes to main
- Require pull request reviews before merging
- Ensure all tests pass before merging
- Maintain a clean, reviewable history

## Quick Start

### Option 1: Using GitHub Web Interface (Recommended)

1. Navigate to your repository settings: https://github.com/schoedel-learn/drm-catholic/settings/branches

2. Click **"Add branch protection rule"** (or **"Add rule"**)

3. Configure the following settings:

   **Branch name pattern:**
   ```
   main
   ```

   **Protect matching branches:**
   
   ✅ **Require a pull request before merging**
   - Require approvals: `1`
   - ✅ Dismiss stale pull request approvals when new commits are pushed
   - ✅ Require review from Code Owners (optional - only if you have CODEOWNERS file)
   
   ✅ **Require status checks to pass before merging**
   - ✅ Require branches to be up to date before merging
   - Select status checks (type to search):
     - `build (18.x)`
     - `build (20.x)` 
     - `code-quality`
   
   ✅ **Require conversation resolution before merging**
   
   ✅ **Do not allow bypassing the above settings** (includes administrators)
   
   ❌ **Allow force pushes** (keep this disabled)
   
   ❌ **Allow deletions** (keep this disabled)

4. Click **"Create"** or **"Save changes"**

### Option 2: Using GitHub Repository Rulesets (New Feature)

GitHub Rulesets provide more flexibility and better management of branch protection rules.

1. Navigate to: https://github.com/schoedel-learn/drm-catholic/settings/rules

2. Click **"New branch ruleset"**

3. Configure the ruleset:

   **Ruleset Name:**
   ```
   Protect main branch
   ```

   **Enforcement status:**
   - Select: `Active`

   **Target branches:**
   - Include by pattern: `main`

   **Branch protections:**
   
   ✅ **Restrict deletions**
   
   ✅ **Require a pull request before merging**
   - Required approvals: `1`
   - Dismiss stale pull request approvals when new commits are pushed
   - Require approval of the most recent reviewable push
   
   ✅ **Require status checks to pass**
   - ✅ Require branches to be up to date before merging
   - Add status checks:
     - `build (18.x)`
     - `build (20.x)`
     - `code-quality`
   
   ✅ **Block force pushes**
   
   ✅ **Require conversation resolution before merging**

4. **Bypass list:** Leave empty (no one can bypass, including admins)

5. Click **"Create"**

## What These Settings Do

### 1. Require Pull Request Reviews
- **Purpose:** Ensures all changes are reviewed before merging
- **Effect:** You cannot push directly to `main`; all changes must go through a pull request
- **Approvals:** At least 1 approval required from another contributor

### 2. Dismiss Stale Reviews
- **Purpose:** Ensures reviews are current
- **Effect:** If you push new commits after approval, the approval is dismissed and re-review is required

### 3. Require Status Checks
- **Purpose:** Ensures code quality before merging
- **Effect:** All CI tests must pass:
  - Build succeeds on Node.js 18.x and 20.x
  - Linting passes
  - Unit tests pass
  - Code coverage tests pass

### 4. Require Branches Up to Date
- **Purpose:** Prevents merge conflicts and integration issues
- **Effect:** Your branch must be rebased/merged with latest main before merging

### 5. Include Administrators
- **Purpose:** Enforces rules for everyone, even repository admins
- **Effect:** Even admins must follow the pull request workflow

### 6. Block Force Pushes and Deletions
- **Purpose:** Protects branch history
- **Effect:** Cannot force push or delete the main branch

## Development Workflow

With branch protection enabled, follow this workflow:

### Creating a New Feature

```bash
# 1. Create a new branch from main
git checkout main
git pull origin main
git checkout -b feature/my-new-feature

# 2. Make your changes
# ... edit files ...

# 3. Commit your changes
git add .
git commit -m "Add my new feature"

# 4. Push your branch
git push origin feature/my-new-feature

# 5. Create a pull request on GitHub
# Go to: https://github.com/schoedel-learn/drm-catholic/pulls
# Click "New pull request"
# Select your branch and create the PR

# 6. Wait for CI checks to pass
# The following checks must pass:
# - build (18.x)
# - build (20.x)
# - code-quality

# 7. Request a review (if needed)
# Assign reviewers in the PR interface

# 8. After approval and passing checks, merge the PR
```

### Working on Your Branch

```bash
# Keep your branch up to date with main
git checkout main
git pull origin main
git checkout feature/my-new-feature
git merge main  # or use: git rebase main

# Push updates
git push origin feature/my-new-feature
```

## CI/CD Integration

The branch protection is integrated with the existing GitHub Actions workflows:

### Required Status Checks

Located in `.github/workflows/ci.yml`:

1. **build (18.x)** - Tests on Node.js 18.x
   - Install dependencies
   - Run linter
   - Build TypeScript
   - Run tests

2. **build (20.x)** - Tests on Node.js 20.x
   - Install dependencies
   - Run linter
   - Build TypeScript
   - Run tests

3. **code-quality** - Code quality checks
   - Run tests with coverage
   - Generate coverage reports

All three checks must pass for a PR to be mergeable.

## Troubleshooting

### "Required status checks not found"

If you see this error when setting up branch protection:

1. Make sure you've pushed at least one commit to main after setting up CI
2. The status check names must match exactly: `build (18.x)`, `build (20.x)`, `code-quality`
3. You can set up the rule without selecting specific checks initially, then edit it after the first CI run

### "This branch is out-of-date"

This means your branch needs to be updated with the latest changes from main:

```bash
git checkout main
git pull origin main
git checkout your-branch
git merge main
git push origin your-branch
```

### "All checks have failed"

1. Click on "Details" next to the failed check
2. Review the error logs
3. Fix the issues in your branch
4. Commit and push the fixes
5. CI will automatically re-run

### "Waiting for status to be reported"

This means CI hasn't started or completed yet. Wait a few minutes for GitHub Actions to run.

## For Repository Administrators

Even as an admin, you should follow the pull request workflow. This ensures:

- Code review happens consistently
- CI tests catch issues before merging
- Changes are documented in pull requests
- The project maintains high quality standards

If you absolutely must bypass protection (emergencies only):

1. Settings → Branches → Edit the protection rule
2. Temporarily uncheck "Include administrators"
3. Make your emergency fix
4. Re-enable "Include administrators"

**Note:** This should be avoided except in critical situations.

## Configuration Reference

The recommended branch protection configuration is documented in `.github/branch-protection.yml`. This file serves as a reference and documentation but is not automatically applied by GitHub.

## Next Steps

After setting up branch protection:

1. ✅ Create a CODEOWNERS file (optional) to automatically request reviews from specific people
2. ✅ Set up additional required checks as your CI/CD pipeline grows
3. ✅ Consider requiring signed commits for additional security
4. ✅ Document your contribution guidelines in CONTRIBUTING.md

## Resources

- [GitHub Branch Protection Documentation](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-protected-branches/about-protected-branches)
- [GitHub Rulesets Documentation](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-rulesets/about-rulesets)
- [Managing a branch protection rule](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-protected-branches/managing-a-branch-protection-rule)

## Support

If you encounter any issues setting up branch protection, please:

1. Check the troubleshooting section above
2. Review the GitHub documentation links
3. Open an issue in the repository describing the problem
