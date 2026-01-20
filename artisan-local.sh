#!/bin/bash
# Helper script to run artisan commands with local .env settings
# This unsets system environment variables that might override .env

unset DB_DATABASE DB_USERNAME DB_PASSWORD DB_HOST DB_PORT
php artisan "$@"
