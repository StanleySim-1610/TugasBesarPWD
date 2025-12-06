// Rooms Page - Handle rooms list and interactions
document.addEventListener('DOMContentLoaded', function() {
    initializeRooms();
});

function initializeRooms() {
    // Handle book room buttons
    const bookButtons = document.querySelectorAll('.book-room-btn');
    bookButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const roomId = this.dataset.roomId;
            window.location.href = '../../backend/user/booking.php?room_id=' + roomId;
        });
    });

    // Format prices display
    const priceElements = document.querySelectorAll('[data-price]');
    priceElements.forEach(el => {
        const price = parseFloat(el.dataset.price);
        el.textContent = formatRupiah(price);
    });

    // Handle room filter if any
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const category = this.dataset.category;
            filterRoomsByCategory(category);
        });
    });
}

function filterRoomsByCategory(category) {
    const roomCards = document.querySelectorAll('.room-card');
    roomCards.forEach(card => {
        if (category === 'all' || card.dataset.category === category) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
