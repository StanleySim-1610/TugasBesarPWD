document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

function initializeDashboard() {
    const bookingButtons = document.querySelectorAll('.book-now-btn');
    bookingButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const roomId = this.dataset.roomId;
            window.location.href = '../../backend/user/booking.php?room_id=' + roomId;
        });
    });

    const priceElements = document.querySelectorAll('[data-price]');
    priceElements.forEach(el => {
        const price = parseFloat(el.dataset.price);
        el.textContent = formatRupiah(price);
    });
}
