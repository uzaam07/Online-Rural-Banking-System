<?php
echo "MySQL Password Reset Helper\n\n";

// Create the init file
$init_content = "ALTER USER 'root'@'localhost' IDENTIFIED BY '';";
file_put_contents('C:\\mysql-init.txt', $init_content);
echo "Created init file at C:\\mysql-init.txt\n\n";

// Check for MySQL services
echo "Checking for MySQL services...\n\n";
$output = [];
exec('sc query type= service | findstr /i "mysql"', $output);
if (!empty($output)) {
    echo "Found these MySQL-related services:\n";
    foreach ($output as $line) {
        echo $line . "\n";
    }
    echo "\n";
} else {
    echo "No MySQL services found. Please check your installation.\n";
}

echo "Now follow these steps:\n\n";
echo "1. Open Command Prompt as Administrator\n";
echo "2. Run these commands:\n\n";
echo "   net stop MySQL90\n";
echo "   cd \"C:\\Program Files\\MySQL\\MySQL Server 9.0\\bin\"\n";
echo "   mysqld --init-file=C:\\mysql-init.txt\n\n";
echo "3. Open a new Command Prompt window and run:\n\n";
echo "   cd \"C:\\Program Files\\MySQL\\MySQL Server 9.0\\bin\"\n";
echo "   mysql -u root\n\n";
echo "4. If you see the MySQL prompt, run:\n\n";
echo "   ALTER USER 'root'@'localhost' IDENTIFIED BY '';\n";
echo "   FLUSH PRIVILEGES;\n";
echo "   exit;\n\n";
echo "5. Go back to the first Command Prompt and press Ctrl+C to stop MySQL\n";
echo "6. Run these commands:\n\n";
echo "   net stop MySQL90\n";
echo "   net start MySQL90\n\n";
echo "7. After MySQL restarts, run check_mysql.php again\n";

echo "\nNote: If you're not sure about the MySQL service name:\n";
echo "1. Press Windows + R\n";
echo "2. Type 'services.msc' and press Enter\n";
echo "3. Look for a service that starts with 'MySQL'\n";
echo "4. Use that exact service name in the commands above\n";
?> 
