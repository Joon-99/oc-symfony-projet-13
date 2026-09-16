#!/usr/bin/env bash
set -u

GREEN='\033[7;32m'
RED='\033[7;31m'
RESET='\033[0m'

missing=0

print_status() {
    local label="$1"
    local status="$2"

    if [ "$status" = "OK" ]; then
        printf '%b%s: %s%b\n' "$GREEN" "$label" "$status" "$RESET"
    else
        printf '%b%s: %s%b\n' "$RED" "$label" "$status" "$RESET"
    fi
}
print_message() {
    local text="$1"
    local color="$2"
    printf '%b%s%b\n' "$color" "$text" "$RESET"
}
check_command() {
    local name="$1"
    if command -v "$name" >/dev/null 2>&1; then
        print_status "$name" "OK"
    else
        print_status "$name" "MISSING"
        missing=1
    fi
}

check_extension() {
    local ext="$1"
    if php -m 2>/dev/null | grep -q "^${ext}$"; then
        print_status "$ext" "OK"
    else
        print_status "$ext" "MISSING"
        missing=1
    fi
}

echo
echo "Checking project requirements..."
echo

check_command php
for ext in bcmath ctype gd iconv pdo_pgsql; do
    check_extension "$ext"
done

echo

check_command composer
check_command docker
check_command docker-compose || true


if php -i 2>/dev/null | grep -q 'PDO support => enabled'; then
    print_status "PDO" "OK"
else
    print_status "PDO" "MISSING"
    missing=1
fi

if php -i 2>/dev/null | grep -q 'PDO drivers =>'; then
    print_status "PDO driver info" "OK"
else
    print_status "PDO driver info" "MISSING"
    missing=1
fi

echo
if [ "$missing" -eq 0 ]; then
    print_message "All required tools and extensions appear to be installed." "$GREEN"
    exit 0
else
    print_message "One or more required tools or extensions are missing." "$RED"
    exit 1
fi
