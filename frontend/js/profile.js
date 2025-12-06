// Profile Page Script
document.addEventListener('DOMContentLoaded', async () => {
    try {
        await loadProfileData();
        setupFormHandlers();
    } catch (error) {
        console.error('Error loading profile:', error);
        showAlert('Error loading profile', 'error');
    }
});

async function loadProfileData() {
    try {
        const response = await apiRequest('/user/get-profile.php');
        if (response && response.success) {
            const user = response.data;
            
            // Update profile header
            document.getElementById('profileName').textContent = user.nama;
            document.getElementById('profileEmail').textContent = user.email;
            document.getElementById('profileAvatar').textContent = user.nama.charAt(0).toUpperCase();
            
            // Populate form fields
            document.getElementById('nama').value = user.nama;
            document.getElementById('email').value = user.email;
            document.getElementById('no_telp').value = user.no_telp || '';
            document.getElementById('no_identitas').value = user.no_identitas || '';
        }
    } catch (error) {
        console.error('Error loading profile data:', error);
    }
}

function setupFormHandlers() {
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', handleProfileSubmit);
    }
}

async function handleProfileSubmit(e) {
    e.preventDefault();
    
    try {
        const nama = document.getElementById('nama').value;
        const email = document.getElementById('email').value;
        const noTelp = document.getElementById('no_telp').value;
        const noIdentitas = document.getElementById('no_identitas').value;
        const currentPassword = document.getElementById('current_password').value;
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        // Validation
        if (!nama || !email) {
            showAlert('Nama dan email wajib diisi!', 'error');
            return;
        }
        
        // Validate password if provided
        if (newPassword || confirmPassword) {
            if (!currentPassword) {
                showAlert('Masukkan password lama!', 'error');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showAlert('Password baru dan konfirmasi tidak cocok!', 'error');
                return;
            }
            
            if (newPassword.length < 6) {
                showAlert('Password minimal 6 karakter!', 'error');
                return;
            }
        }
        
        // Submit update
        const response = await apiRequest('/user/update-profile.php', {
            method: 'POST',
            body: {
                nama,
                email,
                no_telp: noTelp,
                no_identitas: noIdentitas,
                current_password: currentPassword,
                new_password: newPassword,
                confirm_password: confirmPassword
            }
        });
        
        if (response && response.success) {
            showAlert('Profil berhasil diperbarui!', 'success');
            
            // Clear password fields
            document.getElementById('current_password').value = '';
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
            
            // Reload profile data
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(response?.message || 'Terjadi kesalahan saat memperbarui profil', 'error');
        }
    } catch (error) {
        console.error('Profile update error:', error);
        showAlert('Terjadi kesalahan saat memperbarui profil', 'error');
    }
}
