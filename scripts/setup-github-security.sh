#!/usr/bin/env bash
# Setup GitHub security for schoedel-learn/drm-catholic
# APA 7: OWASP Foundation. (2021). OWASP DevSecOps Guideline. 
# https://owasp.org/www-project-devsecops-guideline/

set -euo pipefail

REPO="schoedel-learn/drm-catholic"

echo "🔒 Enabling branch protection on main..."
gh api --method PUT -H "Accept: application/vnd.github+json" \
  "repos/$REPO/branches/main/protection" \
  -f required_status_checks='{"strict":true,"contexts":[]}' \
  -f enforce_admins=false \
  -f required_pull_request_reviews='{"required_approving_review_count":1,"dismiss_stale_reviews":true,"require_code_owner_reviews":false}' \
  -f restrictions=null \
  -F allow_force_pushes=false \
  -F allow_deletions=false \
  -F required_conversation_resolution=true \
  -F lock_branch=false \
  -F allow_fork_syncing=false

echo "🛡️  Enabling Dependabot alerts..."
gh api --method PUT "repos/$REPO/vulnerability-alerts"

echo "🤖 Enabling Dependabot security updates..."
gh api --method PUT "repos/$REPO/automated-security-fixes"

echo ""
echo "✅ Automated security baseline applied!"
echo ""
echo "⚠️  Manual steps still required (GitHub web UI only):"
echo "   1. Enable Secret Scanning: https://github.com/$REPO/settings/security_analysis"
echo "   2. Enable Push Protection: https://github.com/$REPO/settings/security_analysis"
echo "   3. Review all settings: https://github.com/$REPO/settings/security_analysis"
echo ""
echo "📋 See docs/security-checklist.md for complete audit"
