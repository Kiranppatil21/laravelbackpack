#!/bin/bash

echo "🚀 Enhanced Visitor Management System - Frontend Setup"
echo "=================================================="
echo ""

# Check if server is running
echo "🔍 Checking if Laravel server is running..."
if curl -s http://127.0.0.1:8000/api/mobile/config > /dev/null; then
    echo "✅ Laravel server is running"
else
    echo "❌ Laravel server is not running"
    echo "💡 Please start it with: php artisan serve"
    echo ""
fi

# Show available frontend interfaces
echo "📱 Available Frontend Interfaces:"
echo "================================="
echo ""
echo "1. 🏠 Main Interface:"
echo "   http://127.0.0.1:8000/visitor-frontend-index.html"
echo ""
echo "2. 🔑 Login Interface (Get Auth Token):"
echo "   http://127.0.0.1:8000/login.html"
echo ""
echo "3. 📊 Test Dashboard (Full Features):"
echo "   http://127.0.0.1:8000/visitor-test-dashboard.html"
echo ""

# Test credentials
echo "🔐 Test Credentials:"
echo "==================="
echo "Email: admin@test.com"
echo "Password: password"
echo ""

# Quick test
echo "🧪 Quick API Test:"
echo "=================="
if curl -s http://127.0.0.1:8000/api/mobile/config | python3 -c "
import sys, json
try:
    data = json.load(sys.stdin)
    print('✅ Mobile Config API: SUCCESS')
    print(f'   App Version: {data[\"config\"][\"app_version\"]}')
    print(f'   Features: {len(data[\"config\"][\"features\"])} available')
except:
    print('❌ Mobile Config API: FAILED')
" 2>/dev/null; then
    echo ""
else
    echo "❌ API test failed - server may not be running"
    echo ""
fi

echo "🎯 Next Steps:"
echo "============="
echo "1. Open: http://127.0.0.1:8000/visitor-frontend-index.html"
echo "2. Get auth token from login page"
echo "3. Test all features in the dashboard"
echo "4. Create visitors and test mobile features"
echo ""
echo "🎉 Frontend testing environment is ready!"