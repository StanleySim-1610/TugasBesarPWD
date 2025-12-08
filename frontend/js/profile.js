document.addEventListener('DOMContentLoaded', function() {
    initializeProfile();
});

function initializeProfile() {
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            if (!validateProfileForm()) {
                e.preventDefault();
            }
        });
    }

    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            if (!validatePasswordForm()) {
                e.preventDefault();
            }
        });
    }

    const photoInput = document.getElementById('foto_profil');
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            previewPhoto(e);
        });
    }
}

function validateProfileForm() {
    const namaInput = document.getElementById('nama');
    const teleponInput = document.getElementById('no_telp');
    const identitasInput = document.getElementById('no_identitas');

    let isValid = true;

    document.querySelectorAll('.error-message').forEach(el => el.remove());

    if (!namaInput.value.trim()) {
        showFieldError(namaInput, 'Nama tidak boleh kosong');
        isValid = false;
    }

    if (!teleponInput.value.trim()) {
        showFieldError(teleponInput, 'Nomor telepon harus diisi');
        isValid = false;
    } else if (!validatePhone(teleponInput.value)) {
        showFieldError(teleponInput, 'Nomor telepon harus 10-13 digit angka');
        isValid = false;
    }

    if (!identitasInput.value.trim()) {
        showFieldError(identitasInput, 'Nomor identitas harus diisi');
        isValid = false;
    } else if (!validateIdentityNumber(identitasInput.value)) {
        showFieldError(identitasInput, 'Nomor identitas harus 16 digit angka');
        isValid = false;
    }

    return isValid;
}

function validatePasswordForm() {
    const oldPasswordInput = document.getElementById('old_password');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    let isValid = true;

    document.querySelectorAll('.error-message').forEach(el => el.remove());

    if (!oldPasswordInput.value) {
        showFieldError(oldPasswordInput, 'Password lama harus diisi');
        isValid = false;
    }

    if (!newPasswordInput.value) {
        showFieldError(newPasswordInput, 'Password baru harus diisi');
        isValid = false;
    } else if (newPasswordInput.value.length < 6) {
        showFieldError(newPasswordInput, 'Password baru minimal 6 karakter');
        isValid = false;
    }

    if (!confirmPasswordInput.value) {
        showFieldError(confirmPasswordInput, 'Konfirmasi password harus diisi');
        isValid = false;
    } else if (confirmPasswordInput.value !== newPasswordInput.value) {
        showFieldError(confirmPasswordInput, 'Password tidak cocok');
        isValid = false;
    }

    return isValid;
}

function previewPhoto(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('fotoPreview');
            if (preview) {
                preview.src = e.target.result;
            }
        };
        reader.readAsDataURL(file);
    }
}

function showFieldError(field, message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}
