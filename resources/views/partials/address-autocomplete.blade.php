{{--
    Mapbox Address Autocomplete
    Usage: @include('partials.address-autocomplete', ['inputId' => 'vendor-address-search', 'fields' => [...]])

    Required $fields keys (all optional — omit any you don't need):
        'line1'     => DOM ID for street address line 1
        'line2'     => DOM ID for street address line 2 (suburb)
        'city'      => DOM ID for city
        'province'  => DOM ID for province (can be input or select)
        'postal'    => DOM ID for postal code
        'lat'       => DOM ID for hidden GPS latitude
        'lng'       => DOM ID for hidden GPS longitude
        'gpsDisplay'=> DOM ID for GPS display text (optional)
--}}
@push('styles')
<style>
    .mapbox-ac-wrap { position:relative; }
    .mapbox-ac-list { position:absolute; top:100%; left:0; right:0; z-index:999; background:var(--card, #1a1a2e); border:1px solid var(--glass-border, rgba(255,255,255,0.1)); border-top:none; border-radius:0 0 var(--radius-sm, 6px) var(--radius-sm, 6px); max-height:240px; overflow-y:auto; display:none; box-shadow:0 8px 24px rgba(0,0,0,0.4); }
    .mapbox-ac-list.open { display:block; }
    .mapbox-ac-item { padding:10px 14px; cursor:pointer; font-size:0.8125rem; color:var(--text, #fff); border-bottom:1px solid rgba(255,255,255,0.05); transition:background 0.15s; }
    .mapbox-ac-item:last-child { border-bottom:none; }
    .mapbox-ac-item:hover, .mapbox-ac-item.active { background:rgba(255,255,255,0.08); }
    .mapbox-ac-item small { color:var(--text-muted, #999); display:block; margin-top:2px; }
    .mapbox-ac-empty { padding:12px 14px; font-size:0.8rem; color:var(--text-muted, #999); }
</style>
@endpush

@push('scripts')
<script>
(function() {
    if (window._mapboxAcInit) return;
    window._mapboxAcInit = true;

    var MAPBOX_TOKEN = @json(config('services.mapbox.token'));
    var debounceTimers = {};

    window.initMapboxAutocomplete = function(inputId, fields) {
        var input = document.getElementById(inputId);
        if (!input) return;

        // Wrap input for dropdown positioning
        var wrap = document.createElement('div');
        wrap.className = 'mapbox-ac-wrap';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);

        var list = document.createElement('div');
        list.className = 'mapbox-ac-list';
        wrap.appendChild(list);

        var activeIdx = -1;
        var results = [];

        function close() {
            list.classList.remove('open');
            list.innerHTML = '';
            results = [];
            activeIdx = -1;
        }

        function fillFields(feature) {
            var ctx = {};
            (feature.context || []).forEach(function(c) {
                var type = c.id.split('.')[0];
                ctx[type] = c.text;
                if (c.short_code) ctx[type + '_short'] = c.short_code;
            });

            var street = [(feature.address || ''), (feature.text || '')].filter(Boolean).join(' ');

            if (fields.line1) setValue(fields.line1, street);
            if (fields.line2) setValue(fields.line2, ctx.neighborhood || ctx.locality || '');
            if (fields.city) setValue(fields.city, ctx.place || ctx.locality || '');
            if (fields.province) setProvince(fields.province, ctx.region || '');
            if (fields.postal) setValue(fields.postal, ctx.postcode || '');
            if (fields.lat && feature.center) setValue(fields.lat, feature.center[1]);
            if (fields.lng && feature.center) setValue(fields.lng, feature.center[0]);
            if (fields.gpsDisplay && feature.center) {
                var el = document.getElementById(fields.gpsDisplay);
                if (el) el.textContent = 'GPS: ' + feature.center[1].toFixed(7) + ', ' + feature.center[0].toFixed(7);
            }

            input.value = feature.place_name || street;
            close();
        }

        function setValue(id, val) {
            var el = document.getElementById(id);
            if (el) el.value = val;
        }

        function setProvince(id, val) {
            var el = document.getElementById(id);
            if (!el) return;
            if (el.tagName === 'SELECT') {
                // Try exact match first, then partial
                var found = false;
                for (var i = 0; i < el.options.length; i++) {
                    if (el.options[i].value.toLowerCase() === val.toLowerCase() ||
                        el.options[i].text.toLowerCase() === val.toLowerCase()) {
                        el.selectedIndex = i;
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    // Try partial match
                    for (var j = 0; j < el.options.length; j++) {
                        if (val.toLowerCase().indexOf(el.options[j].value.toLowerCase()) >= 0 ||
                            el.options[j].value.toLowerCase().indexOf(val.toLowerCase()) >= 0) {
                            el.selectedIndex = j;
                            break;
                        }
                    }
                }
            } else {
                el.value = val;
            }
        }

        function render() {
            list.innerHTML = '';
            if (!results.length) {
                list.innerHTML = '<div class="mapbox-ac-empty">No results found</div>';
                list.classList.add('open');
                return;
            }
            results.forEach(function(f, i) {
                var item = document.createElement('div');
                item.className = 'mapbox-ac-item' + (i === activeIdx ? ' active' : '');
                var parts = (f.place_name || '').split(',');
                item.innerHTML = '<strong>' + (parts[0] || '').trim() + '</strong>' +
                    (parts.length > 1 ? '<small>' + parts.slice(1).join(',').trim() + '</small>' : '');
                item.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    fillFields(f);
                });
                list.appendChild(item);
            });
            list.classList.add('open');
        }

        function search(query) {
            if (!MAPBOX_TOKEN || query.length < 3) { close(); return; }
            var url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' +
                encodeURIComponent(query) + '.json?access_token=' + MAPBOX_TOKEN +
                '&country=za&types=address,place,locality,neighborhood&limit=5&language=en';

            fetch(url)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    results = data.features || [];
                    activeIdx = -1;
                    render();
                })
                .catch(function() { close(); });
        }

        input.addEventListener('input', function() {
            var q = input.value.trim();
            clearTimeout(debounceTimers[inputId]);
            if (q.length < 3) { close(); return; }
            debounceTimers[inputId] = setTimeout(function() { search(q); }, 300);
        });

        input.addEventListener('keydown', function(e) {
            if (!results.length) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIdx = Math.min(activeIdx + 1, results.length - 1);
                render();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIdx = Math.max(activeIdx - 1, 0);
                render();
            } else if (e.key === 'Enter' && activeIdx >= 0) {
                e.preventDefault();
                fillFields(results[activeIdx]);
            } else if (e.key === 'Escape') {
                close();
            }
        });

        input.addEventListener('blur', function() {
            setTimeout(close, 200);
        });

        input.addEventListener('focus', function() {
            if (results.length) render();
        });
    };
})();
</script>
@endpush
