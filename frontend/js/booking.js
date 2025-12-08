document.addEventListener('DOMContentLoaded', function() {
    initializeBooking();
});

function initializeBooking() {
    const checkInInput = document.getElementById('checkIn');
    const checkOutInput = document.getElementById('checkOut');
    
    if (checkInInput && checkOutInput) {
        checkInInput.addEventListener('change', updateBookingPrice);
        checkOutInput.addEventListener('change', updateBookingPrice);
    }

    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            if (!validateBookingForm()) {
                e.preventDefault();
            }
        });
    }

    const priceElements = document.querySelectorAll('[data-price]');
    priceElements.forEach(el => {
        const price = parseFloat(el.dataset.price);
        el.textContent = formatRupiah(price);
    });
}

function updateBookingPrice() {
    const checkInInput = document.getElementById('checkIn');
    const checkOutInput = document.getElementById('checkOut');
    const pricePerNightElement = document.getElementById('pricePerNight');
    const daysElement = document.getElementById('days');
    const totalPriceElement = document.getElementById('totalPrice');

    if (checkInInput && checkOutInput && checkInInput.value && checkOutInput.value) {
        const days = calculateDays(checkInInput.value, checkOutInput.value);
        
        if (pricePerNightElement && daysElement && totalPriceElement) {
            const pricePerNight = parseFloat(pricePerNightElement.dataset.price);
            const totalPrice = pricePerNight * days;
            
            daysElement.textContent = days + ' malam';
            totalPriceElement.textContent = formatRupiah(totalPrice);
            
            const totalPriceInput = document.getElementById('totalPrice_input');
            if (totalPriceInput) {
                totalPriceInput.value = totalPrice;
            }
        }
    }
}

function validateBookingForm() {
    const checkInInput = document.getElementById('checkIn');
    const checkOutInput = document.getElementById('checkOut');
    const jumlahOrangInput = document.getElementById('jumlahOrang');

    let isValid = true;

    document.querySelectorAll('.error-message').forEach(el => el.remove());

    if (!checkInInput.value) {
        showFieldError(checkInInput, 'Tanggal check-in harus diisi');
        isValid = false;
    }

    if (!checkOutInput.value) {
        showFieldError(checkOutInput, 'Tanggal check-out harus diisi');
        isValid = false;
    }

    if (checkInInput.value && checkOutInput.value) {
        if (new Date(checkOutInput.value) <= new Date(checkInInput.value)) {
            showFieldError(checkOutInput, 'Tanggal check-out harus lebih besar dari check-in');
            isValid = false;
        }
    }
    
    if (!jumlahOrangInput.value || jumlahOrangInput.value < 1) {
        showFieldError(jumlahOrangInput, 'Jumlah orang minimal 1');
        isValid = false;
    }

    return isValid;
}

function showFieldError(field, message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}
