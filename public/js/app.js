// Concreto App JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Mobile navbar toggle
    const navToggle = document.querySelector('.navbar-toggle');
    const navLinks = document.querySelector('.navbar-links');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function() {
            navLinks.classList.toggle('open');
        });
    }

    // Admin sidebar toggle
    const menuToggle = document.querySelector('.admin-menu-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
        // Close sidebar on outside click (mobile)
        document.addEventListener('click', function(e) {
            if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('.alert[data-dismiss]').forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s';
            setTimeout(function() { alert.remove(); }, 300);
        }, 5000);
    });

    // Order item management for order creation
    initOrderForm();

    // Signature pad
    initSignaturePad();

    // Driver location tracking
    initDriverTracking();
});

// Order creation form
function initOrderForm() {
    const addItemBtn = document.getElementById('add-item-btn');
    const itemsContainer = document.getElementById('order-items');
    if (!addItemBtn || !itemsContainer) return;

    let itemIndex = 0;

    addItemBtn.addEventListener('click', function() {
        const productSelect = document.getElementById('product-select');
        const products = JSON.parse(productSelect.dataset.products || '[]');
        const template = createOrderItemRow(itemIndex, products);
        itemsContainer.insertAdjacentHTML('beforeend', template);
        itemIndex++;
        updateOrderTotal();
    });

    // Delegate change/input events for quantity
    itemsContainer.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-qty')) {
            updateOrderTotal();
        }
    });
    itemsContainer.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-product')) {
            updateOrderTotal();
        }
    });

    // Remove item
    itemsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.order-item-row').remove();
            updateOrderTotal();
        }
    });
}

function createOrderItemRow(index, products) {
    let options = '<option value="">Select product</option>';
    products.forEach(function(p) {
        options += '<option value="' + p.id + '" data-price="' + p.price + '" data-unit="' + p.unit + '">' +
            p.name + ' - R' + parseFloat(p.price).toFixed(2) + '/' + p.unit + '</option>';
    });

    return '<div class="order-item-row" style="display:flex;gap:0.5rem;align-items:end;margin-bottom:0.5rem;flex-wrap:wrap;">' +
        '<div style="flex:2;min-width:150px;"><select name="items[' + index + '][product_id]" class="form-control item-product" required>' + options + '</select></div>' +
        '<div style="flex:1;min-width:80px;"><input type="number" name="items[' + index + '][qty]" class="form-control item-qty" placeholder="Qty" step="0.01" min="0.01" required></div>' +
        '<div style="flex:1;min-width:80px;"><span class="item-total text-muted">R0.00</span></div>' +
        '<button type="button" class="btn btn-danger btn-sm remove-item">X</button>' +
        '</div>';
}

function updateOrderTotal() {
    let total = 0;
    document.querySelectorAll('.order-item-row').forEach(function(row) {
        const select = row.querySelector('.item-product');
        const qtyInput = row.querySelector('.item-qty');
        const totalSpan = row.querySelector('.item-total');
        if (select && qtyInput && select.value) {
            const option = select.selectedOptions[0];
            const price = parseFloat(option.dataset.price || 0);
            const qty = parseFloat(qtyInput.value || 0);
            const lineTotal = price * qty;
            totalSpan.textContent = 'R' + lineTotal.toFixed(2);
            total += lineTotal;
        }
    });
    const totalEl = document.getElementById('order-total');
    if (totalEl) {
        const vat = total * 0.15;
        totalEl.textContent = 'Subtotal: R' + total.toFixed(2) + ' + VAT: R' + vat.toFixed(2) + ' = R' + (total + vat).toFixed(2);
    }
}

// Signature pad using canvas
function initSignaturePad() {
    const canvas = document.getElementById('signature-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let drawing = false;
    let lastX = 0, lastY = 0;

    // Set canvas size
    function resizeCanvas() {
        const rect = canvas.parentElement.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = 200;
        ctx.strokeStyle = '#2c3e50';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const touch = e.touches ? e.touches[0] : e;
        return { x: touch.clientX - rect.left, y: touch.clientY - rect.top };
    }

    canvas.addEventListener('mousedown', function(e) { drawing = true; const p = getPos(e); lastX = p.x; lastY = p.y; });
    canvas.addEventListener('touchstart', function(e) { e.preventDefault(); drawing = true; const p = getPos(e); lastX = p.x; lastY = p.y; }, { passive: false });

    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('touchmove', function(e) { e.preventDefault(); draw(e); }, { passive: false });

    canvas.addEventListener('mouseup', function() { drawing = false; });
    canvas.addEventListener('touchend', function() { drawing = false; });
    canvas.addEventListener('mouseleave', function() { drawing = false; });

    function draw(e) {
        if (!drawing) return;
        const p = getPos(e);
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        lastX = p.x;
        lastY = p.y;
    }

    // Clear button
    const clearBtn = document.getElementById('clear-signature');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        });
    }

    // On form submit, set hidden input to base64
    const form = canvas.closest('form');
    if (form) {
        form.addEventListener('submit', function() {
            const input = document.getElementById('signature-data');
            if (input) {
                input.value = canvas.toDataURL('image/png');
            }
        });
    }
}

// Driver GPS tracking
function initDriverTracking() {
    const trackingEl = document.getElementById('driver-tracking');
    if (!trackingEl) return;

    const orderId = trackingEl.dataset.orderId;
    const url = trackingEl.dataset.url;
    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!orderId || !url || !navigator.geolocation) return;

    let trackingInterval = null;

    function sendLocation() {
        navigator.geolocation.getCurrentPosition(function(pos) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    lat: pos.coords.latitude,
                    lng: pos.coords.longitude,
                    speed: pos.coords.speed,
                    heading: pos.coords.heading,
                }),
            }).catch(function() { /* silent fail */ });
        }, function() { /* silent fail */ }, {
            enableHighAccuracy: true,
            timeout: 10000,
        });
    }

    // Send every 30 seconds
    sendLocation();
    trackingInterval = setInterval(sendLocation, 30000);

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (trackingInterval) clearInterval(trackingInterval);
    });
}

// Open Google Maps / Waze
function openNavigation(lat, lng, address) {
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    const url = isIOS
        ? 'maps://maps.apple.com/?daddr=' + encodeURIComponent(address || (lat + ',' + lng))
        : 'https://www.google.com/maps/dir/?api=1&destination=' + lat + ',' + lng;
    window.open(url, '_blank');
}
