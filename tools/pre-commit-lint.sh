#!/bin/bash
#
# Pre-commit lint check for Tickets CAD
# Runs php -l on all staged PHP files to catch syntax errors before committing.
#
# Usage:
#   ./tools/pre-commit-lint.sh           # Check staged files
#   ./tools/pre-commit-lint.sh --all     # Check all PHP files
#
# To install as a git hook:
#   cp tools/pre-commit-lint.sh .git/hooks/pre-commit
#   chmod +x .git/hooks/pre-commit

set -e

ERRORS=0
CHECKED=0

if [ "$1" = "--all" ]; then
    echo "Checking ALL PHP files..."
    FILES=$(find . -name "*.php" -not -path "./vendor/*" -not -path "./node_modules/*")
else
    echo "Checking staged PHP files..."
    FILES=$(git diff --cached --name-only --diff-filter=ACM | grep '\.php$' || true)
fi

if [ -z "$FILES" ]; then
    echo "No PHP files to check."
    exit 0
fi

for FILE in $FILES; do
    if [ -f "$FILE" ]; then
        OUTPUT=$(php -l "$FILE" 2>&1)
        if [ $? -ne 0 ]; then
            echo "SYNTAX ERROR: $FILE"
            echo "  $OUTPUT"
            ERRORS=$((ERRORS + 1))
        fi
        CHECKED=$((CHECKED + 1))
    fi
done

echo ""
echo "Checked $CHECKED files, found $ERRORS errors."

if [ $ERRORS -gt 0 ]; then
    echo "COMMIT BLOCKED: Fix syntax errors before committing."
    exit 1
fi

echo "All files passed syntax check."
exit 0
