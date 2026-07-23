<?php
echo "Checking MySQL connection...\n\n";

// Try different connection methods
$connection_methods = [
    ['host' => 'localhost', 'user' => 'root', 'pass' => ''],
    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => ''],
    ['host' => 'localhost:3306', 'user' => 'root', 'pass' => '']
];

$connected = false;
foreach ($connection_methods as $method) {
    echo "Trying to connect to {$method['host']}...\n";
    $conn = @mysqli_connect($method['host'], $method['user'], $method['pass']);
    
    if ($conn) {
        echo "Successfully connected to MySQL!\n";
        echo "You can now run setup_database.php\n";
        mysqli_close($conn);
        $connected = true;
        break;
    } else {
        echo "Failed to connect: " . mysqli_connect_error() . "\n\n";
    }
}

if (!$connected) {
    echo "\nAll connection attempts failed. Please try these steps:\n\n";
    
    echo "1. Open MySQL Workbench\n";
    echo "2. Connect to your local instance\n";
    echo "3. Run these SQL commands:\n";
    echo "   ALTER USER 'root'@'localhost' IDENTIFIED BY '';\n";
    echo "   FLUSH PRIVILEGES;\n\n";
    
    echo "4. After running those commands, try this script again.\n";
    
    // Check Windows services
    echo "\nChecking Windows Services...\n";
    $output = [];
    exec('sc query MySQL', $output);
    if (empty($output)) {
        echo "MySQL service not found. Please check your installation.\n";
    } else {
        echo "MySQL service found. Current status:\n";
        echo implode("\n", $output);
    }
}
?> 
