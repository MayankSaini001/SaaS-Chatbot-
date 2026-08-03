<?php
// Cache clear
$base = __DIR__;
// Delete config cache
@unlink($base . '/bootstrap/cache/config.php');
@unlink($base . '/bootstrap/cache/routes-v7.php');
// Delete view cache
$viewPath = $base . '/storage/framework/views/';
foreach (glob($viewPath . '*.php') as $file) {
    @unlink($file);
}
echo "Cache cleared!";