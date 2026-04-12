# Security Policy

## Supported Versions

The following versions are currently supported with security updates:

| Version | Supported |
| ------- | --------- |
| latest  | yes       |

## Reporting a Vulnerability

**Please do not open a public issue to report security vulnerabilities.**

To report a security vulnerability, please use GitHub private vulnerability reporting:

1. Navigate to this repository on GitHub
2. Go to **Settings** > **Security** > **Report a vulnerability**
3. Fill in the details of the vulnerability

We will acknowledge receipt within **48 hours** and work with you to resolve the issue promptly.
All reports will be reviewed and investigated fairly, and we will keep you informed of progress.

## Security Measures

This project employs the following security measures:

- **CodeQL scanning**: Automated code analysis to detect potential security vulnerabilities
- **Dependabot alerts**: Automated dependency vulnerability notifications and updates
- **Secret scanning**: GitHub secret scanning to detect accidentally committed credentials
- **Environment variables for secrets**: All secrets and sensitive values are stored as environment
  variables and are never hardcoded in source code
