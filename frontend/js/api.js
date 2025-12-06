// API Base URL - sesuaikan dengan server Anda
const API_BASE_URL = 'http://localhost/TugasBesarPWD/backend';

// Helper function untuk membuat request
async function apiRequest(endpoint, options = {}) {
    const {
        method = 'GET',
        body = null,
        headers = {}
    } = options;

    const config = {
        method,
        headers: {
            'Content-Type': 'application/json',
            ...headers
        },
        credentials: 'include' // Untuk mengirim cookies session
    };

    if (body) {
        config.body = JSON.stringify(body);
    }

    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
        
        if (!response.ok) {
            if (response.status === 401) {
                // Unauthorized - redirect to login
                window.location.href = '../login.html';
                return null;
            }
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// Format Rupiah
function formatRupiah(amount) {
    return 'Rp ' + amount.toLocaleString('id-ID');
}

// Format tanggal
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Hitung selisih hari
function calculateDays(checkIn, checkOut) {
    const date1 = new Date(checkIn);
    const date2 = new Date(checkOut);
    const diffTime = Math.abs(date2 - date1);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
}

// Get Query Parameter
function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

// Logout function
function logout() {
    fetch(`${API_BASE_URL}/logout.php`, {
        method: 'POST',
        credentials: 'include'
    }).then(() => {
        window.location.href = '../login.html';
    }).catch(err => {
        console.error('Logout error:', err);
        window.location.href = '../login.html';
    });
}

// Show alert message
function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) return;
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    alertContainer.innerHTML = '';
    alertContainer.appendChild(alertDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
