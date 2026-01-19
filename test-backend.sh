#!/bin/bash

echo "========================================="
echo "Testing Laravel Backend Pages"
echo "========================================="
echo ""

# Check if server is running
if ! curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8001 | grep -q "200\|302"; then
    echo "⚠️  Server not running. Starting server..."
    php artisan serve --port=8001 &
    sleep 3
fi

echo "1. Testing Dashboard..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8001/admin/dashboard)
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
    echo "   ✓ Dashboard: OK (HTTP $HTTP_CODE)"
else
    echo "   ✗ Dashboard: FAILED (HTTP $HTTP_CODE)"
fi

echo ""
echo "2. Testing Client List..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8001/admin/client)
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
    echo "   ✓ Client List: OK (HTTP $HTTP_CODE)"
else
    echo "   ✗ Client List: FAILED (HTTP $HTTP_CODE)"
fi

echo ""
echo "3. Testing Employee List..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8001/admin/employee)
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
    echo "   ✓ Employee List: OK (HTTP $HTTP_CODE)"
else
    echo "   ✗ Employee List: FAILED (HTTP $HTTP_CODE)"
fi

echo ""
echo "4. Testing Invoice List..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8001/admin/client-invoice)
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
    echo "   ✓ Invoice List: OK (HTTP $HTTP_CODE)"
else
    echo "   ✗ Invoice List: FAILED (HTTP $HTTP_CODE)"
fi

echo ""
echo "========================================="
echo "Database Status"
echo "========================================="

mysql -u root laravelbackpack_central -e "
SELECT 
    (SELECT COUNT(*) FROM clients) as total_clients,
    (SELECT COUNT(*) FROM employees) as total_employees,
    (SELECT COUNT(*) FROM client_invoices) as total_invoices,
    (SELECT COUNT(*) FROM users) as total_users;
" 2>/dev/null

echo ""
echo "========================================="
echo "Recent Errors (if any)"
echo "========================================="
tail -n 20 storage/logs/laravel.log 2>/dev/null | grep -i "error\|exception" || echo "No recent errors found"

echo ""
echo "Test complete!"
