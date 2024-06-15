#!/bin/bash

# ANSI color codes
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check that browser sync is installed
if ! command -v browser-sync &> /dev/null
then
    echo "${RED}browser-sync is not installed, please install it globally first${NC}"
    exit 1
fi

# Check if a file argument is provided
if [ -z "$1" ]; then
    echo "Usage: $0 <php-file>"
    exit 1
fi

# Get the PHP file from the first argument
php_file=$1

# Function to handle cleanup
cleanup() {
    echo "Stopping applications..."
    # Kill both background jobs
    kill $browser_sync_pid
    kill $php_server_pid
}

# Set up trap to call cleanup on script termination
trap cleanup SIGINT

# Start the PHP built-in server in the background
php -S localhost:1234 $php_file &
php_server_pid=$!

# Start browser-sync in the background
browser-sync start --proxy "localhost:1234/$php_file" --files "./**/*.php" &
browser_sync_pid=$!

# Wait for both background jobs to finish
wait $php_server_pid
wait $browser_sync_pid