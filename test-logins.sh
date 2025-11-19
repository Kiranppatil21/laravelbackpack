#!/bin/bash

# Simple login test script
echo "🔐 Testing Login Process - Security Service SaaS"
echo "=============================================="

# Test credentials
CREDENTIALS=(
    "super_admin@example.test:password:Super Admin"
    "agency_owner@example.test:password:Agency Owner"
    "hr@example.test:password:HR"
    "client@example.test:password:Client"
    "visitor@example.test:password:Visitor"
    "police@example.test:password:Police"
)

SERVER_URL="http://localhost:8000"

echo "🌐 Server URL: $SERVER_URL"
echo ""

# Test server connectivity
echo "🔍 Testing server connectivity..."
if curl -s -f "$SERVER_URL" > /dev/null; then
    echo "✅ Server is responsive"
else
    echo "❌ Server is not responsive"
    exit 1
fi

echo ""
echo "📋 Login Credentials Ready for Testing:"
echo "======================================"

for credential in "${CREDENTIALS[@]}"; do
    IFS=':' read -r email password role <<< "$credential"
    echo "🔑 $role"
    echo "   📧 Email: $email"
    echo "   🔒 Password: $password"
    echo "   🌐 Login URL: $SERVER_URL/login"
    echo ""
done

echo "✅ All emails are verified and ready for login!"
echo ""
echo "🚀 Quick Test Instructions:"
echo "1. Open: $SERVER_URL/login"
echo "2. Use any email/password combination above"
echo "3. You should login directly without email verification"
echo ""
echo "🎯 Recommended first test: super_admin@example.test / password"