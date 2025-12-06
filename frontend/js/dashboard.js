// Dashboard Page Script
document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Load user data
        await loadUserData();
        
        // Load stats
        await loadStats();
        
        // Load available rooms
        await loadRooms();
        
        // Load recent reservations
        await loadRecentReservations();
    } catch (error) {
        console.error('Error loading dashboard:', error);
        showAlert('Error loading dashboard', 'error');
    }
});

async function loadUserData() {
    try {
        const response = await apiRequest('/user/get-profile.php');
        if (response && response.success) {
            const user = response.data;
            document.getElementById('userName').textContent = user.nama;
            document.getElementById('userEmail').textContent = user.email;
            
            // Update avatar
            const avatar = document.getElementById('userAvatar');
            avatar.textContent = user.nama.charAt(0).toUpperCase();
        }
    } catch (error) {
        console.error('Error loading user data:', error);
    }
}

async function loadStats() {
    try {
        const response = await apiRequest('/user/get-stats.php');
        if (response && response.success) {
            document.getElementById('totalReservations').textContent = response.data.total_reservations;
            document.getElementById('activeBookings').textContent = response.data.active_bookings;
            document.getElementById('pendingBookings').textContent = response.data.pending_bookings;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

async function loadRooms() {
    try {
        const response = await apiRequest('/user/get-available-rooms.php');
        if (response && response.success) {
            const roomsContainer = document.getElementById('roomsGrid');
            roomsContainer.innerHTML = '';
            
            response.data.forEach(room => {
                const roomCard = createRoomCard(room);
                roomsContainer.appendChild(roomCard);
            });
        }
    } catch (error) {
        console.error('Error loading rooms:', error);
    }
}

function createRoomCard(room) {
    const card = document.createElement('div');
    card.className = 'room-card';
    
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
        <img src="../assets/room_photo/${gambar}" alt="${room.tipe_kamar}" class="room-image-dashboard">
        
        <div class="room-content-padding">
            <div class="room-header">
                <h3>${room.tipe_kamar}</h3>
                <span class="room-available">${room.jumlah_tersedia} available</span>
            </div>
            <p class="room-description">${room.deskripsi}</p>
            
            <div class="room-footer">
                <div class="room-price">
                    ${formatRupiah(room.harga)}<span>/night</span>
                </div>
                <a href="booking.html?room=${room.id_kamar}" class="btn btn-primary" style="padding: 8px 16px;">Book Now</a>
            </div>
        </div>
    `;
    
    return card;
}

async function loadRecentReservations() {
    try {
        const response = await apiRequest('/user/get-reservations.php?limit=5');
        if (response && response.success) {
            const container = document.getElementById('reservationsContainer');
            
            if (response.data.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <p>You don't have any reservations yet.</p>
                        <a href="rooms.html" class="btn btn-primary">Browse Rooms</a>
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Room Type</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Duration</th>
                                    <th>Total Price</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="reservationsTableBody">
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center" style="margin-top: 20px;">
                        <a href="reservations.html" class="btn btn-outline">View All Reservations</a>
                    </div>
                `;
                
                const tbody = document.getElementById('reservationsTableBody');
                response.data.forEach(res => {
                    const row = document.createElement('tr');
                    const days = calculateDays(res.check_in, res.check_out);
                    
                    row.innerHTML = `
                        <td>${res.tipe_kamar}</td>
                        <td>${formatDate(res.check_in)}</td>
                        <td>${formatDate(res.check_out)}</td>
                        <td>${days} days</td>
                        <td>${formatRupiah(res.total_harga)}</td>
                        <td><span class="status-badge status-${res.status}">${res.status.charAt(0).toUpperCase() + res.status.slice(1)}</span></td>
                        <td><a href="reservations.html#${res.id_reservation}" class="btn btn-sm">View</a></td>
                    `;
                    
                    tbody.appendChild(row);
                });
            }
        }
    } catch (error) {
        console.error('Error loading reservations:', error);
    }
}
