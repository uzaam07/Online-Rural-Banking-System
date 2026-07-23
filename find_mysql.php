<?php
echo "Searching for MySQL installation...\n\n";

$possible_paths = [
    'C:\\Program Files\\MySQL',
    'C:\\Program Files (x86)\\MySQL',
    'C:\\xampp\\mysql',
    'C:\\wamp64\\bin\\mysql',
    'C:\\wamp\\bin\\mysql'
];

foreach ($possible_paths as $path) {
    if (is_dir($path)) {
        echo "Found MySQL at: $path\n";
        
        // Look for bin directory
        $bin_path = $path . '\\bin';
        if (is_dir($bin_path)) {
            echo "MySQL bin directory found at: $bin_path\n";
            echo "Try running these commands:\n";
            echo "cd \"$bin_path\"\n";
            echo "mysql -u root -p\n";
        }
        
        // Look for MySQL Server directory
        $server_paths = glob($path . '\\MySQL Server*');
        foreach ($server_paths as $server_path) {
            echo "Found MySQL Server at: $server_path\n";
            $server_bin = $server_path . '\\bin';
            if (is_dir($server_bin)) {
                echo "MySQL Server bin directory found at: $server_bin\n";
                echo "Try running these commands:\n";
                echo "cd \"$server_bin\"\n";
                echo "mysql -u root -p\n";
            }
        }
    }
}

echo "\nIf MySQL is not found in any of these locations, please:\n";
echo "1. Open Control Panel\n";
echo "2. Go to Programs and Features\n";
echo "3. Look for 'MySQL' in the list\n";
echo "4. Note the installation path\n\n";

echo "After finding MySQL, run these commands:\n";
echo "cd \"[MySQL bin directory path]\"\n";
echo "mysql -u root -p\n";
echo "(Press Enter when asked for password)\n";
?> 
