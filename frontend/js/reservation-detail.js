document.addEventListener('DOMContentLoaded', function() {
    initializeReservationDetail();
});

function initializeReservationDetail() {
    const priceElements = document.querySelectorAll('[data-price]');
    priceElements.forEach(el => {
        const price = parseFloat(el.dataset.price);
        el.textContent = formatRupiah(price);
    });

    const dateElements = document.querySelectorAll('[data-date]');
    dateElements.forEach(el => {
        const dateString = el.dataset.date;
        el.textContent = formatDate(dateString);
    });

    const editButton = document.getElementById('editReservationBtn');
    if (editButton) {
        editButton.addEventListener('click', function() {
            const reservationId = this.dataset.reservationId;
            window.location.href = '../../backend/user/edit_reservation.php?id=' + reservationId;
        });
    }

    const deleteButton = document.getElementById('deleteReservationBtn');
    if (deleteButton) {
        deleteButton.addEventListener('click', function() {
            const reservationId = this.dataset.reservationId;
            deleteReservation(reservationId);
        });
    }

    const paymentButton = document.getElementById('paymentBtn');
    if (paymentButton) {
        paymentButton.addEventListener('click', function() {
            const reservationId = this.dataset.reservationId;
            window.location.href = '../../backend/user/payment.php?reservation_id=' + reservationId;
        });
    }
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
