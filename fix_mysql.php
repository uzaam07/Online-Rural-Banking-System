<?php
echo "MySQL Connection Fixer\n\n";

// Create a file with the password reset command
$init_content = "ALTER USER 'root'@'localhost' IDENTIFIED BY 'newpassword123';\nFLUSH PRIVILEGES;";
file_put_contents('C:\\mysql-init.txt', $init_content);
echo "Created init file at C:\\mysql-init.txt\n\n";

echo "Follow these steps:\n\n";
echo "1. First, stop MySQL:\n";
echo "   - Press Windows + R\n";
echo "   - Type 'services.msc' and press Enter\n";
echo "   - Find 'MySQL90'\n";
echo "   - Right-click and select 'Stop'\n";
echo "   - Wait until it says 'Stopped'\n\n";

echo "2. Open Command Prompt as Administrator and run:\n\n";
echo "   cd \"C:\\Program Files\\MySQL\\MySQL Server 9.0\\bin\"\n";
echo "   mysqld --init-file=C:\\mysql-init.txt --console\n\n";

echo "3. Open a new Command Prompt window and run:\n\n";
echo "   cd \"C:\\Program Files\\MySQL\\MySQL Server 9.0\\bin\"\n";
echo "   mysql -u root -p\n";
echo "   (When prompted, enter: newpassword123)\n\n";

echo "4. If you get in, run these commands:\n\n";
echo "   ALTER USER 'root'@'localhost' IDENTIFIED BY '';\n";
echo "   FLUSH PRIVILEGES;\n";
echo "   exit;\n\n";

echo "5. Go back to the first Command Prompt and press Ctrl+C\n\n";

echo "6. Now, start MySQL again:\n";
echo "   - Go back to the Services window (services.msc)\n";
echo "   - Find 'MySQL90'\n";
echo "   - Right-click and select 'Start'\n";
echo "   - Wait until it says 'Running'\n\n";

echo "7. After MySQL is running, try connecting to MySQL Workbench:\n";
echo "   - Open MySQL Workbench\n";
echo "   - Double-click on your local instance\n";
echo "   - When prompted for password, just press Enter\n\n";

echo "If you get any errors, please tell me exactly what error message you see.\n";
?> 
