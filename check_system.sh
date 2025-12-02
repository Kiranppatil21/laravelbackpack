#!/bin/bash

echo "=== Client Management System Verification ==="
echo

echo "1. Database Tables Check:"
cd /Users/admin/Desktop/laravelbackpack

# Check central tables
echo "Central DB Tables:"
php artisan migrate:status | grep -E "(clients|companies|designations)" || echo "   No client-related tables in central DB"

echo
echo "2. Tenant Database Check:"
# Check if tenant exists and run verification
TENANT_ID=$(php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
\$tenant = \App\Models\Tenant::first();
if(\$tenant) echo \$tenant->id;
else echo 'none';
")

if [ "$TENANT_ID" != "none" ]; then
    echo "Found tenant: $TENANT_ID"
    echo "Tenant Tables:"
    php artisan tenants:run "migrate:status" --tenants="$TENANT_ID" | grep -E "(clients|companies|designations)" || echo "   No additional tables in tenant DB"
else
    echo "No tenants found"
fi

echo
echo "3. Model Files Check:"
ls -la app/Models/ | grep -E "(Client|Company|Designation)" || echo "   Model files not found"

echo
echo "4. Migration Files Check:"
echo "Tenant migrations:"
ls -la database/migrations/tenant/ | grep -E "(client|company|designation)" || echo "   No tenant migration files"
echo "Central migrations:"
ls -la database/migrations/ | grep -E "(client|company|designation)" || echo "   No central migration files"

echo
echo "5. Controller Check:"
ls -la app/Http/Controllers/ | grep -i client || echo "   No client controller"

echo
echo "6. React Component Check:"
ls -la resources/js/Pages/Clients/ || echo "   No client pages directory"

echo
echo "=== Summary ==="