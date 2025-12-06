// Reservation Detail Page Script
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const reservationId = getQueryParam('id');
        
        if (!reservationId) {
            window.location.href = 'reservations.html';
            return;
        }
        
        await loadReservationDetail(reservationId);
    } catch (error) {
        console.error('Error loading reservation detail:', error);
        showAlert('Error loading reservation detail', 'error');
    }
});

async function loadReservationDetail(reservationId) {
    try {
        const response = await apiRequest(`/user/get-reservation-detail.php?id=${reservationId}`);
        
        if (response && response.success) {
            const res = response.data;
            const days = calculateDays(res.check_in, res.check_out);
            
            const container = document.getElementById('detailContainer');
            container.innerHTML = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <div>
                        <h3>Booking Information</h3>
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-top: 15px;">
                            <p><strong>Room Type:</strong> ${res.tipe_kamar}</p>
                            <p><strong>Check-in:</strong> ${formatDate(res.check_in)}</p>
                            <p><strong>Check-out:</strong> ${formatDate(res.check_out)}</p>
                            <p><strong>Duration:</strong> ${days} nights</p>
                            <p><strong>Guests:</strong> ${res.jumlah_orang} person(s)</p>
                            <p><strong>Status:</strong> <span class="status-badge status-${res.status}">${res.status}</span></p>
                        </div>
                    </div>
                    
                    <div>
                        <h3>Price Summary</h3>
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-top: 15px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span>Price per night:</span>
                                <strong>${formatRupiah(res.harga)}</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                                <span>Number of nights:</span>
                                <strong>${days}</strong>
                            </div>
                            <div style="border-top: 2px solid #e0e0e0; padding-top: 15px; display: flex; justify-content: space-between; font-size: 20px; font-weight: 700;">
                                <span>Total:</span>
                                <span style="color: #ff7a89;">${formatRupiah(res.total_harga)}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 30px;">
                    <h3>Payment Status</h3>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-top: 15px;">
                        ${res.payment_status ? `
                            <p><strong>Status:</strong> <span class="status-badge status-${res.payment_status}">${res.payment_status}</span></p>
                            <p><strong>Method:</strong> ${res.payment_method}</p>
                            <p><strong>Paid at:</strong> ${res.paid_at ? formatDate(res.paid_at) : 'Not paid yet'}</p>
                        ` : `
                            <p style="color: #999;">No payment information available</p>
                        `}
                    </div>
                </div>
                
                ${res.status === 'pending' ? `
                    <div style="margin-top: 30px; display: flex; gap: 10px;">
                        <button class="btn btn-primary" onclick="goToPayment(${res.id_reservation})">Proceed to Payment</button>
                        <button class="btn btn-outline" onclick="deleteReservation(${res.id_reservation})">Delete</button>
                    </div>
                ` : ''}
            `;
        } else {
            showAlert('Reservation not found', 'error');
            setTimeout(() => window.location.href = 'reservations.html', 2000);
        }
    } catch (error) {
        console.error('Error loading reservation detail:', error);
        showAlert('Error loading reservation detail', 'error');
    }
}

function goToPayment(reservationId) {
    window.location.href = `payment.html?reservation=${reservationId}`;
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
                window.location.href = 'reservations.html';
            }, 1500);
        } else {
            showAlert(response?.message || 'Error deleting reservation', 'error');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showAlert('Error deleting reservation', 'error');
    }
}
