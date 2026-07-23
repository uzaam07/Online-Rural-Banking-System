<?php
echo "MySQL Installation Checker\n\n";

// Check common MySQL installation paths
$possible_paths = [
    'C:\\Program Files\\MySQL\\MySQL Server 9.0',
    'C:\\Program Files\\MySQL\\MySQL Server 8.0',
    'C:\\Program Files (x86)\\MySQL\\MySQL Server 9.0',
    'C:\\Program Files (x86)\\MySQL\\MySQL Server 8.0'
];

echo "Checking MySQL installation paths...\n";
foreach ($possible_paths as $path) {
    if (is_dir($path)) {
        echo "Found MySQL at: $path\n";
        if (is_dir($path . '\\bin')) {
            echo "Bin directory exists at: " . $path . "\\bin\n";
        }
    }
}

// Check MySQL service
echo "\nChecking MySQL services...\n";
$output = [];
exec('sc query type= service | findstr /i "mysql"', $output);
if (!empty($output)) {
    echo "Found these MySQL-related services:\n";
    foreach ($output as $line) {
        echo $line . "\n";
    }
} else {
    echo "No MySQL services found.\n";
}

echo "\nPlease check these things:\n";
echo "1. Open Control Panel > Programs and Features\n";
echo "2. Look for 'MySQL' in the list\n";
echo "3. Note the exact version number\n";
echo "4. Also check if MySQL Workbench is listed\n\n";

echo "After checking, please tell me:\n";
echo "1. What version of MySQL is listed in Programs and Features?\n";
echo "2. What version of MySQL Workbench is listed?\n";
echo "3. What is the exact path where MySQL is installed?\n";
?> 
