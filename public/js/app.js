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

    // Cart system
    initCart();

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

    function addItemRow() {
        const productSelect = document.getElementById('product-select');
        const products = JSON.parse(productSelect.dataset.products || '[]');
        const template = createOrderItemRow(itemIndex, products);
        itemsContainer.insertAdjacentHTML('beforeend', template);
        itemIndex++;
        updateOrderTotal();
    }

    addItemBtn.addEventListener('click', addItemRow);

    // Auto-add first item row so the form isn't empty
    if (itemsContainer.children.length === 0 || (itemsContainer.children.length === 1 && itemsContainer.querySelector('.form-error'))) {
        addItemRow();
    }

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
    let hasDrawn = false;
    let lastX = 0, lastY = 0;

    // Set canvas size
    function resizeCanvas() {
        const rect = canvas.parentElement.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = 250;
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 2.5;
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

    function startDraw(e) {
        drawing = true;
        const p = getPos(e);
        lastX = p.x;
        lastY = p.y;
    }

    function draw(e) {
        if (!drawing) return;
        hasDrawn = true;
        const p = getPos(e);
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        lastX = p.x;
        lastY = p.y;
    }

    function stopDraw() { drawing = false; }

    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('touchstart', function(e) { e.preventDefault(); startDraw(e); }, { passive: false });
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('touchmove', function(e) { e.preventDefault(); draw(e); }, { passive: false });
    canvas.addEventListener('mouseup', stopDraw);
    canvas.addEventListener('touchend', stopDraw);
    canvas.addEventListener('mouseleave', stopDraw);

    // Clear button
    const clearBtn = document.getElementById('clear-signature');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hasDrawn = false;
        });
    }

    // On form submit, validate signature and set hidden input to base64
    const form = canvas.closest('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!hasDrawn) {
                e.preventDefault();
                alert('Please provide a signature before completing delivery.');
                return false;
            }
            const input = document.getElementById('signature-data');
            if (input) {
                input.value = canvas.toDataURL('image/png');
            }
        });
    }
}

// Driver GPS tracking
function initDriverTracking() {
    var trackingEl = document.getElementById('driver-tracking');
    if (!trackingEl) return;

    var url = trackingEl.dataset.url;
    var token = document.querySelector('meta[name="csrf-token"]');
    token = token ? token.content : null;

    if (!url || !token || !navigator.geolocation) {
        updateGpsStatus('GPS not available', 'error');
        return;
    }

    var statusEl = document.getElementById('gps-status');
    var watchId = null;
    var lastSendTime = 0;
    var sendPending = false;
    var lastLat = 0;
    var lastLng = 0;
    var consecutiveErrors = 0;
    var maxRetries = 3;

    function updateGpsStatus(text, type) {
        if (!statusEl) return;
        statusEl.textContent = text;
        statusEl.className = 'gps-status gps-status-' + type;
    }

    function sendLocation(pos) {
        var now = Date.now();
        var speed = pos.coords.speed != null ? pos.coords.speed * 3.6 : 0; // m/s to km/h
        var isStationary = speed < 1;
        var minInterval = isStationary ? 60000 : 10000;

        // Throttle sends based on speed
        if (now - lastSendTime < minInterval) return;

        // Skip if position hasn't changed meaningfully (within ~10m)
        if (lastLat !== 0 && lastLng !== 0 && isStationary) {
            var dlat = pos.coords.latitude - lastLat;
            var dlng = pos.coords.longitude - lastLng;
            var dist = Math.sqrt(dlat * dlat + dlng * dlng) * 111000; // rough meters
            if (dist < 10) return;
        }

        if (sendPending) return;
        sendPending = true;
        lastSendTime = now;
        lastLat = pos.coords.latitude;
        lastLng = pos.coords.longitude;

        var speedText = speed > 1 ? Math.round(speed) + ' km/h' : 'Stationary';
        var accText = pos.coords.accuracy ? ' (' + Math.round(pos.coords.accuracy) + 'm)' : '';
        updateGpsStatus('GPS active - ' + speedText + accText, 'ok');

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
                speed: speed,
                heading: pos.coords.heading,
                accuracy: pos.coords.accuracy,
            }),
        }).then(function(res) {
            sendPending = false;
            consecutiveErrors = 0;
            if (res.status === 429) {
                // Throttled by server, will retry next position update
            } else if (!res.ok) {
                updateGpsStatus('GPS active - send error', 'pending');
            }
        }).catch(function() {
            sendPending = false;
            consecutiveErrors++;
            if (consecutiveErrors >= maxRetries) {
                updateGpsStatus('GPS active - server unreachable', 'error');
            } else {
                updateGpsStatus('GPS active - retrying...', 'pending');
            }
        });
    }

    function startWatching() {
        updateGpsStatus('Acquiring GPS...', 'pending');

        watchId = navigator.geolocation.watchPosition(
            function(pos) {
                consecutiveErrors = 0;
                sendLocation(pos);
            },
            function(err) {
                if (err.code === 1) {
                    updateGpsStatus('GPS permission denied', 'error');
                } else if (err.code === 2) {
                    updateGpsStatus('GPS unavailable', 'error');
                    // Retry after a delay
                    setTimeout(function() {
                        if (watchId !== null) {
                            navigator.geolocation.clearWatch(watchId);
                        }
                        startWatching();
                    }, 10000);
                } else if (err.code === 3) {
                    updateGpsStatus('GPS timeout - retrying...', 'pending');
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 20000,
                maximumAge: 5000,
            }
        );
    }

    startWatching();

    // Fallback: if watchPosition doesn't fire for 60s, try getCurrentPosition
    var fallbackTimer = setInterval(function() {
        if (Date.now() - lastSendTime > 60000 && !sendPending) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                sendLocation(pos);
            }, function() {}, { enableHighAccuracy: true, timeout: 15000, maximumAge: 30000 });
        }
    }, 60000);

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (watchId !== null) navigator.geolocation.clearWatch(watchId);
        if (fallbackTimer) clearInterval(fallbackTimer);
    });

    // Re-acquire GPS when app returns to foreground (for mobile WebView)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden && watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
            startWatching();
        }
    });
}

// Shopping Cart
function initCart() {
    var token = document.querySelector('meta[name="csrf-token"]');
    if (!token) return;
    token = token.content;

    // Update cart badge on page load
    updateCartBadge();

    // Add to cart forms (AJAX)
    document.querySelectorAll('.add-to-cart-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var productId = form.dataset.productId;
            var qty = form.querySelector('[name="qty"]').value;
            var btn = form.querySelector('.btn-add-cart');
            var origText = btn.textContent;
            btn.textContent = 'Adding...';
            btn.disabled = true;

            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ product_id: productId, qty: parseFloat(qty) }),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.textContent = 'Added!';
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-success');
                updateCartBadgeCount(data.count);
                showCartToast(data.message || 'Added to order');
                setTimeout(function() {
                    btn.textContent = origText;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-primary');
                    btn.disabled = false;
                }, 1500);
            })
            .catch(function() {
                btn.textContent = origText;
                btn.disabled = false;
            });
        });
    });

    // Cart page: auto-submit qty changes
    document.querySelectorAll('.cart-qty-input').forEach(function(input) {
        var debounce = null;
        input.addEventListener('change', function() {
            clearTimeout(debounce);
            debounce = setTimeout(function() {
                input.closest('.cart-qty-form').submit();
            }, 500);
        });
    });
}

function updateCartBadge() {
    fetch('/cart/count', { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(data) { updateCartBadgeCount(data.count); })
        .catch(function() {});
}

function updateCartBadgeCount(count) {
    var badge = document.getElementById('cart-badge');
    if (!badge) return;
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'inline-flex';
    } else {
        badge.style.display = 'none';
    }
}

function showCartToast(message) {
    var toast = document.createElement('div');
    toast.className = 'cart-toast';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(function() { toast.classList.add('show'); }, 10);
    setTimeout(function() {
        toast.classList.remove('show');
        setTimeout(function() { toast.remove(); }, 300);
    }, 2500);
}

// Open Google Maps / Waze
function openNavigation(lat, lng, address) {
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    const url = isIOS
        ? 'maps://maps.apple.com/?daddr=' + encodeURIComponent(address || (lat + ',' + lng))
        : 'https://www.google.com/maps/dir/?api=1&destination=' + lat + ',' + lng;
    window.open(url, '_blank');
}
