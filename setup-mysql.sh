#!/bin/bash

# Laravel Multi-tenant MySQL Setup Script
# This script creates the central database and sets up the project for MySQL

set -e

echo "================================================"
echo "Laravel Multi-tenant MySQL Setup"
echo "================================================"
echo ""

# Configuration
DB_NAME="laravelbackpack_central"
DB_USER="root"
DB_PASS=""
DB_HOST="127.0.0.1"
DB_PORT="3306"

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}Step 1: Checking MySQL connection...${NC}"
# Use XAMPP MySQL path if available
MYSQL_CMD="mysql"
if [ -f "/Applications/XAMPP/xamppfiles/bin/mysql" ]; then
    MYSQL_CMD="/Applications/XAMPP/xamppfiles/bin/mysql"
fi

if $MYSQL_CMD -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" -e "SELECT 1;" &> /dev/null; then
    echo -e "${GREEN}✓ MySQL connection successful${NC}"
else
    echo -e "${RED}✗ MySQL connection failed!${NC}"
    echo -e "${YELLOW}Please ensure MySQL is running and credentials are correct.${NC}"
    echo -e "${YELLOW}If you're using MAMP/XAMPP/WAMP, make sure the MySQL service is started.${NC}"
    exit 1
fi

echo ""
echo -e "${BLUE}Step 2: Creating central database...${NC}"
$MYSQL_CMD -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" <<EOF
DROP DATABASE IF EXISTS ${DB_NAME};
CREATE DATABASE ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EOF

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Central database '${DB_NAME}' created successfully${NC}"
else
    echo -e "${RED}✗ Failed to create database${NC}"
    exit 1
fi

echo ""
echo -e "${BLUE}Step 3: Running Laravel migrations...${NC}"
php artisan migrate --force

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Migrations completed successfully${NC}"
else
    echo -e "${RED}✗ Migrations failed${NC}"
    exit 1
fi

echo ""
echo -e "${BLUE}Step 4: Seeding database...${NC}"
php artisan db:seed --force

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Database seeding completed${NC}"
else
    echo -e "${YELLOW}⚠ Seeding completed with warnings (this is usually OK)${NC}"
fi

echo ""
echo -e "${GREEN}================================================${NC}"
echo -e "${GREEN}MySQL Setup Complete!${NC}"
echo -e "${GREEN}================================================${NC}"
echo ""
echo -e "${BLUE}Database Information:${NC}"
echo -e "  Host: ${DB_HOST}:${DB_PORT}"
echo -e "  Central Database: ${DB_NAME}"
echo -e "  Username: ${DB_USER}"
echo ""
echo -e "${BLUE}PHPMyAdmin Access:${NC}"
echo -e "  URL: http://localhost/phpmyadmin"
echo -e "  Server: ${DB_HOST}"
echo -e "  Username: ${DB_USER}"
echo -e "  Password: (your MySQL root password)"
echo ""
echo -e "${BLUE}Tenant Databases:${NC}"
echo -e "  Tenant databases will be automatically created with names like:"
echo -e "  tenant<uuid> (e.g., tenant1234-5678-90ab-cdef)"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo -e "  1. Access PHPMyAdmin at: http://localhost/phpmyadmin"
echo -e "  2. Login with MySQL credentials"
echo -e "  3. View '${DB_NAME}' database for central tables"
echo -e "  4. Create a tenant: php artisan tenants:create"
echo -e "  5. Start development server: composer run dev"
echo ""
