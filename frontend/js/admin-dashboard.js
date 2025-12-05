// Admin Dashboard Script
document.addEventListener('DOMContentLoaded', async () => {
    try {
        await loadAdminData();
        await loadStats();
        await loadRecentReservations();
    } catch (error) {
        console.error('Error loading admin dashboard:', error);
    }
});

async function loadAdminData() {
    try {
        const response = await apiRequest('/admin/get-admin-info.php');
        if (response && response.success) {
            document.getElementById('adminName').textContent = response.data.nama;
            document.getElementById('adminAvatar').textContent = response.data.nama.charAt(0).toUpperCase();
        }
    } catch (error) {
        console.error('Error loading admin data:', error);
    }
}

async function loadStats() {
    try {
        const response = await apiRequest('/admin/get-dashboard-stats.php');
        if (response && response.success) {
            document.getElementById('totalRooms').textContent = response.data.total_rooms;
            document.getElementById('totalReservations').textContent = response.data.total_reservations;
            document.getElementById('totalUsers').textContent = response.data.total_users;
            document.getElementById('totalRevenue').textContent = formatRupiah(response.data.total_revenue);
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

async function loadRecentReservations() {
    try {
        const response = await apiRequest('/admin/get-reservations.php?limit=10');
        if (response && response.success) {
            const container = document.getElementById('recentReservations');
            
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
                        <td><a href="reservation-detail.html?id=${res.id_reservation}" class="btn btn-sm">View</a></td>
                    `;
                    tbody.appendChild(row);
                });
            }
        }
    } catch (error) {
        console.error('Error loading reservations:', error);
    }
}
