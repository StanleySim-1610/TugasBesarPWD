// Admin Reservations Management Script
document.addEventListener('DOMContentLoaded', async () => {
    try {
        await loadReservations();
    } catch (error) {
        console.error('Error loading reservations:', error);
    }
});

async function loadReservations() {
    try {
        const response = await apiRequest('/admin/get-all-reservations.php');
        if (response && response.success) {
            const container = document.getElementById('reservationsContainer');
            
            if (response.data.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #999;">No reservations found</p>';
            } else {
                container.innerHTML = `
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Guest Name</th>
                                    <th>Room Type</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
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
                    row.innerHTML = `
                        <td>${res.nama}</td>
                        <td>${res.tipe_kamar}</td>
                        <td>${formatDate(res.check_in)}</td>
                        <td>${formatDate(res.check_out)}</td>
                        <td>${formatRupiah(res.total_harga)}</td>
                        <td><span class="status-badge status-${res.status}">${res.status}</span></td>
                        <td>
                            <a href="reservation-detail.html?id=${res.id_reservation}" class="btn btn-sm">View</a>
                            <button class="btn btn-sm" style="background:#ff9800; color:white;" onclick="updateStatus(${res.id_reservation})">Update</button>
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

function updateStatus(reservationId) {
    const newStatus = prompt('Enter new status (pending, confirmed, cancelled, completed):');
    if (!newStatus) return;
    
    // Implementation would call API to update status
    alert('Status update would be implemented here');
}
