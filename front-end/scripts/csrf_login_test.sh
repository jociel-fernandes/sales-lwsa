#!/usr/bin/env bash
# Simple helper to test Sanctum CSRF + login flow from the shell
# Usage: ./csrf_login_test.sh [API_BASE] [EMAIL] [PASSWORD]
API_BASE=${1:-http://localhost:8007}
EMAIL=${2:-admin@test.io}
PASSWORD=${3:-password}
CK=/tmp/lwsa_csrf_ck
rm -f $CK
# get csrf cookie
curl -sS -c $CK -b $CK $API_BASE/sanctum/csrf-cookie -o /dev/null
# extract XSRF token from cookie jar and decode
XSRF=$(grep XSRF-TOKEN $CK | awk '{print $7}' | sed 's/%3D/=/g')
echo "XSRF: $XSRF"
# login (include X-XSRF-TOKEN header)
curl -sS -b $CK -c $CK -H "X-XSRF-TOKEN: $XSRF" -H "Content-Type: application/json" -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}" $API_BASE/auth/login -w "\nHTTP_CODE:%{http_code}\n" -o /tmp/lwsa_login_out.txt
cat /tmp/lwsa_login_out.txt || true
echo
# check api/user
curl -sS -b $CK $API_BASE/api/user -w "\nHTTP_CODE:%{http_code}\n"
# cleanup
rm -f $CK /tmp/lwsa_login_out.txt
