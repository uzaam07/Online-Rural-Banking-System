<?php
echo "MySQL Service Helper\n\n";

// Check service status
$output = [];
exec('sc query MySQL90', $output);
$serviceStatus = implode("\n", $output);

echo "Current MySQL Service Status:\n";
echo $serviceStatus . "\n\n";

if (strpos($serviceStatus, 'RUNNING') !== false) {
    echo "MySQL service is running.\n";
} else {
    echo "MySQL service is not running. Attempting to start...\n";
    exec('net start MySQL90', $startOutput);
    echo implode("\n", $startOutput) . "\n\n";
    
    // Check status again
    exec('sc query MySQL90', $output);
    $serviceStatus = implode("\n", $output);
    echo "Updated MySQL Service Status:\n";
    echo $serviceStatus . "\n\n";
}

echo "Now try these steps:\n\n";
echo "1. Open Command Prompt as Administrator\n";
echo "2. Run these commands:\n\n";
echo "   cd \"C:\\Program Files\\MySQL\\MySQL Server 9.0\\bin\"\n";
echo "   mysql -u root -p\n\n";
echo "3. When prompted for password, just press Enter\n\n";
echo "4. If you get access denied, run these commands in MySQL Workbench:\n\n";
echo "   ALTER USER 'root'@'localhost' IDENTIFIED BY '';\n";
echo "   FLUSH PRIVILEGES;\n\n";
echo "5. After running those commands, try the mysql -u root command again\n";
?> 
