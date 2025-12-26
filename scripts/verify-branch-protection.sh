#!/bin/bash
# Branch Protection Verification Script
# This script verifies that branch protection mechanisms are in place

echo "🔍 Verifying Branch Protection Setup..."
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

SUCCESS=0
WARNINGS=0
FAILURES=0

# Check 1: CODEOWNERS file exists
echo -n "Checking CODEOWNERS file... "
if [ -f ".github/CODEOWNERS" ]; then
    echo -e "${GREEN}✓ Found${NC}"
    ((SUCCESS++))
    
    # Verify it has owners
    if grep -q "@schoedel-learn" .github/CODEOWNERS; then
        echo -e "  ${GREEN}✓ Valid code owners configured${NC}"
        ((SUCCESS++))
    else
        echo -e "  ${RED}✗ No valid code owners found${NC}"
        ((FAILURES++))
    fi
else
    echo -e "${RED}✗ Missing${NC}"
    ((FAILURES++))
fi
echo ""

# Check 2: Branch Protection workflow exists
echo -n "Checking Branch Protection workflow... "
if [ -f ".github/workflows/branch-protection.yml" ]; then
    echo -e "${GREEN}✓ Found${NC}"
    ((SUCCESS++))
    
    # Validate YAML syntax with proper error handling
    if python3 -c "import yaml, sys; yaml.safe_load(open('.github/workflows/branch-protection.yml'))" 2>&1 | grep -q "Error"; then
        echo -e "  ${RED}✗ Invalid YAML syntax${NC}"
        ((FAILURES++))
    else
        echo -e "  ${GREEN}✓ Valid YAML syntax${NC}"
        ((SUCCESS++))
    fi
else
    echo -e "${RED}✗ Missing${NC}"
    ((FAILURES++))
fi
echo ""

# Check 3: CI workflow exists
echo -n "Checking CI workflow... "
if [ -f ".github/workflows/ci.yml" ]; then
    echo -e "${GREEN}✓ Found${NC}"
    ((SUCCESS++))
    
    # Validate YAML syntax with proper error handling
    if python3 -c "import yaml, sys; yaml.safe_load(open('.github/workflows/ci.yml'))" 2>&1 | grep -q "Error"; then
        echo -e "  ${RED}✗ Invalid YAML syntax${NC}"
        ((FAILURES++))
    else
        echo -e "  ${GREEN}✓ Valid YAML syntax${NC}"
        ((SUCCESS++))
    fi
else
    echo -e "${RED}✗ Missing${NC}"
    ((FAILURES++))
fi
echo ""

# Check 4: CodeQL workflow exists
echo -n "Checking CodeQL workflow... "
if [ -f ".github/workflows/codeql.yml" ]; then
    echo -e "${GREEN}✓ Found${NC}"
    ((SUCCESS++))
else
    echo -e "${YELLOW}⚠ Missing (optional)${NC}"
    ((WARNINGS++))
fi
echo ""

# Check 5: Documentation exists
echo -n "Checking branch protection documentation... "
if [ -f "docs/BRANCH_PROTECTION.md" ]; then
    echo -e "${GREEN}✓ Found${NC}"
    ((SUCCESS++))
else
    echo -e "${YELLOW}⚠ Missing${NC}"
    ((WARNINGS++))
fi
echo ""

# Check 6: README mentions branch protection
echo -n "Checking README documentation... "
if grep -q "Branch Protection" README.md; then
    echo -e "${GREEN}✓ Branch Protection section found in README${NC}"
    ((SUCCESS++))
else
    echo -e "${YELLOW}⚠ No Branch Protection section in README${NC}"
    ((WARNINGS++))
fi
echo ""

# Check 7: Current branch
echo -n "Checking current branch... "
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" = "main" ]; then
    echo -e "${YELLOW}⚠ You are on the main branch${NC}"
    echo -e "  ${YELLOW}  Branch protection should prevent direct pushes${NC}"
    ((WARNINGS++))
else
    echo -e "${GREEN}✓ On feature branch: $CURRENT_BRANCH${NC}"
    ((SUCCESS++))
fi
echo ""

# Check 8: Verify package.json has required scripts
echo -n "Checking package.json scripts... "
if [ -f "package.json" ]; then
    # Use jq if available, otherwise fall back to grep
    if command -v jq &> /dev/null; then
        HAS_BUILD=$(jq -r '.scripts.build // empty' package.json)
        HAS_LINT=$(jq -r '.scripts.lint // empty' package.json)
        HAS_TEST=$(jq -r '.scripts.test // empty' package.json)
        
        if [ -n "$HAS_BUILD" ] && [ -n "$HAS_LINT" ] && [ -n "$HAS_TEST" ]; then
            echo -e "${GREEN}✓ Required scripts found (build, lint, test)${NC}"
            ((SUCCESS++))
        else
            echo -e "${YELLOW}⚠ Some required scripts missing${NC}"
            ((WARNINGS++))
        fi
    else
        # Fallback to basic check without jq
        if grep -q '"build"[[:space:]]*:' package.json && \
           grep -q '"lint"[[:space:]]*:' package.json && \
           grep -q '"test"[[:space:]]*:' package.json; then
            echo -e "${GREEN}✓ Required scripts found (build, lint, test)${NC}"
            ((SUCCESS++))
        else
            echo -e "${YELLOW}⚠ Some scripts may be missing (jq not available for accurate check)${NC}"
            ((WARNINGS++))
        fi
    fi
else
    echo -e "${RED}✗ package.json not found${NC}"
    ((FAILURES++))
fi
echo ""

# Summary
echo "================================================"
echo "VERIFICATION SUMMARY"
echo "================================================"
echo -e "${GREEN}Successes: $SUCCESS${NC}"
echo -e "${YELLOW}Warnings:  $WARNINGS${NC}"
echo -e "${RED}Failures:  $FAILURES${NC}"
echo ""

if [ $FAILURES -eq 0 ]; then
    echo -e "${GREEN}✅ Branch protection mechanisms are properly configured!${NC}"
    echo ""
    echo "Next steps:"
    echo "1. Configure GitHub branch protection rules (see docs/BRANCH_PROTECTION.md)"
    echo "2. Test by creating a pull request"
    echo "3. Verify status checks run automatically"
    exit 0
else
    echo -e "${RED}❌ Some checks failed. Please review the errors above.${NC}"
    exit 1
fi
