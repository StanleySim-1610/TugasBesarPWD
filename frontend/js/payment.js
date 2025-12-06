// Payment Page - Handle payment form and calculations
document.addEventListener('DOMContentLoaded', function() {
    initializePayment();
});

function initializePayment() {
    // Format prices display
    const priceElements = document.querySelectorAll('[data-price]');
    priceElements.forEach(el => {
        const price = parseFloat(el.dataset.price);
        el.textContent = formatRupiah(price);
    });

    // Handle payment method selection
    const paymentMethods = document.querySelectorAll('input[name="metode"]');
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            updatePaymentInfo(this.value);
        });
    });

    // Handle payment form submission
    const paymentForm = document.getElementById('paymentForm');
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            if (!validatePaymentForm()) {
                e.preventDefault();
            }
        });
    }
}

function updatePaymentInfo(method) {
    const infoDiv = document.getElementById('paymentInfo');
    if (infoDiv) {
        let info = '';
        switch(method) {
            case 'transfer':
                info = 'Silakan transfer ke rekening yang akan ditampilkan';
                break;
            case 'kartu_kredit':
                info = 'Masukkan data kartu kredit Anda';
                break;
            case 'tunai':
                info = 'Pembayaran tunai dilakukan saat check-in';
                break;
            default:
                info = '';
        }
        infoDiv.textContent = info;
    }
}

function validatePaymentForm() {
    const methodInput = document.querySelector('input[name="metode"]:checked');
    
    if (!methodInput) {
        showAlert('Pilih metode pembayaran terlebih dahulu', 'error');
        return false;
    }

    return true;
}
