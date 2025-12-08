# PENJELASAN BONUS TUGAS BESAR

## BONUS 3: Deteksi Email Duplikat Saat Registrasi

### Lokasi Code:
1. **Backend Register**: `backend/register.php` (line ~90-108)
2. **Backend Profile**: `backend/user/profile.php` (line ~70-85)

### Cara Kerja:

#### A. Saat Registrasi (register.php)
```php
// Step 1: Prepare query untuk cek email
$stmt = $conn->prepare("SELECT id_user FROM user WHERE email = ?");

// Step 2: Bind parameter email yang akan dicek
$stmt->bind_param("s", $email);

// Step 3: Eksekusi query
$stmt->execute();

// Step 4: Ambil hasil
$result = $stmt->get_result();

// Step 5: Cek apakah email sudah ada
if ($result->num_rows > 0) {
    // Email sudah terdaftar - TOLAK registrasi
    $errors['email'] = 'Email sudah terdaftar';
} else {
    // Email belum ada - LANJUTKAN registrasi
    // Insert user baru ke database
}
```

**Alur Proses:**
1. User mengisi form registrasi dengan email
2. Backend cek ke database: `SELECT id_user FROM user WHERE email = 'user@gmail.com'`
3. Jika ada hasil (num_rows > 0) → Email sudah dipakai → Tampilkan error
4. Jika tidak ada hasil (num_rows = 0) → Email masih kosong → Lanjutkan insert

**Keamanan:**
- ✅ Menggunakan **Prepared Statement** (mencegah SQL Injection)
- ✅ Validasi di **Backend** (tidak hanya frontend)
- ✅ Error message yang jelas untuk user

---

#### B. Saat Update Profile (profile.php)
```php
// Hanya cek jika user mengubah email
if ($email !== $user['email']) {
    // Query: Cari email yang sama TAPI user berbeda
    $check = $conn->prepare("SELECT id_user FROM user WHERE email = ? AND id_user != ?");
    
    // Bind 2 parameter: email baru & id user yang login
    $check->bind_param("si", $email, $user_id);
    
    $check->execute();
    
    // Jika email sudah dipakai user lain
    if ($check->get_result()->num_rows > 0) {
        $error = 'Email sudah digunakan!';
    }
}
```

**Perbedaan dengan Registrasi:**
- Ada kondisi `AND id_user != ?` → Exclude user sendiri
- User boleh tetap pakai email lama
- Hanya tolak jika email dipakai **user lain**

---

## BONUS 4: Upload & Update Foto Profil

### Lokasi Code:
1. **Upload saat Register**: `backend/register.php` (line ~58-95)
2. **Update di Profile**: `backend/user/profile.php` (line ~34-70)

### Cara Kerja:

#### A. Upload Foto Saat Registrasi
```php
// Step 1: Cek apakah user upload file
if (!empty($_FILES['foto_profil']['name'])) {
    
    // Step 2: Ambil informasi file
    $file = $_FILES['foto_profil'];
    $file_name = $file['name'];        // Nama asli: foto.jpg
    $file_size = $file['size'];        // Ukuran: 2048000 bytes
    $file_tmp = $file['tmp_name'];     // Lokasi temporary
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Step 3: Validasi ekstensi file (whitelist)
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
    if (in_array($file_ext, $allowed_ext)) {
        
        // Step 4: Validasi ukuran (max 5MB)
        if ($file_size <= 5000000) {
            
            // Step 5: Generate nama file UNIK
            // Format: profile_[timestamp_unique_id].[ext]
            // Contoh: profile_6756abc123.jpg
            $file_new_name = uniqid('profile_', true) . '.' . $file_ext;
            
            // Step 6: Pindahkan file ke folder uploads
            $file_destination = 'assets/uploads/profile/' . $file_new_name;
            if (move_uploaded_file($file_tmp, $file_destination)) {
                // SUCCESS: Simpan path ke variabel
                $foto_profil = 'assets/uploads/profile/' . $file_new_name;
            }
        }
    }
}

// Step 7: Simpan ke database bersama data user lainnya
INSERT INTO user (nama, email, password, foto_profil) VALUES (?, ?, ?, ?)
```

**Fitur Keamanan:**
1. ✅ **Validasi Ekstensi**: Hanya jpg, jpeg, png, gif (cegah upload .php, .exe)
2. ✅ **Validasi Ukuran**: Maksimal 5MB (cegah DOS attack)
3. ✅ **Unique Filename**: Pakai `uniqid()` (cegah overwrite file)
4. ✅ **Folder Terpisah**: Upload ke folder khusus (`assets/uploads/profile/`)

---

#### B. Update Foto di Profile
```php
// Step 1: Cek apakah user upload foto baru
if (!empty($_FILES['foto_profil']['name'])) {
    
    // Step 2-4: Validasi sama seperti registrasi
    // (ekstensi, ukuran, dll)
    
    // Step 5: HAPUS FOTO LAMA sebelum upload baru
    if (!empty($user['foto_profil']) && file_exists($old_foto_path)) {
        unlink($old_foto_path);  // Hapus file lama dari server
    }
    
    // Step 6: Upload foto baru
    $file_new_name = uniqid('profile_', true) . '.' . $file_ext;
    move_uploaded_file($file_tmp, $file_destination);
    
    // Step 7: Update database
    UPDATE user SET foto_profil = ? WHERE id_user = ?
}
```

**Perbedaan dengan Upload Registrasi:**
- Ada proses **DELETE foto lama** (hemat storage)
- Ada proses **UPDATE database** (bukan INSERT)
- User bisa update foto berkali-kali

---

## DIAGRAM ALUR

### Bonus 3: Email Duplikat Check

```
User Register/Update
        ↓
Input Email: user@gmail.com
        ↓
Backend Query: SELECT id_user FROM user WHERE email = 'user@gmail.com'
        ↓
    Found?
   ↙     ↘
YES      NO
 ↓        ↓
Error   Success
Email   Continue
sudah   Process
ada
```

---

### Bonus 4: Upload Foto

```
User Upload Foto
        ↓
Validasi Ekstensi (jpg/png/gif)
        ↓
    Valid?
   ↙     ↘
NO       YES
 ↓        ↓
Error   Validasi Ukuran (<5MB)
         ↓
      Valid?
     ↙     ↘
   NO       YES
    ↓        ↓
  Error   Delete Foto Lama (jika update)
           ↓
        Generate Unique Filename
           ↓
        move_uploaded_file()
           ↓
        Save Path ke Database
           ↓
         Success
```

---

## CONTOH UNTUK PRESENTASI

### Demo 1: Email Duplikat
1. Register dengan email: `test@gmail.com` → SUCCESS
2. Register lagi dengan email: `test@gmail.com` → ERROR: "Email sudah terdaftar"
3. Update profile user A dengan email user B → ERROR: "Email sudah digunakan"

### Demo 2: Upload Foto
1. Register + upload foto → Foto tersimpan sebagai `profile_6756abc.jpg`
2. Update profile + upload foto baru → Foto lama terhapus, foto baru: `profile_6756xyz.jpg`
3. Coba upload file .php → ERROR: "Format tidak didukung"
4. Coba upload file 10MB → ERROR: "Ukuran terlalu besar"

---

## KODE PENTING UNTUK DITUNJUKKAN

### 1. Prepared Statement (Security)
```php
$stmt = $conn->prepare("SELECT id_user FROM user WHERE email = ?");
$stmt->bind_param("s", $email);
```

### 2. Email Check Logic
```php
if ($result->num_rows > 0) {
    $errors['email'] = 'Email sudah terdaftar';
}
```

### 3. File Validation
```php
$allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
if (in_array($file_ext, $allowed_ext) && $file_size <= 5000000) {
    // Process upload
}
```

### 4. Unique Filename
```php
$file_new_name = uniqid('profile_', true) . '.' . $file_ext;
```

### 5. Delete Old File
```php
if (!empty($user['foto_profil']) && file_exists($old_file)) {
    unlink($old_file);
}
```

---

## KESIMPULAN

✅ **Bonus 3** (Email Duplikat):
- Implementasi dengan Prepared Statement
- Validasi di backend (aman)
- User experience yang baik (error message jelas)

✅ **Bonus 4** (Upload Foto):
- Fitur upload saat registrasi
- Fitur update foto di profile
- Security: validasi ekstensi, ukuran, unique filename
- Optimization: hapus foto lama saat update

**Kedua bonus telah diimplementasikan dengan baik dan memenuhi kriteria tugas!**
