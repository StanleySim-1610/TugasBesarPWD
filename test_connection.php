<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            background: linear-gradient(135deg, #f0b343 0%, #ff7a89 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .test-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #ff7a89;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        .test-item {
            padding: 15px;
            margin: 10px 0;
            background: white;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        .status.pass {
            background: #28a745;
            color: white;
        }
        .status.fail {
            background: #dc3545;
            color: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #ff7a89;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #f0b343 0%, #ff7a89 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,127,153,0.25);
        }
        .code {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            overflow-x: auto;
            margin: 10px 0;
        }
        .icon {
            font-size: 24px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔌 Database Connection Test</h1>
        <p class="subtitle">Testing your Hotel Management System database configuration</p>

        <?php
        // Test 1: PHP Version
        $php_version = phpversion();
        $php_ok = version_compare($php_version, '7.4.0', '>=');
        ?>

        <div class="test-section <?php echo $php_ok ? 'success' : 'error'; ?>">
            <h3><span class="icon">🐘</span>PHP Version Check</h3>
            <div class="test-item">
                <span>PHP Version: <strong><?php echo $php_version; ?></strong></span>
                <span class="status <?php echo $php_ok ? 'pass' : 'fail'; ?>">
                    <?php echo $php_ok ? '✓ PASS' : '✗ FAIL'; ?>
                </span>
            </div>
            <?php if (!$php_ok): ?>
                <p>⚠️ PHP 7.4 or higher is required. Current version: <?php echo $php_version; ?></p>
            <?php endif; ?>
        </div>

        <?php
        // Test 2: Database Connection
        require_once 'config/database.php';
        
        $db_config_exists = file_exists('config/database.php');
        $connection_success = !$conn->connect_error;
        ?>

        <div class="test-section <?php echo $connection_success ? 'success' : 'error'; ?>">
            <h3><span class="icon">🗄️</span>Database Connection</h3>
            
            <?php if ($connection_success): ?>
                <div class="test-item">
                    <span>Connection Status</span>
                    <span class="status pass">✓ CONNECTED</span>
                </div>
                <div class="test-item">
                    <span>Database Host</span>
                    <span><strong><?php echo DB_HOST; ?></strong></span>
                </div>
                <div class="test-item">
                    <span>Database Name</span>
                    <span><strong><?php echo DB_NAME; ?></strong></span>
                </div>
                <div class="test-item">
                    <span>Database User</span>
                    <span><strong><?php echo DB_USER; ?></strong></span>
                </div>
            <?php else: ?>
                <div class="test-item">
                    <span>Connection Status</span>
                    <span class="status fail">✗ FAILED</span>
                </div>
                <p><strong>Error:</strong> <?php echo $conn->connect_error; ?></p>
                <p>Please check your database configuration in <code>config/database.php</code></p>
            <?php endif; ?>
        </div>

        <?php if ($connection_success):
            // Test 3: Tables Check
            $required_tables = ['user', 'kamar', 'reservation', 'fnb_order', 'payment_fnb', 'payment_reservation'];
            $tables_result = $conn->query("SHOW TABLES");
            $existing_tables = [];
            while ($row = $tables_result->fetch_array()) {
                $existing_tables[] = $row[0];
            }
            
            $all_tables_exist = count(array_intersect($required_tables, $existing_tables)) === count($required_tables);
        ?>

        <div class="test-section <?php echo $all_tables_exist ? 'success' : 'warning'; ?>">
            <h3><span class="icon">📊</span>Database Tables</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>Table Name</th>
                        <th>Status</th>
                        <th>Row Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($required_tables as $table): 
                        $exists = in_array($table, $existing_tables);
                        $count = 0;
                        if ($exists) {
                            $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
                            $count = $count_result->fetch_assoc()['count'];
                        }
                    ?>
                    <tr>
                        <td><strong><?php echo $table; ?></strong></td>
                        <td>
                            <span class="status <?php echo $exists ? 'pass' : 'fail'; ?>">
                                <?php echo $exists ? '✓ EXISTS' : '✗ MISSING'; ?>
                            </span>
                        </td>
                        <td><?php echo $exists ? $count . ' rows' : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (!$all_tables_exist): ?>
                <div class="warning" style="margin-top: 20px; padding: 15px;">
                    <p><strong>⚠️ Warning:</strong> Some tables are missing!</p>
                    <p>Please import the database file: <code>database/hotel_management.sql</code></p>
                </div>
            <?php endif; ?>
        </div>

        <?php
            // Test 4: Admin User Check
            $admin_exists = false;
            $admin_query = $conn->query("SELECT * FROM user WHERE email = 'admin@gmail.com'");
            $admin_exists = $admin_query && $admin_query->num_rows > 0;
        ?>

        <div class="test-section <?php echo $admin_exists ? 'success' : 'warning'; ?>">
            <h3><span class="icon">👤</span>Admin Account</h3>
            
            <div class="test-item">
                <span>Admin User Exists</span>
                <span class="status <?php echo $admin_exists ? 'pass' : 'fail'; ?>">
                    <?php echo $admin_exists ? '✓ YES' : '✗ NO'; ?>
                </span>
            </div>

            <?php if ($admin_exists): ?>
                <div class="success" style="margin-top: 15px; padding: 15px;">
                    <p><strong>✓ Admin account is ready!</strong></p>
                    <p>Email: <strong>admin@gmail.com</strong></p>
                    <p>Password: <strong>admin123</strong></p>
                </div>
            <?php else: ?>
                <div class="warning" style="margin-top: 15px; padding: 15px;">
                    <p><strong>⚠️ Admin account not found!</strong></p>
                    <p>Run this SQL query in phpMyAdmin:</p>
                    <div class="code">
INSERT INTO user (nama, email, password, no_telp, no_identitas) VALUES<br>
('Administrator', 'admin@gmail.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFpHiKl5ra.PFrRYvSbT8vDnIQz2BPGq', '081234567890', 'ADM001');
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php
            // Test 5: Sample Data Check
            $rooms_count = $conn->query("SELECT COUNT(*) as count FROM kamar")->fetch_assoc()['count'];
        ?>

        <div class="test-section <?php echo $rooms_count > 0 ? 'success' : 'info'; ?>">
            <h3><span class="icon">🏨</span>Sample Data</h3>
            
            <div class="test-item">
                <span>Room Types Available</span>
                <span><strong><?php echo $rooms_count; ?> rooms</strong></span>
            </div>

            <?php if ($rooms_count > 0): ?>
                <p style="color: #155724; margin-top: 10px;">✓ Sample rooms are available. You can start booking!</p>
            <?php else: ?>
                <p style="color: #856404; margin-top: 10px;">⚠️ No rooms found. Database might need to be re-imported.</p>
            <?php endif; ?>
        </div>

        <?php endif; // End if connection_success ?>

        <!-- Summary -->
        <div class="test-section <?php echo ($connection_success && $all_tables_exist && $admin_exists) ? 'success' : 'error'; ?>">
            <h3><span class="icon">📋</span>Summary</h3>
            
            <?php if ($connection_success && $all_tables_exist && $admin_exists): ?>
                <h2 style="color: #28a745; text-align: center; margin: 20px 0;">
                    🎉 All Tests Passed!
                </h2>
                <p style="text-align: center; font-size: 18px;">
                    Your Hotel Management System is ready to use!
                </p>
                
                <div style="text-align: center; margin-top: 30px;">
                    <a href="index.html" class="btn">🏠 Go to Homepage</a>
                    <a href="login.php" class="btn">🔐 Login</a>
                    <a href="admin/dashboard.php" class="btn">👨‍💼 Admin Panel</a>
                </div>
            <?php else: ?>
                <h2 style="color: #dc3545; text-align: center; margin: 20px 0;">
                    ⚠️ Setup Incomplete
                </h2>
                <p style="text-align: center;">
                    Please fix the issues above before proceeding.
                </p>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="test_connection.php" class="btn">🔄 Retest</a>
                    <a href="README.md" class="btn">📖 Read Documentation</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- System Information -->
        <div class="test-section info">
            <h3><span class="icon">ℹ️</span>System Information</h3>
            <div class="test-item">
                <span>PHP Version</span>
                <span><?php echo PHP_VERSION; ?></span>
            </div>
            <div class="test-item">
                <span>Server Software</span>
                <span><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span>
            </div>
            <div class="test-item">
                <span>Document Root</span>
                <span><?php echo $_SERVER['DOCUMENT_ROOT']; ?></span>
            </div>
            <div class="test-item">
                <span>Current Time</span>
                <span><?php echo date('Y-m-d H:i:s'); ?></span>
            </div>
        </div>

    </div>
</body>
</html>
