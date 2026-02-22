{{--
    Mapbox Address Autocomplete + Drop-Pin Map
    Usage: @include('partials.address-autocomplete')
    Then call: initMapboxAutocomplete('input-id', { line1:'id', line2:'id', city:'id', province:'id', postal:'id', lat:'id', lng:'id', gpsDisplay:'id' })
--}}
@push('styles')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css" rel="stylesheet" />
<style>
    .mapbox-ac-wrap { position:relative; }
    .mapbox-ac-list { position:absolute; top:100%; left:0; right:0; z-index:999; background:var(--card, #1a1a2e); border:1px solid var(--glass-border, rgba(255,255,255,0.1)); border-top:none; border-radius:0 0 var(--radius-sm, 6px) var(--radius-sm, 6px); max-height:240px; overflow-y:auto; display:none; box-shadow:0 8px 24px rgba(0,0,0,0.4); }
    .mapbox-ac-list.open { display:block; }
    .mapbox-ac-item { padding:10px 14px; cursor:pointer; font-size:0.8125rem; color:var(--text, #fff); border-bottom:1px solid rgba(255,255,255,0.05); transition:background 0.15s; }
    .mapbox-ac-item:last-child { border-bottom:none; }
    .mapbox-ac-item:hover, .mapbox-ac-item.active { background:rgba(255,255,255,0.08); }
    .mapbox-ac-item small { color:var(--text-muted, #999); display:block; margin-top:2px; }
    .mapbox-ac-empty { padding:12px 14px; font-size:0.8rem; color:var(--text-muted, #999); }
    .mapbox-pin-toggle { margin-top:6px; font-size:0.8rem; cursor:pointer; color:var(--primary, #f97316); background:none; border:none; padding:0; font-weight:600; display:inline-flex; align-items:center; gap:4px; }
    .mapbox-pin-toggle:hover { text-decoration:underline; }
    .mapbox-pin-map { margin-top:8px; border-radius:var(--radius-sm, 6px); border:1px solid var(--glass-border, rgba(255,255,255,0.1)); overflow:hidden; display:none; }
    .mapbox-pin-map.open { display:block; }
    .mapbox-pin-map-container { width:100%; height:260px; }
    .mapbox-pin-hint { font-size:0.75rem; color:var(--text-muted, #999); padding:6px 10px; background:rgba(255,255,255,0.03); border-top:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; justify-content:space-between; }
    .mapbox-pin-coords { font-family:monospace; font-size:0.7rem; color:var(--text-muted, #999); }
</style>
@endpush

@push('scripts')
<script src="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js"></script>
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

        // Drop-pin toggle button
        var toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'mapbox-pin-toggle';
        toggleBtn.innerHTML = '&#9873; Drop pin on map';
        wrap.parentNode.insertBefore(toggleBtn, wrap.nextSibling);

        // Map container
        var mapWrap = document.createElement('div');
        mapWrap.className = 'mapbox-pin-map';
        var mapId = 'pin-map-' + inputId;
        mapWrap.innerHTML = '<div class="mapbox-pin-map-container" id="' + mapId + '"></div>' +
            '<div class="mapbox-pin-hint"><span>Click the map to drop a pin, or drag the marker to adjust</span><span class="mapbox-pin-coords" id="' + mapId + '-coords"></span></div>';
        toggleBtn.parentNode.insertBefore(mapWrap, toggleBtn.nextSibling);

        var pinMap = null;
        var pinMarker = null;

        toggleBtn.addEventListener('click', function() {
            var isOpen = mapWrap.classList.contains('open');
            if (isOpen) {
                mapWrap.classList.remove('open');
                toggleBtn.innerHTML = '&#9873; Drop pin on map';
                return;
            }
            mapWrap.classList.add('open');
            toggleBtn.innerHTML = '&#9650; Hide map';

            if (!pinMap) {
                mapboxgl.accessToken = MAPBOX_TOKEN;

                // Determine initial center from existing GPS values or default SA
                var initLat = fields.lat ? parseFloat(document.getElementById(fields.lat).value) : 0;
                var initLng = fields.lng ? parseFloat(document.getElementById(fields.lng).value) : 0;
                var hasExisting = initLat && initLng && !isNaN(initLat) && !isNaN(initLng);
                var center = hasExisting ? [initLng, initLat] : [28.0473, -26.2041];
                var zoom = hasExisting ? 15 : 6;

                pinMap = new mapboxgl.Map({
                    container: mapId,
                    style: 'mapbox://styles/mapbox/dark-v11',
                    center: center,
                    zoom: zoom,
                    attributionControl: false
                });
                pinMap.addControl(new mapboxgl.NavigationControl(), 'bottom-right');

                if (hasExisting) {
                    addOrMoveMarker(initLng, initLat);
                }

                pinMap.on('click', function(e) {
                    var lng = e.lngLat.lng;
                    var lat = e.lngLat.lat;
                    addOrMoveMarker(lng, lat);
                    updateGps(lat, lng);
                    reverseGeocode(lng, lat);
                });
            } else {
                setTimeout(function() { pinMap.resize(); }, 50);
            }
        });

        function addOrMoveMarker(lng, lat) {
            if (pinMarker) {
                pinMarker.setLngLat([lng, lat]);
            } else {
                pinMarker = new mapboxgl.Marker({ color: '#f97316', draggable: true })
                    .setLngLat([lng, lat])
                    .addTo(pinMap);
                pinMarker.on('dragend', function() {
                    var pos = pinMarker.getLngLat();
                    updateGps(pos.lat, pos.lng);
                    reverseGeocode(pos.lng, pos.lat);
                });
            }
        }

        function updateGps(lat, lng) {
            if (fields.lat) setValue(fields.lat, lat.toFixed(7));
            if (fields.lng) setValue(fields.lng, lng.toFixed(7));
            var coordsEl = document.getElementById(mapId + '-coords');
            if (coordsEl) coordsEl.textContent = lat.toFixed(7) + ', ' + lng.toFixed(7);
            if (fields.gpsDisplay) {
                var gd = document.getElementById(fields.gpsDisplay);
                if (gd) gd.textContent = 'GPS: ' + lat.toFixed(7) + ', ' + lng.toFixed(7);
            }
        }

        function reverseGeocode(lng, lat) {
            if (!MAPBOX_TOKEN) return;
            var url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' + lng + ',' + lat +
                '.json?access_token=' + MAPBOX_TOKEN + '&types=address,place,locality&limit=1&language=en';

            fetch(url)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.features && data.features.length > 0) {
                        fillFromFeature(data.features[0]);
                        input.value = data.features[0].place_name || '';
                    }
                })
                .catch(function() {});
        }

        var activeIdx = -1;
        var results = [];

        function close() {
            list.classList.remove('open');
            list.innerHTML = '';
            results = [];
            activeIdx = -1;
        }

        function fillFromFeature(feature) {
            var ctx = {};
            (feature.context || []).forEach(function(c) {
                var type = c.id.split('.')[0];
                ctx[type] = c.text;
                if (c.short_code) ctx[type + '_short'] = c.short_code;
            });

            // Parse place_name parts: "82 Mooiwater Rd, Vaalfontein, Gauteng 1917, South Africa"
            var nameParts = (feature.place_name || '').split(',').map(function(s) { return s.trim(); });
            // Remove country from end
            if (nameParts.length > 1 && nameParts[nameParts.length - 1].match(/south africa/i)) {
                nameParts.pop();
            }

            // Line 1: use address+text for address types, otherwise first part of place_name
            var line1 = '';
            if (feature.address && feature.text) {
                line1 = feature.address + ' ' + feature.text;
            } else {
                line1 = nameParts[0] || feature.text || '';
            }

            // Province & postal from context (most reliable)
            var province = ctx.region || '';
            var postal = ctx.postcode || '';
            // Fallback: extract 4-digit postal code from place_name
            if (!postal) {
                for (var i = 0; i < nameParts.length; i++) {
                    var m = nameParts[i].match(/\b(\d{4})\b/);
                    if (m) { postal = m[1]; break; }
                }
            }

            // City: In SA, neighborhood is often the actual town name (e.g. "Vaalfontein")
            // while place is the municipality (e.g. "Emfuleni NU") — prefer neighborhood
            var city = '';
            var line2 = '';
            if (ctx.neighborhood) {
                city = ctx.neighborhood;
                if (ctx.locality && ctx.locality !== city) line2 = ctx.locality;
            } else if (ctx.locality) {
                city = ctx.locality;
            } else if (ctx.place) {
                city = ctx.place;
            }
            // Fallback to second part of place_name
            if (!city && nameParts.length >= 2) {
                city = nameParts[1];
            }
            // Clean SA municipality suffixes ("Emfuleni NU", "eThekwini Metropolitan Municipality")
            city = city.replace(/\s+(NU|Metropolitan Municipality|Local Municipality|District Municipality|Metro)$/i, '').trim();

            if (fields.line1) setValue(fields.line1, line1);
            if (fields.line2) setValue(fields.line2, line2);
            if (fields.city) setValue(fields.city, city);
            if (fields.province) setProvince(fields.province, province);
            if (fields.postal) setValue(fields.postal, postal);
        }

        function fillFields(feature) {
            fillFromFeature(feature);

            if (feature.center) {
                updateGps(feature.center[1], feature.center[0]);
                // Pan map to the selected location if map is open
                if (pinMap) {
                    pinMap.flyTo({ center: feature.center, zoom: 16 });
                    addOrMoveMarker(feature.center[0], feature.center[1]);
                }
            }

            input.value = feature.place_name || '';
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
