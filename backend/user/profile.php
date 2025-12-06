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
    $foto_profil = $user['foto_profil'];
    
    // Create upload directory if not exists
    $upload_dir = '../../assets/uploads/profile/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Handle foto upload
    if (!empty($_FILES['foto_profil']['name'])) {
        $file = $_FILES['foto_profil'];
        $file_name = $file['name'];
        $file_size = $file['size'];
        $file_tmp = $file['tmp_name'];
        $file_error = $file['error'];
        
        // Get file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
        
        if ($file_error === 0) {
            if (in_array($file_ext, $allowed_ext)) {
                if ($file_size <= 5000000) { // 5MB max
                    // Delete old foto if exists
                    if (!empty($user['foto_profil']) && file_exists($upload_dir . basename($user['foto_profil']))) {
                        unlink($upload_dir . basename($user['foto_profil']));
                    }
                    
                    $file_new_name = uniqid('profile_', true) . '.' . $file_ext;
                    $file_destination = $upload_dir . $file_new_name;
                    
                    if (move_uploaded_file($file_tmp, $file_destination)) {
                        $foto_profil = 'assets/uploads/profile/' . $file_new_name;
                    } else {
                        $error = 'Gagal upload foto';
                    }
                } else {
                    $error = 'Ukuran foto terlalu besar (max 5MB)';
                }
            } else {
                $error = 'Format foto tidak didukung (jpg, jpeg, png, gif)';
            }
        }
    }
    
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
        // Update basic info including foto
        $stmt = $conn->prepare("UPDATE user SET nama = ?, email = ?, no_telp = ?, no_identitas = ?, foto_profil = ? WHERE id_user = ?");
        $stmt->bind_param("sssssi", $nama, $email, $no_telp, $no_identitas, $foto_profil, $user_id);
        
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
    <title>Profil Saya - Lentera Nusantara</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: white;
            min-height: 100vh;
            position: relative;
        }

        .topnavbar {
            background: linear-gradient(180deg, #ff6b7d 0%, #ff8a94 100%);
            padding: 0;
            box-shadow: 0 4px 20px rgba(255, 107, 125, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
            
        }

        .topnavbar-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
        }

        .topnavbar-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
        }

        .topnavbar-brand h2 {
            color: #ffffff;
            font-size: 24px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .topnavbar-menu {
            display: flex;
            gap: 5px;
            list-style: none;
        }

        .topnavbar-menu a {
            color: white;
            text-decoration: none;
            padding: 20px 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            border-bottom: 3px solid transparent;
        }

        .topnavbar-menu a:hover {
            background: rgba(255, 255, 255, 0.15);
            border-bottom: 3px solid;
            border-image: linear-gradient(90deg, #ff6b7d, #fdff94) 1;
        }

        .topnavbar-menu a.active {
            background: rgba(255, 255, 255, 0.2);
            border-bottom: 3px solid;
            border-image: linear-gradient(90deg, #ff6b7d, #fdff94) 1;
        }

        .main-content {
            position: relative;
            z-index: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            color: #ffffff;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.2);
        }

        .profile-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            background: white;
        }

        .alert-error {
            color: #c62828;
            border: 2px solid #ef5350;
        }

        .alert-success {
            color: #2e7d32;
            border: 2px solid #4caf50;
        }

        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            margin-bottom: 25px;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .profile-header {
            text-align: center;
            padding-bottom: 25px;
            border-bottom: 2px solid #fdff94;
            margin-bottom: 30px;
        }

        .profile-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(180deg, #ff6b7d 0%, #fdff94 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            overflow: hidden;
            border: 4px solid #fdff94;
            box-shadow: 0 4px 15px rgba(255, 107, 125, 0.3);
        }

        .profile-avatar-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-header h2 {
            color: #ff6b7d;
            margin-bottom: 10px;
        }

        .section-divider {
            border-top: 2px solid #fdff94;
            margin: 30px 0;
            padding-top: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #ff6b7d;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff6b7d;
            box-shadow: 0 0 0 3px rgba(255, 107, 125, 0.1);
        }

        .foto-upload-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        .foto-preview {
            width: 100%;
            height: 200px;
            border: 2px dashed #fdff94;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            background: linear-gradient(180deg, rgba(255, 107, 125, 0.05) 0%, rgba(253, 255, 148, 0.05) 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .foto-preview:hover {
            border-color: #ff6b7d;
            background: rgba(255, 107, 125, 0.1);
        }

        .foto-preview svg {
            color: #ff6b7d;
            margin-bottom: 8px;
        }

        .foto-preview p {
            color: #999;
            font-size: 14px;
            margin: 0;
        }

        .foto-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .foto-preview.has-image svg,
        .foto-preview.has-image p {
            display: none;
        }

        .foto-preview.has-image img {
            display: block;
        }

        .foto-input {
            display: none;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(180deg, #ff6b7d 0%, #ff8a94 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 125, 0.3);
            flex: 1;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 125, 0.4);
        }

        .btn-outline {
            background: white;
            color: #ff6b7d;
            border: 2px solid #ff6b7d;
        }

        .btn-outline:hover {
            background: #ff6b7d;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="topnavbar">
        <div class="topnavbar-content">
            <div class="topnavbar-brand">
                <h2>🏨 Lentera Nusantara</h2>
            </div>
            <ul class="topnavbar-menu">
                <li><a href="dashboard.php"><span>🏠</span> Beranda</a></li>
                <li><a href="reservations.php"><span>📅</span> Reservasi Saya</a></li>
                <li><a href="rooms.php"><span>🏨</span> Lihat Kamar</a></li>
                <li><a href="fnb_new_order.php"><span>🍽️</span> Pesan F&B</a></li>
                <li><a href="fnb_orders.php"><span>📋</span> Pesanan F&B</a></li>
                <li><a href="profile.php" class="active"><span>👤</span> Profil</a></li>
                <li><a href="../logout.php"><span>🚪</span> Keluar</a></li>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <h1>Profil Saya</h1>
        </div>

        <div class="profile-container">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar-large">
                        <?php if ($user['foto_profil'] && file_exists('../../' . $user['foto_profil'])): ?>
                            <img src="../../<?php echo htmlspecialchars($user['foto_profil']); ?>" alt="Profile">
                        <?php else: ?>
                            <?php echo strtoupper(substr($user['nama'], 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <h2><?php echo htmlspecialchars($user['nama']); ?></h2>
                    <p style="color: #666;"><?php echo htmlspecialchars($user['email']); ?></p>
                    <p style="color: #999; font-size: 14px; margin-top: 10px;">
                        Member sejak <?php echo date('F Y', strtotime($user['created_at'])); ?>
                    </p>
                </div>

                <form method="POST" action="" enctype="multipart/form-data">
                    <h3 style="color: #ff6b7d;">Informasi Personal</h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nama">Nama Lengkap</label>
                            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="no_telp">Nomor Telepon</label>
                            <input type="text" id="no_telp" name="no_telp" value="<?php echo htmlspecialchars($user['no_telp']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="no_identitas">Nomor Identitas (KTP/SIM)</label>
                            <input type="text" id="no_identitas" name="no_identitas" value="<?php echo htmlspecialchars($user['no_identitas']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="foto_profil">Foto Profil</label>
                        <div class="foto-upload-wrapper">
                            <div class="foto-preview <?php echo (!empty($user['foto_profil']) && file_exists('../../' . $user['foto_profil'])) ? 'has-image' : ''; ?>" id="fotoPreview">
                                <?php if (!empty($user['foto_profil']) && file_exists('../../' . $user['foto_profil'])): ?>
                                    <img src="../../<?php echo $user['foto_profil']; ?>" alt="Profile">
                                <?php endif; ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                    <circle cx="12" cy="13" r="4"></circle>
                                </svg>
                                <p>Pilih Foto</p>
                            </div>
                            <input type="file" id="foto_profil" name="foto_profil" class="foto-input" accept="image/*" onchange="previewFoto(event)">
                        </div>
                        <small style="color: #999; font-size: 13px;">Format: JPG, PNG, GIF (Maks 5MB)</small>
                    </div>

                    <div class="section-divider">
                        <h3 style="color: #ff6b7d;">Ubah Password</h3>
                        <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
                            Kosongkan jika tidak ingin mengubah password
                        </p>
                    </div>

                    <div class="form-group">
                        <label for="current_password">Password Lama</label>
                        <input type="password" id="current_password" name="current_password" autocomplete="off">
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="new_password">Password Baru</label>
                            <input type="password" id="new_password" name="new_password" autocomplete="off">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password Baru</label>
                            <input type="password" id="confirm_password" name="confirm_password" autocomplete="off">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="dashboard.php" class="btn btn-outline">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <script>
    function previewFoto(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('fotoPreview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                let img = preview.querySelector('img');
                if (!img) {
                    img = document.createElement('img');
                    preview.appendChild(img);
                }
                img.src = e.target.result;
                preview.classList.add('has-image');
            }
            reader.readAsDataURL(file);
        }
    }
    
    // Make foto preview clickable
    const fotoPreview = document.getElementById('fotoPreview');
    const fotoInput = document.getElementById('foto_profil');
    fotoPreview.addEventListener('click', function() {
        fotoInput.click();
    });
    </script>
</body>
</html>
