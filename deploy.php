<?php
// Check if running as admin/root
if (posix_getuid() !== 0) {
    die("This script must be run as root/administrator\n");
}

echo "Starting deployment process...\n\n";

// Create necessary directories
$directories = array(
    'logs',
    'uploads',
    'backups',
    'temp'
);

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "Created directory: $dir\n";
        } else {
            echo "Failed to create directory: $dir\n";
        }
    } else {
        echo "Directory already exists: $dir\n";
    }
}

// Create .htaccess file for security
$htaccess = <<<EOT
Options -Indexes
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "\.(sql|log|bak|tmp)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect sensitive files
<FilesMatch "^(config\.php|deploy\.php|test_connection\.php)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Enable PHP error reporting in development
php_flag display_errors on
php_value error_reporting E_ALL

# Set default character set
AddDefaultCharset UTF-8

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/x-javascript
</IfModule>

# Set security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</IfModule>
EOT;

if (file_put_contents('.htaccess', $htaccess)) {
    echo "Created .htaccess file\n";
} else {
    echo "Failed to create .htaccess file\n";
}

// Create a backup of the database
echo "\nCreating database backup...\n";
$backup_file = 'backups/db_backup_' . date('Y-m-d_H-i-s') . '.sql';
$command = "mysqldump -u root -p bank > $backup_file";
exec($command, $output, $return_var);

if ($return_var === 0) {
    echo "Database backup created successfully: $backup_file\n";
} else {
    echo "Failed to create database backup\n";
}

// Set file permissions
echo "\nSetting file permissions...\n";
$files = glob('*.php');
foreach ($files as $file) {
    chmod($file, 0644);
    echo "Set permissions for: $file\n";
}

// Create a simple health check file
$health_check = <<<EOT
<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'version' => '1.0.0'
]);
EOT;

if (file_put_contents('health.php', $health_check)) {
    echo "\nCreated health check file\n";
} else {
    echo "\nFailed to create health check file\n";
}

echo "\nDeployment completed!\n";
echo "Please check the following:\n";
echo "1. Database connection settings in config files\n";
echo "2. File permissions (especially for uploads and logs)\n";
echo "3. Web server configuration (Apache/Nginx)\n";
echo "4. SSL certificate if using HTTPS\n";
?> 
