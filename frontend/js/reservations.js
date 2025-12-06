// Reservations Page Script
document.addEventListener('DOMContentLoaded', async () => {
    try {
        await loadAllReservations();
    } catch (error) {
        console.error('Error loading reservations:', error);
        showAlert('Error loading reservations', 'error');
    }
});

async function loadAllReservations() {
    try {
        const response = await apiRequest('/user/get-reservations.php');
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
                        <td>
                            <a href="reservation-detail.html?id=${res.id_reservation}" class="btn btn-sm">View</a>
                            ${res.status === 'pending' ? `<a href="javascript:deleteReservation(${res.id_reservation})" class="btn btn-sm" style="background: #dc3545; color: white;">Delete</a>` : ''}
                        </td>
                    `;
                    
                    tbody.appendChild(row);
                });
            }
        }
    } catch (error) {
        console.error('Error loading reservations:', error);
    }
}

async function deleteReservation(reservationId) {
    if (!confirm('Are you sure you want to delete this reservation?')) {
        return;
    }
    
    try {
        const response = await apiRequest(`/user/delete-reservation.php?id=${reservationId}`, {
            method: 'POST'
        });
        
        if (response && response.success) {
            showAlert('Reservation deleted successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(response?.message || 'Error deleting reservation', 'error');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showAlert('Error deleting reservation', 'error');
    }
}
