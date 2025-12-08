<?php
require_once 'config/database.php';
require_once 'config/functions.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $no_telp = sanitize($_POST['no_telp']);
    $no_identitas = sanitize($_POST['no_identitas']);
    $foto_profil = '';
    
    // Create upload directory if not exists
    $upload_dir = '../assets/uploads/profile/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    if (empty($nama)) {
        $errors['nama'] = 'Nama lengkap wajib diisi';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email wajib diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@.*\.com$/', $email)) {
        $errors['email'] = 'Email wajib diisi';
    }
    
    if (empty($no_telp)) {
        $errors['no_telp'] = 'Nomor telepon wajib diisi';
    } elseif (!preg_match('/^[0-9]{10,13}$/', $no_telp)) {
        $errors['no_telp'] = 'Nomor telepon harus 10-13 digit angka';
    }
    
    if (empty($no_identitas)) {
        $errors['no_identitas'] = 'Nomor identitas wajib diisi';
    } elseif (!preg_match('/^[0-9]{16}$/', $no_identitas)) {
        $errors['no_identitas'] = 'Nomor identitas harus 16 digit angka';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password wajib diisi';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter';
    }
    
    if (empty($confirm_password)) {
        $errors['confirm_password'] = 'Konfirmasi password wajib diisi';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Password tidak sama';
    }
    
    // ============================================================================
    // BONUS 4: FITUR UPLOAD FOTO PROFIL SAAT REGISTRASI
    // ============================================================================
    // Validasi dan upload foto profil pengguna
    // Menggunakan $_FILES untuk menangkap file yang diupload dari form
    if (!empty($_FILES['foto_profil']['name'])) {
        // Ambil informasi file yang diupload
        $file = $_FILES['foto_profil'];
        $file_name = $file['name'];           // Nama file asli
        $file_size = $file['size'];           // Ukuran file dalam bytes
        $file_tmp = $file['tmp_name'];        // Lokasi temporary file di server
        $file_error = $file['error'];         // Error code (0 = no error)
        
        // Extract ekstensi file (jpg, png, dll) dan ubah ke lowercase
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Daftar ekstensi file yang diperbolehkan (whitelist)
        // Hanya file gambar yang aman yang diizinkan
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
        
        // Cek jika tidak ada error saat upload
        if ($file_error === 0) {
            // Validasi 1: Cek ekstensi file
            // Pastikan ekstensi file ada dalam daftar yang diizinkan
            if (in_array($file_ext, $allowed_ext)) {
                // Validasi 2: Cek ukuran file
                // Maksimal 5MB (5000000 bytes) untuk mencegah upload file terlalu besar
                if ($file_size <= 5000000) {
                    // Generate nama file unik menggunakan uniqid()
                    // Format: profile_[unique_id].[extension]
                    // Contoh: profile_6756abc123def.jpg
                    // Tujuan: Mencegah overwrite file dengan nama sama
                    $file_new_name = uniqid('profile_', true) . '.' . $file_ext;
                    
                    // Path lengkap tujuan file
                    $file_destination = $upload_dir . $file_new_name;
                    
                    // Pindahkan file dari temporary location ke folder tujuan
                    // move_uploaded_file() adalah fungsi PHP untuk memindahkan file upload
                    if (move_uploaded_file($file_tmp, $file_destination)) {
                        // Simpan path relative untuk disimpan ke database
                        // Path ini akan digunakan untuk menampilkan foto di aplikasi
                        $foto_profil = 'assets/uploads/profile/' . $file_new_name;
                    } else {
                        // Jika gagal memindahkan file (permission error, disk full, dll)
                        $errors['foto_profil'] = 'Gagal upload foto';
                    }
                } else {
                    // File terlalu besar
                    $errors['foto_profil'] = 'Ukuran foto terlalu besar (max 5MB)';
                }
            } else {
                // Ekstensi file tidak diizinkan (misal: .exe, .php, .sh)
                $errors['foto_profil'] = 'Format foto tidak didukung (jpg, jpeg, png, gif)';
            }
        }
    }
    // ============================================================================
    
    if (empty($errors)) {
        // ============================================================================
        // BONUS 3: DETEKSI EMAIL DUPLIKAT SAAT REGISTRASI
        // ============================================================================
        // Cek apakah email sudah terdaftar di database
        // Menggunakan prepared statement untuk keamanan (mencegah SQL injection)
        
        // Prepare query SQL dengan placeholder (?)
        $stmt = $conn->prepare("SELECT id_user FROM user WHERE email = ?");
        
        // Bind parameter - ganti placeholder dengan nilai actual
        // "s" = string (tipe data parameter)
        // $email = nilai yang akan dicek
        $stmt->bind_param("s", $email);
        
        // Eksekusi query ke database
        $stmt->execute();
        
        // Ambil hasil query
        $result = $stmt->get_result();
        
        // Cek jumlah baris hasil query
        // Jika num_rows > 0, berarti email sudah ada di database
        if ($result->num_rows > 0) {
            // Email sudah terdaftar - tampilkan error ke user
            // Error ini akan ditampilkan di form registrasi
            $errors['email'] = 'Email sudah terdaftar';
        } else {
            // Email belum terdaftar - lanjutkan proses insert user baru
            // ============================================================================
            $hashed_password = hashPassword($password);
            $stmt = $conn->prepare("INSERT INTO user (nama, email, password, no_telp, no_identitas, foto_profil) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $nama, $email, $hashed_password, $no_telp, $no_identitas, $foto_profil);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Registrasi berhasil! Silakan login dengan akun Anda.';
                header('Location: login.php');
                exit();
            } else {
                $errors['general'] = 'Terjadi kesalahan. Silakan coba lagi.';
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Hotel Management</title>
    <link rel="stylesheet" href="../frontend/assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <img src="../frontend/assets/logo.png?v=2" alt="Logo" class="auth-logo">
                <h2>Daftar Akun Baru</h2>
                <p>Buat akun untuk mulai booking kamar hotel</p>
            </div>
            
            <form method="POST" action="" class="auth-form" enctype="multipart/form-data" onsubmit="return validateForm()" novalidate>
                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-error" style="margin-bottom: 20px; padding: 10px; background: #ffe6e6; border: 1px solid #ff4444; border-radius: 5px; color: #cc0000;">
                        ⚠ <?php echo $errors['general']; ?>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" class="form-control <?php echo isset($errors['nama']) ? 'error' : ''; ?>" placeholder="Nama Lengkap" value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                    <div class="error-message <?php echo isset($errors['nama']) ? 'show' : ''; ?>" id="error-nama"><?php echo $errors['nama'] ?? ''; ?></div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control <?php echo isset($errors['email']) ? 'error' : ''; ?>" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <div class="error-message <?php echo isset($errors['email']) ? 'show' : ''; ?>" id="error-email"><?php echo $errors['email'] ?? ''; ?></div>
                </div>
                
                <div class="form-group">
                    <label for="no_telp">No. Telepon</label>
                    <input type="text" id="no_telp" name="no_telp" class="form-control <?php echo isset($errors['no_telp']) ? 'error' : ''; ?>" placeholder="No. Telepon" maxlength="13" value="<?php echo isset($_POST['no_telp']) ? htmlspecialchars($_POST['no_telp']) : ''; ?>">
                    <div class="error-message <?php echo isset($errors['no_telp']) ? 'show' : ''; ?>" id="error-no_telp"><?php echo $errors['no_telp'] ?? ''; ?></div>
                    <small class="form-hint">Contoh: 081234567890 (10-13 digit)</small>
                </div>
                
                <div class="form-group">
                    <label for="no_identitas">No. Identitas (KTP/SIM)</label>
                    <input type="text" id="no_identitas" name="no_identitas" class="form-control <?php echo isset($errors['no_identitas']) ? 'error' : ''; ?>" placeholder="No. Identitas" maxlength="16" value="<?php echo isset($_POST['no_identitas']) ? htmlspecialchars($_POST['no_identitas']) : ''; ?>">
                    <div class="error-message <?php echo isset($errors['no_identitas']) ? 'show' : ''; ?>" id="error-no_identitas"><?php echo $errors['no_identitas'] ?? ''; ?></div>
                    <small class="form-hint">Contoh: 1234567890123456 (16 digit)</small>
                </div>
                
                <div class="form-group">
                    <label for="foto_profil">Foto Profil</label>
                    <div class="foto-upload-wrapper">
                        <div class="foto-preview" id="fotoPreview">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                <circle cx="12" cy="13" r="4"></circle>
                            </svg>
                            <p>Pilih Foto</p>
                        </div>
                        <input type="file" id="foto_profil" name="foto_profil" class="foto-input" accept="image/*" onchange="previewFoto(event)">
                    </div>
                    <div class="error-message <?php echo isset($errors['foto_profil']) ? 'show' : ''; ?>" id="error-foto_profil"><?php echo $errors['foto_profil'] ?? ''; ?></div>
                    <small class="form-hint">Format: JPG, PNG, GIF (Max 5MB)</small>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="form-control <?php echo isset($errors['password']) ? 'error' : ''; ?>" placeholder="Password">
                        <span class="toggle-password" onclick="togglePassword('password')">
                            <svg id="eye-password" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                    </div>
                    <div class="error-message <?php echo isset($errors['password']) ? 'show' : ''; ?>" id="error-password"><?php echo $errors['password'] ?? ''; ?></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control <?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>" placeholder="Konfirmasi Password">
                        <span class="toggle-password" onclick="togglePassword('confirm_password')">
                            <svg id="eye-confirm_password" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                    </div>
                    <div class="error-message <?php echo isset($errors['confirm_password']) ? 'show' : ''; ?>" id="error-confirm_password"><?php echo $errors['confirm_password'] ?? ''; ?></div>
                </div>
                
                <button type="submit" class="btn btn-primary">Daftar</button>
            </form>
            
            <div class="auth-footer">
                <p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
            </div>
        </div>
    </div>
    
    <style>
    .error-message {
        color: #dc3545;
        font-size: 12px;
        margin-top: 5px;
        display: none;
        align-items: center;
        gap: 5px;
    }
    .error-message.show {
        display: flex;
    }
    .error-message::before {
        content: "⚠";
        font-size: 14px;
    }
    .form-control.error {
        border-color: #dc3545 !important;
        background-color: #fff5f5;
    }
    .form-hint {
        color: #6c757d;
        font-size: 11px;
        margin-top: 3px;
        display: block;
    }
    .password-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    .password-wrapper input {
        padding-right: 40px;
    }
    .toggle-password {
        position: absolute;
        right: 10px;
        cursor: pointer;
        color: #6c757d;
        display: flex;
        align-items: center;
        transition: color 0.3s;
    }
    .toggle-password:hover {
        color: #495057;
    }
    </style>
    
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
    
    function togglePassword(fieldId) {
        const passwordField = document.getElementById(fieldId);
        const eyeIcon = document.getElementById('eye-' + fieldId);
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
        } else {
            passwordField.type = 'password';
            eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        }
    }
    
    function showError(fieldId, message) {
        const errorDiv = document.getElementById('error-' + fieldId);
        const inputField = document.getElementById(fieldId);
        
        errorDiv.textContent = message;
        errorDiv.classList.add('show');
        inputField.classList.add('error');
    }
    
    function clearError(fieldId) {
        const errorDiv = document.getElementById('error-' + fieldId);
        const inputField = document.getElementById(fieldId);
        
        errorDiv.classList.remove('show');
        inputField.classList.remove('error');
    }
    
    function clearAllErrors() {
        ['nama', 'email', 'no_telp', 'no_identitas', 'password', 'confirm_password'].forEach(clearError);
    }
    
    function validateForm() {
        clearAllErrors();
        let isValid = true;
        
        const nama = document.getElementById('nama').value.trim();
        const email = document.getElementById('email').value.trim();
        const noTelp = document.getElementById('no_telp').value.trim();
        const noIdentitas = document.getElementById('no_identitas').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (nama === '') {
            showError('nama', 'Nama lengkap wajib diisi');
            isValid = false;
        }
        
        if (email === '') {
            showError('email', 'Email wajib diisi');
            isValid = false;
        } else {
            const emailPattern = /@.*\.com$/;
            if (!emailPattern.test(email)) {
                showError('email', 'Email wajib diisi');
                isValid = false;
            }
        }
        
        if (noTelp === '') {
            showError('no_telp', 'Nomor telepon wajib diisi');
            isValid = false;
        } else {
            const phonePattern = /^[0-9]{10,13}$/;
            if (!phonePattern.test(noTelp)) {
                showError('no_telp', 'Nomor telepon harus 10-13 digit angka');
                isValid = false;
            }
        }
        
        if (noIdentitas === '') {
            showError('no_identitas', 'Nomor identitas wajib diisi');
            isValid = false;
        } else {
            const identityPattern = /^[0-9]{16}$/;
            if (!identityPattern.test(noIdentitas)) {
                showError('no_identitas', 'Nomor identitas harus 16 digit angka');
                isValid = false;
            }
        }
        
        if (password === '') {
            showError('password', 'Password wajib diisi');
            isValid = false;
        } else if (password.length < 6) {
            showError('password', 'Password minimal 6 karakter');
            isValid = false;
        }
        
        // Validasi password dan konfirmasi password harus sama
        if (confirmPassword === '') {
            showError('confirm_password', 'Konfirmasi password wajib diisi');
            isValid = false;
        } else if (password !== confirmPassword) {
            showError('confirm_password', 'Password tidak sama');
            isValid = false;
        }
        
        return isValid;
    }
    
    document.getElementById('no_telp').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('input', function() {
            clearError(this.id);
        });
    });
    
    const fotoPreview = document.getElementById('fotoPreview');
    const fotoInput = document.getElementById('foto_profil');
    fotoPreview.addEventListener('click', function() {
        fotoInput.click();
    });
    </script>
</body>
</html>
