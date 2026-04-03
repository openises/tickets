#!/bin/bash
#
# Pre-commit hook: Scan staged files for potential secrets
#
# Install: cp .github/hooks/pre-commit-secrets.sh .git/hooks/pre-commit && chmod +x .git/hooks/pre-commit
#
# Checks for:
# - Hardcoded passwords (password = 'xxx', passwd = 'xxx')
# - API keys (apikey, api_key with literal values)
# - Private keys (BEGIN RSA PRIVATE KEY, etc.)
# - AWS/cloud credentials
# - Database connection strings with embedded passwords
# - MySQL root with empty password

RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No color

ERRORS=0
WARNINGS=0

# Get staged files (only added/modified, not deleted)
STAGED=$(git diff --cached --name-only --diff-filter=ACM | grep -E '\.(php|js|py|sh|yml|yaml|json|env|ini|conf|cfg|sql)$')

if [ -z "$STAGED" ]; then
    exit 0
fi

echo "Scanning staged files for potential secrets..."

for FILE in $STAGED; do
    # Skip vendor/lib directories
    if echo "$FILE" | grep -qE '^(lib/|vendor/|node_modules/|assets/vendor/)'; then
        continue
    fi

    CONTENT=$(git show ":$FILE" 2>/dev/null)
    if [ -z "$CONTENT" ]; then
        continue
    fi

    # Check for hardcoded passwords
    if echo "$CONTENT" | grep -nE "(password|passwd|pass)\s*=\s*['\"][^'\"]{3,}['\"]" | grep -vE "(get_variable|getenv|config|placeholder|example|default|_PASS|PASS:-)" > /dev/null 2>&1; then
        echo -e "${RED}BLOCKED${NC} $FILE: Possible hardcoded password"
        echo "$CONTENT" | grep -nE "(password|passwd|pass)\s*=\s*['\"][^'\"]{3,}['\"]" | grep -vE "(get_variable|getenv|config|placeholder|example|default|_PASS|PASS:-)" | head -3 | sed 's/^/  /'
        ERRORS=$((ERRORS + 1))
    fi

    # Check for API keys (literal hex/alphanumeric strings assigned to key variables)
    if echo "$CONTENT" | grep -nE "(api_key|apikey|api_secret|secret_key)\s*=\s*['\"][0-9a-fA-F]{16,}['\"]" | grep -vE "(getenv|get_variable|config|example)" > /dev/null 2>&1; then
        echo -e "${RED}BLOCKED${NC} $FILE: Possible hardcoded API key"
        echo "$CONTENT" | grep -nE "(api_key|apikey|api_secret|secret_key)\s*=\s*['\"][0-9a-fA-F]{16,}['\"]" | grep -vE "(getenv|get_variable|config|example)" | head -3 | sed 's/^/  /'
        ERRORS=$((ERRORS + 1))
    fi

    # Check for private keys
    if echo "$CONTENT" | grep -q "BEGIN.*PRIVATE KEY"; then
        echo -e "${RED}BLOCKED${NC} $FILE: Contains a private key!"
        ERRORS=$((ERRORS + 1))
    fi

    # Check for MySQL root with empty password
    if echo "$CONTENT" | grep -nE "'root'\s*,\s*''" | grep -vE "(example|comment|//|#)" > /dev/null 2>&1; then
        echo -e "${RED}BLOCKED${NC} $FILE: MySQL root with empty password"
        ERRORS=$((ERRORS + 1))
    fi

    # Check for AWS credentials
    if echo "$CONTENT" | grep -qE "(AKIA[0-9A-Z]{16}|aws_secret_access_key)" ; then
        echo -e "${RED}BLOCKED${NC} $FILE: Possible AWS credential"
        ERRORS=$((ERRORS + 1))
    fi

    # Warnings (not blocking)
    if echo "$CONTENT" | grep -qE "TODO.*password|FIXME.*secret|HACK.*key"; then
        echo -e "${YELLOW}WARNING${NC} $FILE: Contains TODO/FIXME related to secrets"
        WARNINGS=$((WARNINGS + 1))
    fi
done

if [ $ERRORS -gt 0 ]; then
    echo ""
    echo -e "${RED}COMMIT BLOCKED: $ERRORS potential secret(s) found in staged files.${NC}"
    echo "If these are false positives, you can bypass with: git commit --no-verify"
    echo "But please review carefully first!"
    exit 1
fi

if [ $WARNINGS -gt 0 ]; then
    echo -e "${YELLOW}$WARNINGS warning(s) found but not blocking commit.${NC}"
fi

exit 0
