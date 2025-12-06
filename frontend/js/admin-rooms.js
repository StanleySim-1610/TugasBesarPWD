// Admin Rooms Management Script
document.addEventListener('DOMContentLoaded', async () => {
    try {
        await loadRooms();
        setupFormHandlers();
    } catch (error) {
        console.error('Error loading rooms:', error);
    }
});

async function loadRooms() {
    try {
        const response = await apiRequest('/admin/get-rooms.php');
        if (response && response.success) {
            const container = document.getElementById('roomsContainer');
            
            if (response.data.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #999;">No rooms found</p>';
            } else {
                container.innerHTML = `
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Room Type</th>
                                    <th>Available</th>
                                    <th>Price</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="roomsTableBody">
                            </tbody>
                        </table>
                    </div>
                `;
                
                const tbody = document.getElementById('roomsTableBody');
                response.data.forEach(room => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${room.tipe_kamar}</td>
                        <td>${room.jumlah_tersedia}</td>
                        <td>${formatRupiah(room.harga)}</td>
                        <td>${room.deskripsi.substring(0, 50)}...</td>
                        <td>
                            <button class="btn btn-sm" onclick="editRoom(${room.id_kamar})">Edit</button>
                            <button class="btn btn-sm" style="background:#dc3545; color:white;" onclick="deleteRoom(${room.id_kamar})">Delete</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }
        }
    } catch (error) {
        console.error('Error loading rooms:', error);
    }
}

function setupFormHandlers() {
    const roomForm = document.getElementById('roomForm');
    if (roomForm) {
        roomForm.addEventListener('submit', handleRoomSubmit);
    }
}

async function handleRoomSubmit(e) {
    e.preventDefault();
    
    const tipeKamar = document.getElementById('tipe_kamar').value;
    const jumlahTersedia = document.getElementById('jumlah_tersedia').value;
    const harga = document.getElementById('harga').value;
    const deskripsi = document.getElementById('deskripsi').value;
    
    try {
        const response = await apiRequest('/admin/save-room.php', {
            method: 'POST',
            body: {
                tipe_kamar: tipeKamar,
                jumlah_tersedia: jumlahTersedia,
                harga,
                deskripsi
            }
        });
        
        if (response && response.success) {
            alert('Room saved successfully');
            closeRoomModal();
            await loadRooms();
        }
    } catch (error) {
        alert('Error saving room: ' + error.message);
    }
}

function openAddRoomModal() {
    document.getElementById('roomForm').reset();
    document.getElementById('roomModal').style.display = 'flex';
}

function closeRoomModal() {
    document.getElementById('roomModal').style.display = 'none';
}

async function editRoom(roomId) {
    try {
        const response = await apiRequest(`/admin/get-room.php?id=${roomId}`);
        if (response && response.success) {
            const room = response.data;
            document.getElementById('tipe_kamar').value = room.tipe_kamar;
            document.getElementById('jumlah_tersedia').value = room.jumlah_tersedia;
            document.getElementById('harga').value = room.harga;
            document.getElementById('deskripsi').value = room.deskripsi;
            openAddRoomModal();
        }
    } catch (error) {
        alert('Error loading room');
    }
}

async function deleteRoom(roomId) {
    if (!confirm('Are you sure you want to delete this room?')) return;
    
    try {
        const response = await apiRequest(`/admin/delete-room.php?id=${roomId}`, {
            method: 'POST'
        });
        
        if (response && response.success) {
            alert('Room deleted successfully');
            await loadRooms();
        }
    } catch (error) {
        alert('Error deleting room');
    }
}
