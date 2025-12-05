// Admin Payments Management Script
document.addEventListener('DOMContentLoaded', async () => {
    try {
        await loadPayments();
    } catch (error) {
        console.error('Error loading payments:', error);
    }
});

async function loadPayments() {
    try {
        const response = await apiRequest('/admin/get-payments.php');
        if (response && response.success) {
            const container = document.getElementById('paymentsContainer');
            
            if (response.data.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #999;">No payments found</p>';
            } else {
                container.innerHTML = `
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Reservation ID</th>
                                    <th>Total Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Paid Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="paymentsTableBody">
                            </tbody>
                        </table>
                    </div>
                `;
                
                const tbody = document.getElementById('paymentsTableBody');
                response.data.forEach(payment => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>#${payment.id_reservation}</td>
                        <td>${formatRupiah(payment.total_bayar)}</td>
                        <td>${payment.metode}</td>
                        <td><span class="status-badge status-${payment.status}">${payment.status}</span></td>
                        <td>${payment.paid_at ? formatDate(payment.paid_at) : '-'}</td>
                        <td><a href="javascript:void(0)" class="btn btn-sm">View</a></td>
                    `;
                    tbody.appendChild(row);
                });
            }
        }
    } catch (error) {
        console.error('Error loading payments:', error);
    }
}
