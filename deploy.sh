#!/bin/bash

# Update the repository
git pull origin main

# Install any dependencies (if you're using Composer)
# composer install

# Start PHP server
php -S localhost:8080

echo "Deployment complete. PHP server running on http://localhost:8080"