<?php
/**
 * Password Hash Generator
 * Use this tool to generate password hashes for the database
 */

// Function to generate password hash
function generatePasswordHash($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Generate hash for admin password
$admin_password = 'admin123';
$admin_hash = generatePasswordHash($admin_password);

echo "<h2>Password Hash Generator</h2>";
echo "<hr>";

// Admin credentials
echo "<h3>Admin Credentials:</h3>";
echo "<p><strong>Email:</strong> admin@gmail.com</p>";
echo "<p><strong>Password:</strong> admin123</p>";
echo "<p><strong>Hash:</strong> <code>" . $admin_hash . "</code></p>";
echo "<hr>";

// Test custom password
if (isset($_POST['password'])) {
    $custom_password = $_POST['password'];
    $custom_hash = generatePasswordHash($custom_password);
    
    echo "<h3>Custom Password Hash:</h3>";
    echo "<p><strong>Password:</strong> " . htmlspecialchars($custom_password) . "</p>";
    echo "<p><strong>Hash:</strong> <code>" . $custom_hash . "</code></p>";
    echo "<hr>";
}

// Verification test
echo "<h3>Verification Test:</h3>";
$test_password = 'admin123';
$test_hash = '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFpHiKl5ra.PFrRYvSbT8vDnIQz2BPGq';

if (password_verify($test_password, $test_hash)) {
    echo "<p style='color: green;'>✓ Password verification: <strong>SUCCESS</strong></p>";
    echo "<p>The password 'admin123' matches the hash in the database.</p>";
} else {
    echo "<p style='color: red;'>✗ Password verification: <strong>FAILED</strong></p>";
    echo "<p>The password does not match the hash.</p>";
}

echo "<hr>";

// Form to generate custom hash
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Hash Generator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h2, h3 {
            color: #333;
        }
        code {
            background: #eee;
            padding: 5px 10px;
            border-radius: 3px;
            display: inline-block;
            margin: 10px 0;
            word-break: break-all;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            margin: 10px 0;
        }
        button {
            background: #ff7a89;
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: #ff4f58;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #2196f3;
            margin: 20px 0;
        }
        .warning {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="info">
        <strong>ℹ️ Information:</strong><br>
        This tool generates bcrypt password hashes that can be used in the database.
        The default admin password is: <strong>admin123</strong>
    </div>

    <h3>Generate Custom Password Hash:</h3>
    <form method="POST" action="">
        <label for="password">Enter Password:</label>
        <input type="text" id="password" name="password" required placeholder="Enter password to hash">
        <button type="submit">Generate Hash</button>
    </form>

    <div class="warning">
        <strong>⚠️ Security Note:</strong><br>
        Never store plain text passwords in the database. Always use password_hash() function.
        This tool is for development/testing purposes only. Remove this file in production!
    </div>

    <hr>
    
    <h3>SQL Query to Update Admin Password:</h3>
    <p>If you need to reset the admin password in the database, use this SQL query:</p>
    <code style="display: block; padding: 15px;">
        UPDATE user SET password = '<?php echo $admin_hash; ?>' WHERE email = 'admin@gmail.com';
    </code>

    <hr>
    
    <h3>How to Use:</h3>
    <ol>
        <li>Open this file in your browser: <code>http://localhost/Testing%20tubes/test_password.php</code></li>
        <li>Enter any password you want to hash</li>
        <li>Click "Generate Hash"</li>
        <li>Copy the generated hash</li>
        <li>Use it in your INSERT or UPDATE SQL queries</li>
    </ol>

    <div class="info">
        <strong>🔒 Password Best Practices:</strong>
        <ul>
            <li>Use strong passwords (min 8 characters)</li>
            <li>Include uppercase, lowercase, numbers, and symbols</li>
            <li>Never reuse passwords</li>
            <li>Change default passwords after first login</li>
            <li>Use password managers for complex passwords</li>
        </ul>
    </div>
</body>
</html>
