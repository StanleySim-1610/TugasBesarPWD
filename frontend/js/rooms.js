// Rooms Page Script
document.addEventListener('DOMContentLoaded', async () => {
    try {
        await loadAllRooms();
    } catch (error) {
        console.error('Error loading rooms:', error);
        showAlert('Error loading rooms', 'error');
    }
});

async function loadAllRooms() {
    try {
        const response = await apiRequest('/user/get-available-rooms.php');
        if (response && response.success) {
            const container = document.getElementById('roomsContainer');
            container.innerHTML = '';
            
            response.data.forEach(room => {
                const card = createShowcaseCard(room);
                container.appendChild(card);
            });
        }
    } catch (error) {
        console.error('Error loading rooms:', error);
    }
}

function createShowcaseCard(room) {
    const card = document.createElement('div');
    card.className = 'showcase-card';
    
    // Determine image based on room type
    let gambar = 'standard_room.jpg';
    const tipe = room.tipe_kamar.toLowerCase();
    
    if (tipe.includes('presidential')) {
        gambar = 'presidential_suite.png';
    } else if (tipe.includes('suite')) {
        gambar = 'suite_room.jpg';
    } else if (tipe.includes('deluxe')) {
        gambar = 'deluxe_room.jpg';
    }
    
    card.innerHTML = `
        <div class="showcase-image">
            <img src="../assets/room_photo/${gambar}" alt="${room.tipe_kamar}">
        </div>
        <div class="showcase-content">
            <div class="showcase-header">
                <h3 class="showcase-title">${room.tipe_kamar}</h3>
                <span class="showcase-badge">${room.jumlah_tersedia} Available</span>
            </div>
            
            <p class="showcase-description">
                ${room.deskripsi}
            </p>
            
            <div class="showcase-features">
                <span class="feature-tag">🛏️ King Bed</span>
                <span class="feature-tag">📶 Free WiFi</span>
                <span class="feature-tag">❄️ AC</span>
                <span class="feature-tag">📺 TV</span>
            </div>
            
            <div class="showcase-price">
                ${formatRupiah(room.harga)} <span>/night</span>
            </div>
            
            <a href="booking.html?room=${room.id_kamar}" class="btn btn-primary" style="width: 100%;">
                Book This Room
            </a>
        </div>
    `;
    
    return card;
}
