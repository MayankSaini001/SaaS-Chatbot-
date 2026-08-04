<?php
// Cache clear karne ke liye
$commands = [
    'cd /path/to/chatbot_laravel && php artisan config:clear',
    'php artisan cache:clear',
    'php artisan view:clear',
];
echo "Done";