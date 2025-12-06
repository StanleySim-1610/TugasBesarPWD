// Booking Page Script
let currentRoom = null;

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const roomId = getQueryParam('room');
        
        if (!roomId) {
            window.location.href = 'rooms.html';
            return;
        }
        
        await loadRoomDetails(roomId);
        setupPriceCalculation();
    } catch (error) {
        console.error('Error loading booking page:', error);
        showAlert('Error loading room details', 'error');
    }
});

async function loadRoomDetails(roomId) {
    try {
        const response = await apiRequest(`/user/get-room.php?id=${roomId}`);
        
        if (response && response.success) {
            currentRoom = response.data;
            
            document.getElementById('roomTitle').textContent = currentRoom.tipe_kamar;
            document.getElementById('roomDescription').textContent = currentRoom.deskripsi;
            document.getElementById('pricePerNight').textContent = formatRupiah(currentRoom.harga);
            document.getElementById('summaryPricePerNight').textContent = formatRupiah(currentRoom.harga);
            document.getElementById('availableRooms').textContent = currentRoom.jumlah_tersedia + ' rooms';
            
            // Set min date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('check_in').min = today;
            document.getElementById('check_out').min = today;
        } else {
            showAlert('Room not found', 'error');
            setTimeout(() => window.location.href = 'rooms.html', 2000);
        }
    } catch (error) {
        console.error('Error loading room details:', error);
        showAlert('Error loading room details', 'error');
    }
}

function setupPriceCalculation() {
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    
    function calculatePrice() {
        const checkIn = new Date(checkInInput.value);
        const checkOut = new Date(checkOutInput.value);
        
        if (checkIn && checkOut && checkOut > checkIn) {
            const diffTime = Math.abs(checkOut - checkIn);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const total = currentRoom.harga * diffDays;
            
            document.getElementById('numberOfNights').textContent = diffDays;
            document.getElementById('totalPrice').textContent = formatRupiah(total);
        }
    }
    
    checkInInput.addEventListener('change', calculatePrice);
    checkOutInput.addEventListener('change', calculatePrice);
    
    // Update min checkout date when checkin changes
    checkInInput.addEventListener('change', function() {
        const checkInDate = new Date(this.value);
        checkInDate.setDate(checkInDate.getDate() + 1);
        checkOutInput.min = checkInDate.toISOString().split('T')[0];
    });
}

// Form submission
document.addEventListener('DOMContentLoaded', () => {
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', handleBookingSubmit);
    }
});

async function handleBookingSubmit(e) {
    e.preventDefault();
    
    try {
        const checkIn = document.getElementById('check_in').value;
        const checkOut = document.getElementById('check_out').value;
        const jumlahOrang = document.getElementById('jumlah_orang').value;
        
        // Validation
        if (!checkIn || !checkOut || !jumlahOrang) {
            showAlert('Semua field wajib diisi!', 'error');
            return;
        }
        
        if (new Date(checkIn) < new Date()) {
            showAlert('Tanggal check-in tidak boleh kurang dari hari ini!', 'error');
            return;
        }
        
        if (new Date(checkOut) <= new Date(checkIn)) {
            showAlert('Tanggal check-out harus lebih dari check-in!', 'error');
            return;
        }
        
        // Calculate total price
        const days = calculateDays(checkIn, checkOut);
        const totalHarga = currentRoom.harga * days;
        
        // Submit booking
        const response = await apiRequest('/user/create-reservation.php', {
            method: 'POST',
            body: {
                id_kamar: currentRoom.id_kamar,
                check_in: checkIn,
                check_out: checkOut,
                jumlah_orang: jumlahOrang,
                total_harga: totalHarga
            }
        });
        
        if (response && response.success) {
            showAlert('Booking berhasil! Redirecting to payment...', 'success');
            setTimeout(() => {
                window.location.href = `payment.html?reservation=${response.data.id_reservation}`;
            }, 2000);
        } else {
            showAlert(response?.message || 'Terjadi kesalahan. Silakan coba lagi.', 'error');
        }
    } catch (error) {
        console.error('Booking error:', error);
        showAlert('Terjadi kesalahan saat membuat reservasi', 'error');
    }
}
