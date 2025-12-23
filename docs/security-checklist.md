# Security Checklist for schoedel-learn/drm-catholic

**Date:** 2025-12-23  
**Reviewer:** Barry Schoedel

## Branch Protection
- [ ] `main` requires PR with 1 approval
- [ ] Status checks required (branches up-to-date)
- [ ] Conversation resolution required
- [ ] Force-push disabled
- [ ] Branch deletion disabled

## Dependabot
- [x] Dependabot alerts enabled (requires manual activation in Settings)
- [x] Dependabot security updates enabled (requires manual activation in Settings)
- [x] `.github/dependabot.yml` configured for Composer & GitHub Actions

## Secret Scanning
- [ ] Secret scanning enabled (requires manual activation in Settings)
- [ ] Push protection enabled (requires manual activation in Settings)

## Code Scanning
- [x] CodeQL workflow active (`.github/workflows/codeql.yml`)
- [x] Weekly scans scheduled

## Access & Auth
- [ ] SSH keys only (no password auth)
- [ ] 2FA enabled on GitHub account
- [ ] Repository visibility reviewed (public vs. private tradeoffs)

## Private Vulnerability Reporting
- [ ] Enabled when public (requires manual activation in Settings)

## Secrets Management
- [ ] No secrets in code
- [ ] GitHub Secrets used for CI/CD credentials

## Next Steps (Requires Manual GitHub Web UI Configuration)
1. Enable Dependabot: https://github.com/schoedel-learn/drm-catholic/settings/security_analysis
2. Enable Secret Scanning: https://github.com/schoedel-learn/drm-catholic/settings/security_analysis
3. Configure Branch Protection: https://github.com/schoedel-learn/drm-catholic/settings/branches
4. Review security overview: https://github.com/schoedel-learn/drm-catholic/security
