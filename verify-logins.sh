#!/bin/bash

# Login Verification Script for Security Service SaaS
# This script tests all user logins to verify they work correctly

echo "🔐 Security Service SaaS - Login Verification Script"
echo "=================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Server configuration
SERVER_URL="http://localhost:8000"
LOGIN_ENDPOINT="$SERVER_URL/login"

# Test users array
declare -A TEST_USERS
TEST_USERS["Super Admin"]="super_admin@example.test"
TEST_USERS["Agency Owner"]="agency_owner@example.test" 
TEST_USERS["HR"]="hr@example.test"
TEST_USERS["Client"]="client@example.test"
TEST_USERS["Guard/Employee"]="guard/employee@example.test"
TEST_USERS["Visitor"]="visitor@example.test"
TEST_USERS["Police"]="police@example.test"

PASSWORD="password"

echo -e "${BLUE}Starting login verification tests...${NC}\n"

# Function to check if server is running
check_server() {
    echo -n "🌐 Checking if Laravel server is running... "
    
    if curl -s -f "$SERVER_URL" > /dev/null 2>&1; then
        echo -e "${GREEN}✅ Server is running${NC}"
        return 0
    else
        echo -e "${RED}❌ Server is not running${NC}"
        echo -e "${YELLOW}💡 Please start the server with: php artisan serve${NC}"
        return 1
    fi
}

# Function to get CSRF token
get_csrf_token() {
    local csrf_token=$(curl -s -c /tmp/cookies.txt "$LOGIN_ENDPOINT" | grep -o 'name="_token" value="[^"]*"' | cut -d'"' -f4)
    echo "$csrf_token"
}

# Function to test login
test_login() {
    local role="$1"
    local email="$2"
    local password="$3"
    
    echo -n "🔑 Testing login for $role ($email)... "
    
    # Get CSRF token first
    local csrf_token=$(get_csrf_token)
    
    if [ -z "$csrf_token" ]; then
        echo -e "${RED}❌ Could not get CSRF token${NC}"
        return 1
    fi
    
    # Attempt login
    local response=$(curl -s -w "%{http_code}" -b /tmp/cookies.txt -c /tmp/cookies.txt \
        -X POST "$LOGIN_ENDPOINT" \
        -H "Content-Type: application/x-www-form-urlencoded" \
        -d "_token=$csrf_token&email=$email&password=$password" \
        -L)
    
    local http_code="${response: -3}"
    
    # Check if login was successful (should redirect to dashboard - 200 or 302)
    if [ "$http_code" = "200" ] || [ "$http_code" = "302" ]; then
        # Check if we can access dashboard
        local dashboard_response=$(curl -s -w "%{http_code}" -b /tmp/cookies.txt "$SERVER_URL/dashboard")
        local dashboard_code="${dashboard_response: -3}"
        
        if [ "$dashboard_code" = "200" ]; then
            echo -e "${GREEN}✅ Success${NC}"
            
            # Get user info from dashboard
            local user_info=$(curl -s -b /tmp/cookies.txt "$SERVER_URL/dashboard" | grep -o "Welcome.*" | head -1 | cut -d'<' -f1 2>/dev/null || echo "Dashboard accessible")
            echo -e "   ${BLUE}→ $user_info${NC}"
            
            return 0
        else
            echo -e "${YELLOW}⚠️  Login successful but dashboard access failed (code: $dashboard_code)${NC}"
            return 1
        fi
    else
        echo -e "${RED}❌ Failed (HTTP code: $http_code)${NC}"
        return 1
    fi
}

# Function to test database connectivity
test_database() {
    echo -n "🗄️  Testing database connectivity... "
    
    cd /Users/admin/Desktop/laravelbackpack
    
    local db_test=$(php artisan tinker --execute="
        try {
            \$userCount = App\Models\User::count();
            echo 'Users in database: ' . \$userCount;
        } catch (Exception \$e) {
            echo 'Database error: ' . \$e->getMessage();
        }
    " 2>&1)
    
    if [[ "$db_test" == *"Users in database:"* ]]; then
        echo -e "${GREEN}✅ Connected${NC}"
        echo -e "   ${BLUE}→ $db_test${NC}"
        return 0
    else
        echo -e "${RED}❌ Connection failed${NC}"
        echo -e "   ${RED}→ $db_test${NC}"
        return 1
    fi
}

# Function to verify user roles
verify_user_roles() {
    echo -e "\n📋 Verifying user roles in database..."
    
    cd /Users/admin/Desktop/laravelbackpack
    
    php artisan tinker --execute="
        use App\Models\User;
        echo '=== USER ROLE VERIFICATION ===' . PHP_EOL;
        
        \$testUsers = [
            'super_admin@example.test',
            'agency_owner@example.test', 
            'hr@example.test',
            'client@example.test',
            'guard/employee@example.test',
            'visitor@example.test',
            'police@example.test'
        ];
        
        foreach(\$testUsers as \$email) {
            \$user = User::where('email', \$email)->with('roles')->first();
            if(\$user) {
                \$roles = \$user->roles->pluck('name')->join(', ') ?: 'No roles assigned';
                echo \"✓ \$email - Roles: \$roles\" . PHP_EOL;
            } else {
                echo \"✗ \$email - User not found\" . PHP_EOL;
            }
        }
    "
}

# Function to test API endpoints
test_api_access() {
    echo -e "\n🔌 Testing API access..."
    
    local api_health=$(curl -s -w "%{http_code}" "$SERVER_URL/api/health" 2>/dev/null)
    local api_code="${api_health: -3}"
    
    echo -n "   🏥 API Health endpoint... "
    if [ "$api_code" = "200" ]; then
        echo -e "${GREEN}✅ Working${NC}"
    else
        echo -e "${YELLOW}⚠️  Not accessible (code: $api_code)${NC}"
    fi
}

# Main execution
main() {
    # Check if we're in the right directory
    if [ ! -f "artisan" ]; then
        echo -e "${RED}❌ Please run this script from the Laravel project root directory${NC}"
        exit 1
    fi
    
    # Test database connectivity first
    if ! test_database; then
        echo -e "\n${YELLOW}💡 Try running: php artisan migrate --seed${NC}"
        exit 1
    fi
    
    # Verify user roles
    verify_user_roles
    
    echo -e "\n🌐 Testing server connectivity..."
    
    # Check server
    if ! check_server; then
        echo -e "\n${YELLOW}🚀 Attempting to start Laravel server...${NC}"
        php artisan serve --host=127.0.0.1 --port=8000 &
        SERVER_PID=$!
        echo "   Server started with PID: $SERVER_PID"
        sleep 3
        
        if ! check_server; then
            echo -e "${RED}❌ Failed to start server${NC}"
            kill $SERVER_PID 2>/dev/null
            exit 1
        fi
    fi
    
    echo -e "\n🔐 Testing user logins..."
    
    # Test each user login
    local success_count=0
    local total_count=${#TEST_USERS[@]}
    
    for role in "${!TEST_USERS[@]}"; do
        email="${TEST_USERS[$role]}"
        if test_login "$role" "$email" "$PASSWORD"; then
            ((success_count++))
        fi
        echo "" # Empty line for readability
    done
    
    # Test API access
    test_api_access
    
    # Summary
    echo -e "\n📊 SUMMARY"
    echo "=========="
    echo -e "Total users tested: ${BLUE}$total_count${NC}"
    echo -e "Successful logins: ${GREEN}$success_count${NC}"
    echo -e "Failed logins: ${RED}$((total_count - success_count))${NC}"
    
    if [ $success_count -eq $total_count ]; then
        echo -e "\n${GREEN}🎉 All login tests passed! Your Security Service SaaS is ready!${NC}"
        echo -e "${BLUE}🌐 Access the application at: $SERVER_URL${NC}"
    else
        echo -e "\n${YELLOW}⚠️  Some login tests failed. Check the output above for details.${NC}"
    fi
    
    # Cleanup
    rm -f /tmp/cookies.txt
    
    # Stop server if we started it
    if [ ! -z "$SERVER_PID" ]; then
        echo -e "\n${YELLOW}🛑 Stopping test server (PID: $SERVER_PID)...${NC}"
        kill $SERVER_PID 2>/dev/null
    fi
}

# Run main function
main "$@"