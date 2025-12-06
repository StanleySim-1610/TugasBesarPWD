// Reservations Page - Handle reservations list and actions
document.addEventListener('DOMContentLoaded', function() {
    initializeReservations();
});

function initializeReservations() {
    // Handle delete reservation buttons
    const deleteButtons = document.querySelectorAll('.delete-reservation-btn');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const reservationId = this.dataset.reservationId;
            deleteReservation(reservationId);
        });
    });

    // Handle edit reservation buttons
    const editButtons = document.querySelectorAll('.edit-reservation-btn');
    editButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const reservationId = this.dataset.reservationId;
            window.location.href = '../../backend/user/edit_reservation.php?id=' + reservationId;
        });
    });

    // Handle view detail buttons
    const detailButtons = document.querySelectorAll('.detail-reservation-btn');
    detailButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const reservationId = this.dataset.reservationId;
            window.location.href = '../../backend/user/reservation_detail.php?id=' + reservationId;
        });
    });

    // Format prices display
    const priceElements = document.querySelectorAll('[data-price]');
    priceElements.forEach(el => {
        const price = parseFloat(el.dataset.price);
        el.textContent = formatRupiah(price);
    });

    // Format dates display
    const dateElements = document.querySelectorAll('[data-date]');
    dateElements.forEach(el => {
        const dateString = el.dataset.date;
        el.textContent = formatDate(dateString);
    });
}

function deleteReservation(reservationId) {
    if (confirm('Apakah Anda yakin ingin membatalkan reservasi ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../../backend/user/delete_reservation.php';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'id';
        input.value = reservationId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
