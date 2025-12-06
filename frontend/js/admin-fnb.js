// Admin F&B Orders Management Script
document.addEventListener('DOMContentLoaded', async () => {
    try {
        await loadFnbOrders();
    } catch (error) {
        console.error('Error loading F&B orders:', error);
    }
});

async function loadFnbOrders() {
    try {
        const response = await apiRequest('/admin/get-fnb-orders.php');
        if (response && response.success) {
            const container = document.getElementById('fnbOrdersContainer');
            
            if (response.data.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #999;">No F&B orders found</p>';
            } else {
                container.innerHTML = `
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Reservation ID</th>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="fnbOrdersTableBody">
                            </tbody>
                        </table>
                    </div>
                `;
                
                const tbody = document.getElementById('fnbOrdersTableBody');
                response.data.forEach(order => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>#${order.id_reservation}</td>
                        <td>${order.item}</td>
                        <td>${order.qty}</td>
                        <td>${formatRupiah(order.harga)}</td>
                        <td><span class="status-badge status-${order.status}">${order.status}</span></td>
                        <td><a href="javascript:void(0)" class="btn btn-sm">View</a></td>
                    `;
                    tbody.appendChild(row);
                });
            }
        }
    } catch (error) {
        console.error('Error loading F&B orders:', error);
    }
}
