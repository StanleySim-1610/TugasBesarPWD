<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user data
$stmt = $conn->prepare("SELECT * FROM user WHERE id_user = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    $no_telp = sanitize($_POST['no_telp']);
    $no_identitas = sanitize($_POST['no_identitas']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Check if email is changed and already exists
    if ($email !== $user['email']) {
        $check = $conn->prepare("SELECT id_user FROM user WHERE email = ? AND id_user != ?");
        $check->bind_param("si", $email, $user_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'Email sudah digunakan!';
        }
    }
    
    if (empty($error)) {
        // Update basic info
        $stmt = $conn->prepare("UPDATE user SET nama = ?, email = ?, no_telp = ?, no_identitas = ? WHERE id_user = ?");
        $stmt->bind_param("ssssi", $nama, $email, $no_telp, $no_identitas, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['nama'] = $nama;
            $_SESSION['email'] = $email;
            
            // Update password if provided
            if (!empty($new_password)) {
                if (empty($current_password)) {
                    $error = 'Masukkan password lama!';
                } elseif (!verifyPassword($current_password, $user['password'])) {
                    $error = 'Password lama salah!';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'Password baru tidak cocok!';
                } elseif (strlen($new_password) < 6) {
                    $error = 'Password minimal 6 karakter!';
                } else {
                    $hashed = hashPassword($new_password);
                    $stmt = $conn->prepare("UPDATE user SET password = ? WHERE id_user = ?");
                    $stmt->bind_param("si", $hashed, $user_id);
                    $stmt->execute();
                }
            }
            
            if (empty($error)) {
                $success = 'Profil berhasil diperbarui!';
                // Refresh user data
                $stmt = $conn->prepare("SELECT * FROM user WHERE id_user = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
            }
        } else {
            $error = 'Gagal memperbarui profil!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - User</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        .profile-header {
            text-align: center;
            padding-bottom: 25px;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 30px;
        }
        .profile-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: url('../assets/logo.png?v=2') center/cover no-repeat, var(--accent);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .profile-avatar-large img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        .section-divider {
            border-top: 2px solid #e0e0e0;
            margin: 30px 0;
            padding-top: 30px;
        }
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/logo.png?v=2" alt="Logo" class="sidebar-logo">
                <h3>User Panel</h3>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <span class="nav-icon">🏠</span>
                    Dashboard
                </a>
                <a href="reservations.php" class="nav-item">
                    <span class="nav-icon">📅</span>
                    My Reservations
                </a>
                <a href="rooms.php" class="nav-item">
                    <span class="nav-icon">🏨</span>
                    Browse Rooms
                </a>
                <a href="profile.php" class="nav-item active">
                    <span class="nav-icon">👤</span>
                    Profile
                </a>
                <a href="../logout.php" class="nav-item">
                    <span class="nav-icon">🚪</span>
                    Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="profile-container">
                <div class="top-bar">
                    <h1>My Profile</h1>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="profile-card">
                    <div class="profile-header">
                        <?php if ($user['foto_profil']): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($user['foto_profil']); ?>" alt="Profile" class="profile-avatar-large">
                        <?php else: ?>
                            <div class="profile-avatar-large">
                                <?php echo strtoupper(substr($user['nama'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <h2><?php echo htmlspecialchars($user['nama']); ?></h2>
                        <p style="color: #666;"><?php echo htmlspecialchars($user['email']); ?></p>
                        <p style="color: #999; font-size: 14px; margin-top: 10px;">
                            Member since <?php echo date('F Y', strtotime($user['created_at'])); ?>
                        </p>
                    </div>

                    <form method="POST" action="">
                        <h3>Personal Information</h3>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nama">Full Name</label>
                                <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="no_telp">Phone Number</label>
                                <input type="text" id="no_telp" name="no_telp" value="<?php echo htmlspecialchars($user['no_telp']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="no_identitas">ID Number (KTP/SIM)</label>
                                <input type="text" id="no_identitas" name="no_identitas" value="<?php echo htmlspecialchars($user['no_identitas']); ?>">
                            </div>
                        </div>

                        <div class="section-divider">
                            <h3>Change Password</h3>
                            <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
                                Leave blank if you don't want to change your password
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" autocomplete="off">
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" autocomplete="off">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" autocomplete="off">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
                            <a href="dashboard.php" class="btn btn-outline" style="flex: 1;">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
