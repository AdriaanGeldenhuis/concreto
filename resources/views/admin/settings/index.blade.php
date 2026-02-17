@extends('layouts.admin')
@section('title', 'Settings')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Site Settings</h1>
            <small class="text-muted">Configure branding, theme, content, and business rules for your storefront.</small>
        </div>
        <button type="submit" form="settings-form" class="btn btn-primary">Save All Settings</button>
    </div>

    {{-- Tab Navigation --}}
    <div class="card mb-2">
        <div class="card-body" style="padding:0.5rem 1rem;">
            <div style="display:flex; gap:0.25rem; flex-wrap:wrap;" id="settings-tabs">
                <button type="button" class="btn btn-sm btn-primary settings-tab" data-tab="branding">Branding</button>
                <button type="button" class="btn btn-sm btn-ghost settings-tab" data-tab="backgrounds">Backgrounds</button>
                <button type="button" class="btn btn-sm btn-ghost settings-tab" data-tab="theme">Theme</button>
                <button type="button" class="btn btn-sm btn-ghost settings-tab" data-tab="typography">Typography</button>
                <button type="button" class="btn btn-sm btn-ghost settings-tab" data-tab="layout">Layout</button>
                <button type="button" class="btn btn-sm btn-ghost settings-tab" data-tab="company">Company</button>
                <button type="button" class="btn btn-sm btn-ghost settings-tab" data-tab="homepage">Homepage</button>
                <button type="button" class="btn btn-sm btn-ghost settings-tab" data-tab="footer">Footer & Social</button>
                <button type="button" class="btn btn-sm btn-ghost settings-tab" data-tab="legal">Legal</button>
                <button type="button" class="btn btn-sm btn-ghost settings-tab" data-tab="business">Business Rules</button>
                <button type="button" class="btn btn-sm btn-ghost settings-tab" data-tab="css">Custom CSS</button>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="settings-form">
        @csrf
        <input type="hidden" name="_active_tab" id="activeTabInput" value="">

        {{-- BRANDING --}}
        <div class="settings-panel" id="panel-branding">
        <div class="card">
            <div class="card-header">Branding / Logo</div>
            <div class="card-body">
                <p class="text-muted text-small mb-2">Your logo and favicon appear across the storefront, emails, and invoices.</p>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Site Logo</label>
                        @if(!empty($siteSettings['site_logo']))
                            <div class="settings-preview mb-1">
                                <img src="{{ url('media/' . $siteSettings['site_logo']) }}" alt="Logo" style="max-height:80px; border-radius:var(--radius); border:1px solid var(--border); padding:0.5rem; background:rgba(255,255,255,0.05);">
                                <label class="form-check mt-1" style="font-weight:400; font-size:0.8125rem;">
                                    <input type="checkbox" name="remove_files[site_logo]" value="1"> Remove logo
                                </label>
                            </div>
                        @endif
                        <input type="file" name="file_settings[site_logo]" class="form-control" accept="image/*">
                        <input type="hidden" name="groups[site_logo]" value="branding">
                        <small class="text-muted">Recommended: PNG or SVG with transparent background, max 5MB</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Logo Height (px)</label>
                        <input type="number" name="settings[logo_height]" class="form-control" min="20" max="200" step="2" value="{{ $siteSettings['logo_height'] ?? '36' }}" style="max-width:150px;">
                        <input type="hidden" name="groups[logo_height]" value="branding">
                        <small class="text-muted">Controls the logo size in the navbar, sidebar, and footer. Default: 36px</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Favicon</label>
                        @if(!empty($siteSettings['site_favicon']))
                            <div class="settings-preview mb-1">
                                <img src="{{ url('media/' . $siteSettings['site_favicon']) }}" alt="Favicon" style="max-height:48px; border-radius:var(--radius); border:1px solid var(--border); padding:0.25rem; background:rgba(255,255,255,0.05);">
                                <label class="form-check mt-1" style="font-weight:400; font-size:0.8125rem;">
                                    <input type="checkbox" name="remove_files[site_favicon]" value="1"> Remove favicon
                                </label>
                            </div>
                        @endif
                        <input type="file" name="file_settings[site_favicon]" class="form-control" accept="image/*">
                        <input type="hidden" name="groups[site_favicon]" value="branding">
                        <small class="text-muted">Recommended: 32x32 or 64x64 PNG. This is the small icon in browser tabs.</small>
                    </div>
                </div>
            </div>
        </div>
        </div>

        {{-- BACKGROUNDS --}}
        <div class="settings-panel" id="panel-backgrounds" style="display:none;">
        <div class="card">
            <div class="card-header">Background Images</div>
            <div class="card-body">
                <p class="text-muted text-small mb-2">Upload background images for different screen sizes. The overlay creates contrast for text readability.</p>
                <div class="form-group">
                    <label class="form-label">Desktop Background (1920x1080+)</label>
                    @if(!empty($siteSettings['bg_image_desktop']))
                        <div class="settings-preview mb-1">
                            <img src="{{ url('media/' . $siteSettings['bg_image_desktop']) }}" alt="Desktop BG" style="max-height:120px; width:100%; object-fit:cover; border-radius:var(--radius); border:1px solid var(--border);">
                            <label class="form-check mt-1" style="font-weight:400; font-size:0.8125rem;">
                                <input type="checkbox" name="remove_files[bg_image_desktop]" value="1"> Remove
                            </label>
                        </div>
                    @endif
                    <input type="file" name="file_settings[bg_image_desktop]" class="form-control" accept="image/*">
                    <input type="hidden" name="groups[bg_image_desktop]" value="branding">
                </div>
                <div class="form-group">
                    <label class="form-label">Tablet Background (768x1024)</label>
                    @if(!empty($siteSettings['bg_image_tablet']))
                        <div class="settings-preview mb-1">
                            <img src="{{ url('media/' . $siteSettings['bg_image_tablet']) }}" alt="Tablet BG" style="max-height:120px; width:100%; object-fit:cover; border-radius:var(--radius); border:1px solid var(--border);">
                            <label class="form-check mt-1" style="font-weight:400; font-size:0.8125rem;">
                                <input type="checkbox" name="remove_files[bg_image_tablet]" value="1"> Remove
                            </label>
                        </div>
                    @endif
                    <input type="file" name="file_settings[bg_image_tablet]" class="form-control" accept="image/*">
                    <input type="hidden" name="groups[bg_image_tablet]" value="branding">
                </div>
                <div class="form-group">
                    <label class="form-label">Mobile Background (375x812)</label>
                    @if(!empty($siteSettings['bg_image_mobile']))
                        <div class="settings-preview mb-1">
                            <img src="{{ url('media/' . $siteSettings['bg_image_mobile']) }}" alt="Mobile BG" style="max-height:120px; width:100%; object-fit:cover; border-radius:var(--radius); border:1px solid var(--border);">
                            <label class="form-check mt-1" style="font-weight:400; font-size:0.8125rem;">
                                <input type="checkbox" name="remove_files[bg_image_mobile]" value="1"> Remove
                            </label>
                        </div>
                    @endif
                    <input type="file" name="file_settings[bg_image_mobile]" class="form-control" accept="image/*">
                    <input type="hidden" name="groups[bg_image_mobile]" value="branding">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Overlay Color</label>
                        <input type="color" name="settings[bg_overlay_color]" class="form-control" value="{{ $siteSettings['bg_overlay_color'] ?? '#000000' }}" style="height:45px;">
                        <input type="hidden" name="groups[bg_overlay_color]" value="branding">
                        <small class="text-muted">Colour overlay on the background image</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Overlay Opacity</label>
                        <input type="range" name="settings[bg_overlay_opacity]" class="form-control" min="0" max="100" step="5"
                               value="{{ $siteSettings['bg_overlay_opacity'] ?? '70' }}" style="height:45px;"
                               oninput="this.nextElementSibling.textContent = this.value + '%'">
                        <small class="text-muted">{{ ($siteSettings['bg_overlay_opacity'] ?? '70') }}%</small>
                        <input type="hidden" name="groups[bg_overlay_opacity]" value="branding">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Background Position</label>
                        <select name="settings[bg_position]" class="form-control">
                            @php $bgPos = $siteSettings['bg_position'] ?? 'center center'; @endphp
                            <option value="center center" @selected($bgPos === 'center center')>Center</option>
                            <option value="top center" @selected($bgPos === 'top center')>Top</option>
                            <option value="bottom center" @selected($bgPos === 'bottom center')>Bottom</option>
                            <option value="center left" @selected($bgPos === 'center left')>Left</option>
                            <option value="center right" @selected($bgPos === 'center right')>Right</option>
                        </select>
                        <input type="hidden" name="groups[bg_position]" value="branding">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Background Size</label>
                        <select name="settings[bg_size]" class="form-control">
                            @php $bgSize = $siteSettings['bg_size'] ?? 'cover'; @endphp
                            <option value="cover" @selected($bgSize === 'cover')>Cover (fill screen)</option>
                            <option value="contain" @selected($bgSize === 'contain')>Contain (fit image)</option>
                            <option value="auto" @selected($bgSize === 'auto')>Auto (original size)</option>
                        </select>
                        <input type="hidden" name="groups[bg_size]" value="branding">
                    </div>
                </div>
            </div>
        </div>
        </div>

        {{-- THEME --}}
        <div class="settings-panel" id="panel-theme" style="display:none;">
        <div class="card">
            <div class="card-header">Theme / Colors</div>
            <div class="card-body">
                <p class="text-muted text-small mb-2">Customise the accent colours used across buttons, links, and UI elements on the storefront.</p>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Accent / Primary</label>
                        <input type="color" name="settings[primary_color]" class="form-control" value="{{ $siteSettings['primary_color'] ?? '#F97316' }}" style="height:45px;">
                        <input type="hidden" name="groups[primary_color]" value="theme">
                        <small class="text-muted">{{ $siteSettings['primary_color'] ?? '#F97316' }} &middot; Buttons, links, active states</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Accent Dark (hover)</label>
                        <input type="color" name="settings[primary_dark_color]" class="form-control" value="{{ $siteSettings['primary_dark_color'] ?? '#EA580C' }}" style="height:45px;">
                        <input type="hidden" name="groups[primary_dark_color]" value="theme">
                        <small class="text-muted">{{ $siteSettings['primary_dark_color'] ?? '#EA580C' }} &middot; Used for hover states</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Success</label>
                        <input type="color" name="settings[success_color]" class="form-control" value="{{ $siteSettings['success_color'] ?? '#22c55e' }}" style="height:45px;">
                        <input type="hidden" name="groups[success_color]" value="theme">
                        <small class="text-muted">{{ $siteSettings['success_color'] ?? '#22c55e' }} &middot; Confirmations, "in stock"</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Danger</label>
                        <input type="color" name="settings[danger_color]" class="form-control" value="{{ $siteSettings['danger_color'] ?? '#ef4444' }}" style="height:45px;">
                        <input type="hidden" name="groups[danger_color]" value="theme">
                        <small class="text-muted">{{ $siteSettings['danger_color'] ?? '#ef4444' }} &middot; Errors, destructive actions</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Warning</label>
                        <input type="color" name="settings[warning_color]" class="form-control" value="{{ $siteSettings['warning_color'] ?? '#f59e0b' }}" style="height:45px;">
                        <input type="hidden" name="groups[warning_color]" value="theme">
                        <small class="text-muted">{{ $siteSettings['warning_color'] ?? '#f59e0b' }}</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Info</label>
                        <input type="color" name="settings[info_color]" class="form-control" value="{{ $siteSettings['info_color'] ?? '#3b82f6' }}" style="height:45px;">
                        <input type="hidden" name="groups[info_color]" value="theme">
                        <small class="text-muted">{{ $siteSettings['info_color'] ?? '#3b82f6' }}</small>
                    </div>
                </div>
            </div>
        </div>
        </div>

        {{-- TYPOGRAPHY --}}
        <div class="settings-panel" id="panel-typography" style="display:none;">
        <div class="card">
            <div class="card-header">Typography / Fonts</div>
            <div class="card-body">
                <p class="text-muted text-small mb-2">Choose fonts and sizing. Select a Google Font from the dropdown, or paste a custom Google Fonts URL below.</p>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Body Font</label>
                        <select name="settings[font_family]" class="form-control">
                            @php $ff = $siteSettings['font_family'] ?? 'system'; @endphp
                            <option value="system" @selected($ff === 'system')>System Default</option>
                            <option value="'Inter', sans-serif" @selected($ff === "'Inter', sans-serif")>Inter</option>
                            <option value="'Roboto', sans-serif" @selected($ff === "'Roboto', sans-serif")>Roboto</option>
                            <option value="'Open Sans', sans-serif" @selected($ff === "'Open Sans', sans-serif")>Open Sans</option>
                            <option value="'Lato', sans-serif" @selected($ff === "'Lato', sans-serif")>Lato</option>
                            <option value="'Poppins', sans-serif" @selected($ff === "'Poppins', sans-serif")>Poppins</option>
                            <option value="'Montserrat', sans-serif" @selected($ff === "'Montserrat', sans-serif")>Montserrat</option>
                            <option value="'Nunito', sans-serif" @selected($ff === "'Nunito', sans-serif")>Nunito</option>
                            <option value="'Raleway', sans-serif" @selected($ff === "'Raleway', sans-serif")>Raleway</option>
                            <option value="'Source Sans 3', sans-serif" @selected($ff === "'Source Sans 3', sans-serif")>Source Sans</option>
                            <option value="'PT Sans', sans-serif" @selected($ff === "'PT Sans', sans-serif")>PT Sans</option>
                            <option value="'Merriweather', serif" @selected($ff === "'Merriweather', serif")>Merriweather</option>
                            <option value="'Playfair Display', serif" @selected($ff === "'Playfair Display', serif")>Playfair Display</option>
                            <option value="'Georgia', serif" @selected($ff === "'Georgia', serif")>Georgia</option>
                            <option value="'Fira Code', monospace" @selected($ff === "'Fira Code', monospace")>Fira Code</option>
                        </select>
                        <input type="hidden" name="groups[font_family]" value="typography">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Heading Font</label>
                        <select name="settings[heading_font_family]" class="form-control">
                            @php $hff = $siteSettings['heading_font_family'] ?? 'inherit'; @endphp
                            <option value="inherit" @selected($hff === 'inherit')>Same as Body</option>
                            <option value="'Inter', sans-serif" @selected($hff === "'Inter', sans-serif")>Inter</option>
                            <option value="'Roboto', sans-serif" @selected($hff === "'Roboto', sans-serif")>Roboto</option>
                            <option value="'Poppins', sans-serif" @selected($hff === "'Poppins', sans-serif")>Poppins</option>
                            <option value="'Montserrat', sans-serif" @selected($hff === "'Montserrat', sans-serif")>Montserrat</option>
                            <option value="'Playfair Display', serif" @selected($hff === "'Playfair Display', serif")>Playfair Display</option>
                        </select>
                        <input type="hidden" name="groups[heading_font_family]" value="typography">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Google Font URL (optional)</label>
                    <input type="text" name="settings[google_font_url]" class="form-control" value="{{ $siteSettings['google_font_url'] ?? '' }}" placeholder="https://fonts.googleapis.com/css2?family=...">
                    <input type="hidden" name="groups[google_font_url]" value="typography">
                    <small class="text-muted">Only needed for custom fonts not in the dropdown. Get URLs from fonts.google.com.</small>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Base Size (px)</label><input type="number" name="settings[font_size_base]" class="form-control" min="12" max="24" value="{{ $siteSettings['font_size_base'] ?? '16' }}"><input type="hidden" name="groups[font_size_base]" value="typography"><small class="text-muted">Default: 16px</small></div>
                    <div class="form-group"><label class="form-label">H1 (rem)</label><input type="number" name="settings[font_size_h1]" class="form-control" min="1" max="5" step="0.125" value="{{ $siteSettings['font_size_h1'] ?? '1.75' }}"><input type="hidden" name="groups[font_size_h1]" value="typography"></div>
                    <div class="form-group"><label class="form-label">H2 (rem)</label><input type="number" name="settings[font_size_h2]" class="form-control" min="1" max="4" step="0.125" value="{{ $siteSettings['font_size_h2'] ?? '1.5' }}"><input type="hidden" name="groups[font_size_h2]" value="typography"></div>
                    <div class="form-group"><label class="form-label">H3 (rem)</label><input type="number" name="settings[font_size_h3]" class="form-control" min="0.875" max="3" step="0.125" value="{{ $siteSettings['font_size_h3'] ?? '1.25' }}"><input type="hidden" name="groups[font_size_h3]" value="typography"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Body Weight</label><select name="settings[font_weight_body]" class="form-control">@php $fwb = $siteSettings['font_weight_body'] ?? '400'; @endphp<option value="300" @selected($fwb === '300')>Light (300)</option><option value="400" @selected($fwb === '400')>Regular (400)</option><option value="500" @selected($fwb === '500')>Medium (500)</option><option value="600" @selected($fwb === '600')>Semi-Bold (600)</option></select><input type="hidden" name="groups[font_weight_body]" value="typography"></div>
                    <div class="form-group"><label class="form-label">Heading Weight</label><select name="settings[font_weight_heading]" class="form-control">@php $fwh = $siteSettings['font_weight_heading'] ?? '700'; @endphp<option value="500" @selected($fwh === '500')>Medium (500)</option><option value="600" @selected($fwh === '600')>Semi-Bold (600)</option><option value="700" @selected($fwh === '700')>Bold (700)</option><option value="800" @selected($fwh === '800')>Extra-Bold (800)</option><option value="900" @selected($fwh === '900')>Black (900)</option></select><input type="hidden" name="groups[font_weight_heading]" value="typography"></div>
                    <div class="form-group"><label class="form-label">Line Height</label><input type="number" name="settings[line_height]" class="form-control" min="1" max="2.5" step="0.1" value="{{ $siteSettings['line_height'] ?? '1.6' }}"><input type="hidden" name="groups[line_height]" value="typography"><small class="text-muted">Default: 1.6</small></div>
                    <div class="form-group"><label class="form-label">Letter Spacing (px)</label><input type="number" name="settings[letter_spacing]" class="form-control" min="-2" max="5" step="0.25" value="{{ $siteSettings['letter_spacing'] ?? '0' }}"><input type="hidden" name="groups[letter_spacing]" value="typography"><small class="text-muted">Default: 0</small></div>
                </div>
            </div>
        </div>
        </div>

        {{-- LAYOUT --}}
        <div class="settings-panel" id="panel-layout" style="display:none;">
        <div class="card">
            <div class="card-header">Layout & Shape</div>
            <div class="card-body">
                <p class="text-muted text-small mb-2">Control the visual shape and spacing of cards, buttons, and containers.</p>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Border Radius (px)</label><input type="number" name="settings[border_radius]" class="form-control" min="0" max="24" value="{{ $siteSettings['border_radius'] ?? '8' }}"><input type="hidden" name="groups[border_radius]" value="theme"><small class="text-muted">0 = sharp corners, 24 = very rounded</small></div>
                    <div class="form-group"><label class="form-label">Button Radius (px)</label><input type="number" name="settings[btn_border_radius]" class="form-control" min="0" max="50" value="{{ $siteSettings['btn_border_radius'] ?? '' }}" placeholder="Same as border radius"><input type="hidden" name="groups[btn_border_radius]" value="theme"><small class="text-muted">Leave blank to match global border radius</small></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Card Shadow</label><select name="settings[card_shadow]" class="form-control">@php $cs = $siteSettings['card_shadow'] ?? 'medium'; @endphp<option value="none" @selected($cs === 'none')>None (flat)</option><option value="light" @selected($cs === 'light')>Light (subtle)</option><option value="medium" @selected($cs === 'medium')>Medium (default)</option><option value="heavy" @selected($cs === 'heavy')>Heavy (prominent)</option></select><input type="hidden" name="groups[card_shadow]" value="theme"></div>
                    <div class="form-group"><label class="form-label">Container Width (px)</label><input type="number" name="settings[container_max_width]" class="form-control" min="900" max="1800" step="50" value="{{ $siteSettings['container_max_width'] ?? '1200' }}"><input type="hidden" name="groups[container_max_width]" value="theme"><small class="text-muted">Max width of the main content area. Default: 1200px</small></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Button Style</label><select name="settings[btn_style]" class="form-control">@php $bs = $siteSettings['btn_style'] ?? 'filled'; @endphp<option value="filled" @selected($bs === 'filled')>Filled (solid colour)</option><option value="outline" @selected($bs === 'outline')>Outline (bordered)</option><option value="soft" @selected($bs === 'soft')>Soft (muted fill)</option></select><input type="hidden" name="groups[btn_style]" value="theme"></div>
                    <div class="form-group"><label class="form-label">Button Text</label><select name="settings[btn_text_transform]" class="form-control">@php $btt = $siteSettings['btn_text_transform'] ?? 'none'; @endphp<option value="none" @selected($btt === 'none')>None (as typed)</option><option value="uppercase" @selected($btt === 'uppercase')>UPPERCASE</option><option value="capitalize" @selected($btt === 'capitalize')>Capitalize</option></select><input type="hidden" name="groups[btn_text_transform]" value="theme"></div>
                </div>
            </div>
        </div>
        </div>

        {{-- COMPANY --}}
        <div class="settings-panel" id="panel-company" style="display:none;">
        <div class="card">
            <div class="card-header">Company Information</div>
            <div class="card-body">
                <p class="text-muted text-small mb-2">These details appear on invoices, email footers, and the storefront contact section.</p>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Company Name</label><input type="text" name="settings[company_name]" class="form-control" value="{{ $siteSettings['company_name'] ?? 'Concreto' }}"><input type="hidden" name="groups[company_name]" value="general"></div>
                    <div class="form-group"><label class="form-label">Contact Email</label><input type="email" name="settings[contact_email]" class="form-control" value="{{ $siteSettings['contact_email'] ?? '' }}" placeholder="info@yourcompany.co.za"><input type="hidden" name="groups[contact_email]" value="general"><small class="text-muted">Displayed on the storefront and used as reply-to for emails.</small></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Phone</label><input type="text" name="settings[contact_phone]" class="form-control" value="{{ $siteSettings['contact_phone'] ?? '' }}" placeholder="+27 12 345 6789"><input type="hidden" name="groups[contact_phone]" value="general"></div>
                    <div class="form-group"><label class="form-label">Address</label><input type="text" name="settings[contact_address]" class="form-control" value="{{ $siteSettings['contact_address'] ?? '' }}" placeholder="123 Main Road, City, Province"><input type="hidden" name="groups[contact_address]" value="general"></div>
                </div>
            </div>
        </div>
        </div>

        {{-- HOMEPAGE --}}
        <div class="settings-panel" id="panel-homepage" style="display:none;">
        <div class="card">
            <div class="card-header">Homepage Content</div>
            <div class="card-body">
                <p class="text-muted text-small mb-2">Control the hero section and promotional banners on the storefront homepage.</p>
                <div class="form-group"><label class="form-label">Hero Title</label><input type="text" name="settings[hero_title]" class="form-control" value="{{ $siteSettings['hero_title'] ?? 'Quality Building Materials, Delivered' }}"><input type="hidden" name="groups[hero_title]" value="homepage"><small class="text-muted">Main heading displayed in the hero section.</small></div>
                <div class="form-group"><label class="form-label">Hero Subtitle</label><textarea name="settings[hero_subtitle]" class="form-control" rows="3" placeholder="A short description below the hero title...">{{ $siteSettings['hero_subtitle'] ?? '' }}</textarea><input type="hidden" name="groups[hero_subtitle]" value="homepage"></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Delivery Info Banner</label><input type="text" name="settings[delivery_info]" class="form-control" value="{{ $siteSettings['delivery_info'] ?? '' }}" placeholder="e.g. Free delivery on orders over R2,000"><input type="hidden" name="groups[delivery_info]" value="homepage"><small class="text-muted">Leave blank to hide.</small></div>
                    <div class="form-group"><label class="form-label">Promo Banner</label><input type="text" name="settings[homepage_promo]" class="form-control" value="{{ $siteSettings['homepage_promo'] ?? '' }}" placeholder="e.g. 10% off cement this week!"><input type="hidden" name="groups[homepage_promo]" value="homepage"><small class="text-muted">Leave blank to hide.</small></div>
                </div>
            </div>
        </div>
        </div>

        {{-- FOOTER --}}
        <div class="settings-panel" id="panel-footer" style="display:none;">
        <div class="card">
            <div class="card-header">Footer & Social</div>
            <div class="card-body">
                <p class="text-muted text-small mb-2">Configure footer content and social media links displayed on the storefront.</p>
                <div class="form-group"><label class="form-label">Footer About</label><textarea name="settings[footer_about]" class="form-control" rows="3" placeholder="A short paragraph about your business...">{{ $siteSettings['footer_about'] ?? '' }}</textarea><input type="hidden" name="groups[footer_about]" value="footer"><small class="text-muted">Short description shown in the footer's "About" section.</small></div>
                <div class="form-group"><label class="form-label">Footer Copyright Text</label><input type="text" name="settings[footer_text]" class="form-control" value="{{ $siteSettings['footer_text'] ?? '' }}" placeholder="&copy; 2026 Your Company. All rights reserved."><input type="hidden" name="groups[footer_text]" value="footer"></div>
                <hr>
                <h6>Social Media Links</h6>
                <p class="text-muted text-small mb-2">Leave blank to hide the icon. Social icons appear in the footer.</p>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Facebook URL</label><input type="url" name="settings[social_facebook]" class="form-control" value="{{ $siteSettings['social_facebook'] ?? '' }}" placeholder="https://facebook.com/yourpage"><input type="hidden" name="groups[social_facebook]" value="social"></div>
                    <div class="form-group"><label class="form-label">Instagram URL</label><input type="url" name="settings[social_instagram]" class="form-control" value="{{ $siteSettings['social_instagram'] ?? '' }}" placeholder="https://instagram.com/yourpage"><input type="hidden" name="groups[social_instagram]" value="social"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Twitter / X URL</label><input type="url" name="settings[social_twitter]" class="form-control" value="{{ $siteSettings['social_twitter'] ?? '' }}" placeholder="https://x.com/yourhandle"><input type="hidden" name="groups[social_twitter]" value="social"></div>
                    <div class="form-group"><label class="form-label">WhatsApp Number</label><input type="text" name="settings[social_whatsapp]" class="form-control" value="{{ $siteSettings['social_whatsapp'] ?? '' }}" placeholder="+27 82 123 4567"><input type="hidden" name="groups[social_whatsapp]" value="social"><small class="text-muted">International format. Creates a click-to-chat link.</small></div>
                </div>
            </div>
        </div>
        </div>

        {{-- LEGAL --}}
        <div class="settings-panel" id="panel-legal" style="display:none;">
        <div class="card">
            <div class="card-header">Legal Pages</div>
            <div class="card-body">
                <p class="text-muted text-small mb-2">These pages are accessible at <code>/terms</code> and <code>/privacy</code> on your storefront. HTML is supported.</p>
                <div class="form-group"><label class="form-label">Terms & Conditions</label><textarea name="settings[terms_content]" class="form-control" rows="10" placeholder="Enter your terms and conditions here...">{{ $siteSettings['terms_content'] ?? '' }}</textarea><div class="form-hint">HTML supported. Use &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;strong&gt; etc. Displayed at <strong>/terms</strong>.</div><input type="hidden" name="groups[terms_content]" value="legal"></div>
                <div class="form-group"><label class="form-label">Privacy Policy</label><textarea name="settings[privacy_content]" class="form-control" rows="10" placeholder="Enter your privacy policy here...">{{ $siteSettings['privacy_content'] ?? '' }}</textarea><div class="form-hint">HTML supported. Displayed at <strong>/privacy</strong>. Include POPIA compliance details for SA.</div><input type="hidden" name="groups[privacy_content]" value="legal"></div>
            </div>
        </div>
        </div>

        {{-- BUSINESS RULES --}}
        <div class="settings-panel" id="panel-business" style="display:none;">
        <div class="card">
            <div class="card-header">Business Rules</div>
            <div class="card-body">
                <p class="text-muted text-small mb-2">Configure order minimums, delivery fees, and tax rates that affect pricing and checkout.</p>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Min Order Value (R)</label><input type="number" name="settings[min_order_value]" class="form-control" step="0.01" value="{{ $siteSettings['min_order_value'] ?? '0' }}"><div class="form-hint">Customers cannot check out below this amount. 0 = no minimum.</div><input type="hidden" name="groups[min_order_value]" value="business"></div>
                    <div class="form-group"><label class="form-label">Business Hours</label><input type="text" name="settings[business_hours]" class="form-control" value="{{ $siteSettings['business_hours'] ?? 'Mon-Fri 07:00-17:00, Sat 07:00-12:00' }}"><input type="hidden" name="groups[business_hours]" value="business"><small class="text-muted">Displayed on the storefront and in order confirmations.</small></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Default Delivery Fee (R)</label><input type="number" name="settings[default_delivery_fee]" class="form-control" step="0.01" value="{{ $siteSettings['default_delivery_fee'] ?? '0' }}"><input type="hidden" name="groups[default_delivery_fee]" value="business"><small class="text-muted">Applied when no delivery area matches. See <a href="{{ route('admin.delivery-areas.index') }}">Delivery Areas</a> for zone-based pricing.</small></div>
                    <div class="form-group"><label class="form-label">Free Delivery Threshold (R)</label><input type="number" name="settings[free_delivery_threshold]" class="form-control" step="0.01" value="{{ $siteSettings['free_delivery_threshold'] ?? '0' }}"><div class="form-hint">Orders above this value get free delivery. 0 = disabled (always charge).</div><input type="hidden" name="groups[free_delivery_threshold]" value="business"></div>
                </div>
                <div class="form-group"><label class="form-label">VAT Rate (%)</label><input type="number" name="settings[vat_rate]" class="form-control" step="0.01" value="{{ $siteSettings['vat_rate'] ?? '15' }}" style="max-width:200px;"><div class="form-hint">SA standard rate: 15%. Affects all VAT calculations on invoices and orders.</div><input type="hidden" name="groups[vat_rate]" value="business"></div>
            </div>
        </div>
        </div>

        {{-- CUSTOM CSS --}}
        <div class="settings-panel" id="panel-css" style="display:none;">
        <div class="card">
            <div class="card-header">Custom CSS</div>
            <div class="card-body">
                <p class="text-muted text-small mb-2">Advanced: inject custom CSS into the storefront. Changes apply immediately after saving.</p>
                <div class="form-group"><label class="form-label">Additional CSS</label><textarea name="settings[custom_css]" class="form-control" rows="14" style="font-family: 'Fira Code', monospace; font-size:0.8125rem;" placeholder="/* Example: override the hero background */
.hero-section {
    background: linear-gradient(135deg, #1a1a2e, #16213e);
}">{{ $siteSettings['custom_css'] ?? '' }}</textarea><input type="hidden" name="groups[custom_css]" value="theme"><small class="text-muted">Raw CSS injected after all other stylesheets. Use browser DevTools to inspect classes.</small></div>
            </div>
        </div>
        </div>

        <div class="d-flex gap-2 mt-2 mb-4">
            <button type="submit" class="btn btn-primary btn-lg">Save All Settings</button>
        </div>
    </form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.settings-tab');
    const panels = document.querySelectorAll('.settings-panel');
    const activeTabInput = document.getElementById('activeTabInput');

    function switchTab(target) {
        tabs.forEach(t => { t.classList.remove('btn-primary'); t.classList.add('btn-ghost'); });
        const tab = document.querySelector('.settings-tab[data-tab="' + target + '"]');
        if (tab) {
            tab.classList.remove('btn-ghost');
            tab.classList.add('btn-primary');
        }
        panels.forEach(p => p.style.display = 'none');
        const panel = document.getElementById('panel-' + target);
        if (panel) panel.style.display = 'block';
        activeTabInput.value = target;
        window.location.hash = target;
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            switchTab(this.dataset.tab);
        });
    });

    // Restore tab: prefer flash value (after save), then URL hash
    const savedTab = '{{ session("_active_tab") }}';
    if (savedTab) {
        switchTab(savedTab);
    } else if (window.location.hash) {
        switchTab(window.location.hash.substring(1));
    }
});
</script>
@endpush
@endsection
